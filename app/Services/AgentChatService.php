<?php

namespace App\Services;

use App\Models\HandoverLogModel;
use App\Models\AgentMessageModel;
use App\Models\ConversationModel;
use App\Models\SseEventModel;
use App\Models\WaChannelModel;

class AgentChatService
{
    protected HandoverLogModel $handoverModel;
    protected AgentMessageModel $agentMessageModel;
    protected ConversationModel $conversationModel;
    protected SseEventModel $sseModel;
    protected WaChannelModel $channelModel;
    protected FonnteService $fonnteService;

    public function __construct()
    {
        $this->handoverModel = new HandoverLogModel();
        $this->agentMessageModel = new AgentMessageModel();
        $this->conversationModel = new ConversationModel();
        $this->sseModel = new SseEventModel();
        $this->channelModel = new WaChannelModel();
        $this->fonnteService = new FonnteService();
    }

    /**
     * Claim a handover by an agent
     *
     * @param int $handoverId
     * @param int $agentId
     * @return bool
     * @throws \Exception
     */
    public function claim(int $handoverId, int $agentId): bool
    {
        $handover = $this->handoverModel->find($handoverId);

        if (!$handover) {
            throw new \Exception('Handover tidak ditemukan.');
        }

        // Validate status
        if (!in_array($handover['status'], ['pending', 'in_progress'])) {
            throw new \Exception('Handover sudah selesai atau tidak aktif.');
        }

        // Update handover status
        $data = [
            'status' => 'in_progress',
            'mode' => 'agent',
            'claimed_by' => $agentId,
            'claimed_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->handoverModel->update($handoverId, $data)) {
            // Insert SSE event
            $this->sseModel->insert([
                'tenant_id' => $handover['tenant_id'],
                'channel_id' => $handover['channel_id'],
                'wa_number' => $handover['wa_number'],
                'event_type' => 'handover_claimed',
                'payload' => json_encode([
                    'agent_id' => $agentId,
                    'timestamp' => date('Y-m-d H:i:s'),
                ]),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Send a message from an agent to a customer
     *
     * @param int $handoverId
     * @param int $agentId
     * @param string $message
     * @return bool
     * @throws \Exception
     */
    public function sendMessage(int $handoverId, int $agentId, string $message): bool
    {
        $handover = $this->handoverModel->find($handoverId);

        if (!$handover) {
            throw new \Exception('Handover tidak ditemukan.');
        }

        // Validate agent is claimer
        if ($handover['claimed_by'] != $agentId) {
            throw new \Exception('Anda bukan agen yang mengambil alih percakapan ini.');
        }

        // Get channel info for Fonnte token
        $channel = $this->channelModel->find($handover['channel_id']);
        if (!$channel || empty($channel['fonnte_token'])) {
            throw new \Exception('Channel WA tidak valid atau token Fonnte tidak ditemukan.');
        }

        // Send via Fonnte
        $sent = $this->fonnteService->send($channel['fonnte_token'], $handover['wa_number'], $message);
        if (!$sent) {
            throw new \Exception('Gagal mengirim pesan melalui Fonnte.');
        }

        // Save to agent_messages
        $this->agentMessageModel->insert([
            'tenant_id' => $handover['tenant_id'],
            'channel_id' => $handover['channel_id'],
            'handover_id' => $handoverId,
            'wa_number' => $handover['wa_number'],
            'agent_id' => $agentId,
            'message' => $message,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        // Save to conversations (role: assistant)
        $this->conversationModel->insert([
            'tenant_id' => $handover['tenant_id'],
            'channel_id' => $handover['channel_id'],
            'wa_number' => $handover['wa_number'],
            'role' => 'assistant',
            'message' => $message,
        ]);

        // Insert SSE event
        $this->sseModel->insert([
            'tenant_id' => $handover['tenant_id'],
            'channel_id' => $handover['channel_id'],
            'wa_number' => $handover['wa_number'],
            'event_type' => 'new_message',
            'payload' => json_encode([
                'message' => $message,
                'sender' => 'agent',
                'agent_id' => $agentId,
                'timestamp' => date('Y-m-d H:i:s'),
            ]),
        ]);

        return true;
    }

    /**
     * Return conversation to AI mode
     *
     * @param int $handoverId
     * @return bool
     * @throws \Exception
     */
    public function returnToAi(int $handoverId): bool
    {
        $handover = $this->handoverModel->find($handoverId);

        if (!$handover) {
            throw new \Exception('Handover tidak ditemukan.');
        }

        $data = [
            'mode' => 'ai',
            'returned_to_ai_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->handoverModel->update($handoverId, $data)) {
            // Insert SSE event
            $this->sseModel->insert([
                'tenant_id' => $handover['tenant_id'],
                'channel_id' => $handover['channel_id'],
                'wa_number' => $handover['wa_number'],
                'event_type' => 'returned_to_ai',
                'payload' => json_encode([
                    'timestamp' => date('Y-m-d H:i:s'),
                ]),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Close/Complete a handover
     *
     * @param int $handoverId
     * @return bool
     * @throws \Exception
     */
    public function close(int $handoverId): bool
    {
        $handover = $this->handoverModel->find($handoverId);

        if (!$handover) {
            throw new \Exception('Handover tidak ditemukan.');
        }

        $data = [
            'status' => 'handled',
            'mode' => 'ai',
            'handled_at' => date('Y-m-d H:i:s'),
        ];

        return $this->handoverModel->update($handoverId, $data);
    }
}
