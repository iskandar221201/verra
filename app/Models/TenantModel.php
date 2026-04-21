<?php

namespace App\Models;

use App\Traits\UuidTrait;

class TenantModel extends \CodeIgniter\Model
{
    use UuidTrait;

    protected $table = 'tenants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['uuid', 'name', 'slug', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Callbacks
    protected $beforeInsert = ['generateUuid'];
}
