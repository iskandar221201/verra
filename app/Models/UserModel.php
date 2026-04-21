<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = array_merge($this->allowedFields, [
            'tenant_id',
            'full_name',
            'is_active',
        ]);

        // Add beforeInsert callback to set tenant_id
        $this->beforeInsert[] = 'setTenantId';
    }

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
            $this->where($this->table . '.tenant_id', TENANT_ID);
        }
        return $this;
    }

    /**
     * Filter by active status
     *
     * @return $this
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }
}
