<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentMessageModel extends Model
{
    protected $table = 'agent_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'channel_id',
        'handover_id',
        'wa_number',
        'agent_id',
        'message',
        'sent_at'
    ];

    // Dates
    protected $useTimestamps = false;

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
}
