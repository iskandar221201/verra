<?php

namespace App\Services;

use App\Models\HandoverKeywordModel;
use App\Models\HandoverLogModel;

class HandoverService
{
    protected HandoverKeywordModel $keywordModel;
    protected HandoverLogModel $handoverModel;

    public function __construct()
    {
        $this->keywordModel = new HandoverKeywordModel();
        $this->handoverModel = new HandoverLogModel();
    }

    /**
     * Check if the message contains any active handover keyword
     *
     * @param int $tenantId
     * @param string $message
     * @return bool
     */
    public function checkKeyword(int $tenantId, string $message): bool
    {
        // Fetch active keywords for the tenant
        $keywords = $this->keywordModel
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->findAll();

        if (empty($keywords)) {
            return false;
        }

        // Case-insensitive check
        $messageLower = mb_strtolower($message);

        foreach ($keywords as $kw) {
            if (mb_strpos($messageLower, mb_strtolower($kw['keyword'])) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new handover record
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber
     * @param string $message Trigger message
     * @param string $triggerType 'keyword' | 'ai_unable' | 'manual'
     * @return int Inserted handover ID
     */
    public function createHandover(int $tenantId, int $channelId, string $waNumber, string $message, string $triggerType): int
    {
        $this->handoverModel->insert([
            'tenant_id' => $tenantId,
            'channel_id' => $channelId,
            'wa_number' => $waNumber,
            'trigger_message' => $message,
            'trigger_type' => $triggerType,
            'status' => 'pending',
            'mode' => 'ai',
        ]);

        return $this->handoverModel->getInsertID();
    }

    /**
     * Check if there is an active handover with mode=agent for a specific WA number
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber
     * @return array|null Handover record or null
     */
    public function hasActiveAgentHandover(int $tenantId, int $channelId, string $waNumber): ?array
    {
        $handover = $this->handoverModel
            ->where('tenant_id', $tenantId)
            ->where('channel_id', $channelId)
            ->where('wa_number', $waNumber)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('mode', 'agent')
            ->orderBy('created_at', 'DESC')
            ->first();

        return $handover ?: null;
    }
}
