<?php

namespace App\Models;

use CodeIgniter\Model;

class SseEventModel extends Model
{
    protected $table = 'sse_events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'channel_id',
        'wa_number',
        'event_type',
        'payload'
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
     * Get new SSE events after a given event ID
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber
     * @param int $lastEventId
     * @return array
     */
    public function getNewEvents(int $tenantId, int $channelId, string $waNumber, int $lastEventId = 0): array
    {
        $builder = $this->where('tenant_id', $tenantId)
            ->where('channel_id', $channelId)
            ->where('wa_number', $waNumber);

        if ($lastEventId > 0) {
            $builder->where('id >', $lastEventId);
        }

        return $builder->orderBy('id', 'ASC')
            ->findAll();
    }
}
