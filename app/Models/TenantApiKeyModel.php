<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantApiKeyModel extends Model
{
    protected $table = 'tenant_api_keys';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'provider',
        'label',
        'api_key',
        'priority',
        'is_active',
        'last_used_at',
        'last_error_at',
        'last_error_msg'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    // Callbacks
    protected $beforeInsert = ['setTenantId', 'encryptApiKey'];
    protected $beforeUpdate = ['encryptApiKey'];

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
     * Encrypt API key before saving
     *
     * @param array $data
     * @return array
     */
    protected function encryptApiKey(array $data)
    {
        if (isset($data['data']['api_key'])) {
            $encrypter = \Config\Services::encrypter();
            $data['data']['api_key'] = base64_encode($encrypter->encrypt($data['data']['api_key']));
        }

        return $data;
    }

    /**
     * Decrypt API key
     *
     * @param string $encryptedKey
     * @return string
     */
    public function decryptKey(string $encryptedKey)
    {
        $encrypter = \Config\Services::encrypter();
        return $encrypter->decrypt(base64_decode($encryptedKey));
    }
}
