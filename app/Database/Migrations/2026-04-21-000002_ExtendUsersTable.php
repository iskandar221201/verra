<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendUsersTable extends Migration
{
    public function up()
    {
        $fields = [
            'tenant_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'id',
            ],
            'full_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'username',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => '1',
                'default' => '1',
                'after' => 'active',
            ],
        ];
        $this->forge->addColumn('users', $fields);
        $this->forge->addKey('tenant_id', false, false, 'idx_tenant_id');
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['tenant_id', 'full_name', 'is_active']);
    }
}
