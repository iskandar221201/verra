<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table = 'conversations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'channel_id',
        'wa_number',
        'role',
        'message'
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
     * Get conversation history for a specific WA number on a channel
     *
     * @param int $tenantId
     * @param int $channelId
     * @param string $waNumber
     * @param int $limit
     * @return array
     */
    public function getHistory(int $tenantId, int $channelId, string $waNumber, int $limit = 10): array
    {
        $sql = "
            SELECT * FROM (
                SELECT role, message, created_at 
                FROM conversations 
                WHERE tenant_id = ? AND channel_id = ? AND wa_number = ?
                
                UNION ALL
                
                SELECT 'assistant' AS role, message, sent_at AS created_at 
                FROM agent_messages 
                WHERE tenant_id = ? AND channel_id = ? AND wa_number = ?
            ) AS combined_history
            ORDER BY created_at DESC
            LIMIT ?
        ";

        $query = $this->db->query($sql, [
            $tenantId,
            $channelId,
            $waNumber,
            $tenantId,
            $channelId,
            $waNumber,
            $limit
        ]);

        $results = $query->getResultArray();

        // Reverse to get chronological order (oldest to newest for the AI context)
        return array_reverse($results);
    }
}
