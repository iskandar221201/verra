<?php

namespace App\Services;

use App\Models\ConversationModel;
use App\Models\KnowledgeBaseModel;
use App\Models\TenantConfigModel;

class WebhookProcessorService
{
    protected ConversationModel $conversationModel;
    protected KnowledgeBaseModel $kbModel;
    protected TenantConfigModel $configModel;
    protected HandoverService $handoverService;
    protected AiService $aiService;
    protected FonnteService $fonnteService;

    public function __construct()
    {
        $this->conversationModel = new ConversationModel();
        $this->kbModel = new KnowledgeBaseModel();
        $this->configModel = new TenantConfigModel();
        $this->handoverService = new HandoverService();
        $this->aiService = new AiService();
        $this->fonnteService = new FonnteService();
    }

    /**
     * Process an incoming WhatsApp message
     *
     * @param array $channel WA channel record
     * @param string $waNumber Customer WA number
     * @param string $messageText Message text from customer
     * @return void
     */
    public function process(array $channel, string $waNumber, string $messageText): void
    {
        $tenantId = $channel['tenant_id'];
        $channelId = $channel['id'];
        $fonnteToken = $channel['fonnte_token'];

        // Step 1: Check if customer has active agent handover (mode=agent)
        $activeHandover = $this->handoverService->hasActiveAgentHandover($tenantId, $channelId, $waNumber);
        if ($activeHandover) {
            // Save message to conversations but do NOT process with AI
            $this->conversationModel->insert([
                'tenant_id' => $tenantId,
                'channel_id' => $channelId,
                'wa_number' => $waNumber,
                'role' => 'user',
                'message' => $messageText,
            ]);

            // Insert SSE event if SseEventModel exists
            $this->insertSseEvent($tenantId, $channelId, $waNumber, 'new_message', [
                'message' => $messageText,
                'sender' => 'customer',
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

            return; // STOP — agent will reply manually
        }

        // Step 2: Save customer message to conversations
        $this->conversationModel->insert([
            'tenant_id' => $tenantId,
            'channel_id' => $channelId,
            'wa_number' => $waNumber,
            'role' => 'user',
            'message' => $messageText,
        ]);

        // Step 2.5: Auto-assign lead if enabled (first message from this customer)
        try {
            $leadService = new LeadAssignmentService();
            $leadService->autoAssign($tenantId, $channelId, $waNumber, $fonnteToken);
        } catch (\Exception $e) {
            log_message('error', "[WebhookProcessorService] Lead auto-assign error: {$e->getMessage()}");
        }

        // Step 3: Check handover keyword
        if ($this->handoverService->checkKeyword($tenantId, $messageText)) {
            // Create handover record
            $this->handoverService->createHandover($tenantId, $channelId, $waNumber, $messageText, 'keyword');

            // Send escalation message via Fonnte
            $this->fonnteService->send(
                $fonnteToken,
                $waNumber,
                'Menghubungkan Anda ke agen kami, mohon tunggu sebentar...'
            );

            return; // STOP
        }

        // Step 4: Get tenant config
        $config = $this->configModel->where('tenant_id', $tenantId)->first();
        $maxHistory = $config['max_history'] ?? 10;
        $systemPrompt = $config['system_prompt'] ?? '';

        // Step 5: Get active KB entries
        $kbEntries = $this->kbModel
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        // Step 6: Build system prompt with KB
        $fullSystemPrompt = $this->buildSystemPrompt($systemPrompt, $kbEntries);

        // Step 7: Get conversation history
        $history = $this->conversationModel->getHistory($tenantId, $channelId, $waNumber, $maxHistory);

        // Step 8: Build messages array from history
        $messages = [];
        foreach ($history as $conv) {
            $messages[] = [
                'role' => $conv['role'],
                'content' => $conv['message'],
            ];
        }

        // Step 9: Call AI with error handling
        try {
            $aiResponse = $this->aiService->chat($tenantId, $fullSystemPrompt, $messages);

            // Step 10: Check if AI response indicates "unable to answer"
            if ($this->isAiUnable($aiResponse)) {
                // Trigger handover with ai_unable type
                $this->handoverService->createHandover($tenantId, $channelId, $waNumber, $messageText, 'ai_unable');

                // Still send the AI response to the customer
                $this->fonnteService->send($fonnteToken, $waNumber, $aiResponse);

                // Save AI response to conversations
                $this->conversationModel->insert([
                    'tenant_id' => $tenantId,
                    'channel_id' => $channelId,
                    'wa_number' => $waNumber,
                    'role' => 'assistant',
                    'message' => $aiResponse,
                ]);

                return;
            }

            // Step 11: Save AI response to conversations
            $this->conversationModel->insert([
                'tenant_id' => $tenantId,
                'channel_id' => $channelId,
                'wa_number' => $waNumber,
                'role' => 'assistant',
                'message' => $aiResponse,
            ]);

            // Step 12: Send AI response via Fonnte
            $this->fonnteService->send($fonnteToken, $waNumber, $aiResponse);

        } catch (\Exception $e) {
            // All keys failed or other error — send fallback message
            log_message('error', "[WebhookProcessorService] AI error for tenant {$tenantId}: {$e->getMessage()}");

            $this->fonnteService->send(
                $fonnteToken,
                $waNumber,
                'Maaf, kami sedang mengalami gangguan. Silakan coba beberapa saat lagi.'
            );
        }
    }

    /**
     * Build the full system prompt including KB entries
     *
     * @param string $basePrompt Tenant's system prompt
     * @param array $kbEntries Active KB entries
     * @return string
     */
    private function buildSystemPrompt(string $basePrompt, array $kbEntries): string
    {
        if (empty($kbEntries)) {
            return $basePrompt;
        }

        $kbText = "\n\n## KNOWLEDGE BASE:\n";

        $currentCategory = '';
        foreach ($kbEntries as $entry) {
            if ($entry['category'] !== $currentCategory) {
                $currentCategory = $entry['category'];
                $kbText .= "### {$currentCategory}\n";
            }
            $kbText .= "**{$entry['title']}**\n{$entry['content']}\n\n";
        }

        return $basePrompt . $kbText;
    }

    /**
     * Detect if AI response indicates it cannot answer
     *
     * @param string $response AI response text
     * @return bool
     */
    private function isAiUnable(string $response): bool
    {
        $phrases = [
            'tidak tahu',
            'tidak memiliki informasi',
            'hubungi kami',
            'silakan hubungi',
            'di luar kemampuan saya',
        ];

        $responseLower = mb_strtolower($response);

        foreach ($phrases as $phrase) {
            if (mb_strpos($responseLower, $phrase) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Insert SSE event if SseEventModel exists
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber
     * @param string $eventType
     * @param array $payload
     * @return void
     */
    private function insertSseEvent(int $tenantId, int $channelId, string $waNumber, string $eventType, array $payload): void
    {
        try {
            $sseModel = new \App\Models\SseEventModel();
            $sseModel->insert([
                'tenant_id' => $tenantId,
                'channel_id' => $channelId,
                'wa_number' => $waNumber,
                'event_type' => $eventType,
                'payload' => json_encode($payload),
            ]);
        } catch (\Exception $e) {
            log_message('error', "[WebhookProcessorService] SSE event insert failed: {$e->getMessage()}");
        }
    }
}
