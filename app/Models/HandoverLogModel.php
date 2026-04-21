<?php

namespace App\Models;

use CodeIgniter\Model;

class HandoverLogModel extends Model
{
    protected $table = 'handover_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'channel_id',
        'wa_number',
        'trigger_message',
        'trigger_type',
        'status',
        'mode',
        'claimed_by',
        'claimed_at',
        'returned_to_ai_at',
        'handled_at',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';

    // Callbacks
    protected $beforeInsert = ['setTenantId'];

    /**
     * Ensure tenant_id is set before inserting
     *
     * @param array $data
     * @return array
     */
    protected function setTenantId(array $data)
    {
        if (!isset($data['data']['tenant_id']) && defined('TENANT_ID')) {
            $data['data']['tenant_id'] = TENANT_ID;
        }

        return $data;
    }

    /**
     * Scope query to current tenant
     *
     * @return $this
     */
    public function forTenant()
    {
        if (defined('TENANT_ID')) {
            $this->where('tenant_id', TENANT_ID);
        }
        return $this;
    }

    /**
     * Get active handover for a specific WA number on a channel
     * Active = status is 'pending' or 'in_progress'
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber
     * @return array|null
     */
    public function getActiveHandover(int $tenantId, int $channelId, string $waNumber): ?array
    {
        $result = $this->where('tenant_id', $tenantId)
            ->where('channel_id', $channelId)
            ->where('wa_number', $waNumber)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'DESC')
            ->first();

        return $result ?: null;
    }
}
