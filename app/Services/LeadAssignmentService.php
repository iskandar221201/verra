<?php

namespace App\Services;

use App\Models\ConversationModel;
use App\Models\LeadAssignmentModel;
use App\Models\LeadSalespersonModel;
use App\Models\TenantConfigModel;

class LeadAssignmentService
{
    protected LeadSalespersonModel $salespersonModel;
    protected LeadAssignmentModel $assignmentModel;
    protected TenantConfigModel $configModel;
    protected FonnteService $fonnteService;

    public function __construct()
    {
        $this->salespersonModel = new LeadSalespersonModel();
        $this->assignmentModel = new LeadAssignmentModel();
        $this->configModel = new TenantConfigModel();
        $this->fonnteService = new FonnteService();
    }

    /**
     * Auto-assign a lead using round-robin.
     * Called from WebhookProcessorService when a new customer sends their first message.
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber Customer WA number (raw)
     * @param string $fonnteToken Channel's Fonnte token
     * @return array|null Assignment record or null if skipped
     */
    public function autoAssign(int $tenantId, int $channelId, string $waNumber, string $fonnteToken): ?array
    {
        // Load tenant config
        $config = $this->configModel->where('tenant_id', $tenantId)->first();

        if (!$config || !$config['lead_auto_assign']) {
            return null;
        }

        $groupId = $config['lead_wa_group_id'] ?? '';
        if (empty($groupId)) {
            log_message('warning', "[LeadAssignment] Auto-assign ON but no group ID set for tenant {$tenantId}");
            return null;
        }

        // Check if this is truly the first message (count conversations for this customer)
        $convModel = new ConversationModel();
        $msgCount = $convModel->where('tenant_id', $tenantId)
            ->where('channel_id', $channelId)
            ->where('wa_number', $waNumber)
            ->countAllResults();

        // If more than 1 message exists, this is a returning customer — skip
        if ($msgCount > 1) {
            return null;
        }

        // Get next salesperson via round-robin
        $salesperson = $this->getNextSalesperson($tenantId);
        if (!$salesperson) {
            log_message('warning', "[LeadAssignment] No active salespersons for tenant {$tenantId}, skipping auto-assign");
            return null;
        }

        // Normalize customer phone number
        helper('phone');
        $normalizedWa = normalizePhoneNumber($waNumber);

        // Insert assignment log
        $assignmentData = [
            'tenant_id' => $tenantId,
            'channel_id' => $channelId,
            'wa_number' => $normalizedWa,
            'salesperson_id' => $salesperson['id'],
            'assigned_by' => 'auto',
            'assigned_by_user_id' => null,
            'notified' => 0,
        ];

        $this->assignmentModel->insert($assignmentData);
        $assignmentId = $this->assignmentModel->getInsertID();

        // Build and send notification
        $message = $this->buildNotificationMessage($normalizedWa, $salesperson['wa_number']);
        $notified = $this->fonnteService->sendToGroup($fonnteToken, $groupId, $message);

        // Update notified status
        if ($notified) {
            $this->assignmentModel->update($assignmentId, ['notified' => 1]);
        }

        $assignmentData['id'] = $assignmentId;
        $assignmentData['notified'] = $notified ? 1 : 0;
        return $assignmentData;
    }

    /**
     * Manually assign a lead to a specific salesperson.
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber Customer WA number (raw)
     * @param int $salespersonId
     * @param int $userId Current user performing the assignment
     * @param string $fonnteToken Channel's Fonnte token
     * @return array Result with 'success' and 'message' keys
     */
    public function manualAssign(int $tenantId, int $channelId, string $waNumber, int $salespersonId, int $userId, string $fonnteToken): array
    {
        // Validate salesperson belongs to tenant and is active
        $salesperson = $this->salespersonModel
            ->where('id', $salespersonId)
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->first();

        if (!$salesperson) {
            return ['success' => false, 'message' => 'Sales tidak ditemukan atau tidak aktif.'];
        }

        // Load tenant config for group ID
        $config = $this->configModel->where('tenant_id', $tenantId)->first();
        $groupId = $config['lead_wa_group_id'] ?? '';

        // Normalize customer phone number
        helper('phone');
        $normalizedWa = normalizePhoneNumber($waNumber);

        // Insert assignment log (append, never overwrite)
        $assignmentData = [
            'tenant_id' => $tenantId,
            'channel_id' => $channelId,
            'wa_number' => $normalizedWa,
            'salesperson_id' => $salespersonId,
            'assigned_by' => 'manual',
            'assigned_by_user_id' => $userId,
            'notified' => 0,
        ];

        $this->assignmentModel->insert($assignmentData);
        $assignmentId = $this->assignmentModel->getInsertID();

        // Send notification if group ID is set
        $notified = false;
        if (!empty($groupId) && !empty($fonnteToken)) {
            $message = $this->buildNotificationMessage($normalizedWa, $salesperson['wa_number']);
            $notified = $this->fonnteService->sendToGroup($fonnteToken, $groupId, $message);

            if ($notified) {
                $this->assignmentModel->update($assignmentId, ['notified' => 1]);
            }
        }

        return [
            'success' => true,
            'message' => 'Lead berhasil di-assign ke ' . $salesperson['name'],
            'notified' => $notified,
        ];
    }

    /**
     * Get the next salesperson in round-robin order.
     * Atomically increments the counter in tenant_configs.
     *
     * @param int $tenantId
     * @return array|null Salesperson record or null
     */
    public function getNextSalesperson(int $tenantId): ?array
    {
        $activeSales = $this->salespersonModel->getActiveSorted($tenantId);

        if (empty($activeSales)) {
            return null;
        }

        // Get current counter
        $config = $this->configModel->where('tenant_id', $tenantId)->first();
        $counter = $config['lead_round_robin_counter'] ?? 0;

        // Pick salesperson
        $index = $counter % count($activeSales);
        $selected = $activeSales[$index];

        // Atomically increment counter
        $db = \Config\Database::connect();
        $db->query("UPDATE tenant_configs SET lead_round_robin_counter = lead_round_robin_counter + 1 WHERE tenant_id = ?", [$tenantId]);

        return $selected;
    }

    /**
     * Build the WhatsApp group notification message.
     *
     * @param string $customerWa Normalized customer WA (628xxx)
     * @param string $salespersonWa Salesperson WA (628xxx)
     * @return string Formatted notification
     */
    public function buildNotificationMessage(string $customerWa, string $salespersonWa): string
    {
        return "🔔 Leads Baru\nCustomer: +{$customerWa}\nAssigned to: @{$salespersonWa}";
    }
}
