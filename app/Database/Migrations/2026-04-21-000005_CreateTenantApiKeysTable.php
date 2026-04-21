<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantApiKeysTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'provider' => [
                'type' => 'ENUM',
                'constraint' => ['gemini', 'grok'],
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'api_key' => [
                'type' => 'TEXT',
            ],
            'priority' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'default' => 1,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => '1',
                'default' => '1',
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_error_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_error_msg' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'provider', 'is_active', 'priority'], false, false, 'idx_tenant_provider');
        $this->forge->createTable('tenant_api_keys');
    }

    public function down()
    {
        $this->forge->dropTable('tenant_api_keys');
    }
}
