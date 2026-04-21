<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantConfigsTable extends Migration
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
                'unique' => true,
            ],
            'ai_provider' => [
                'type' => 'ENUM',
                'constraint' => ['gemini', 'grok'],
                'default' => 'gemini',
            ],
            'gemini_model' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'default' => 'gemini-1.5-flash',
            ],
            'grok_model' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'default' => 'grok-beta',
            ],
            'system_prompt' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'max_history' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'default' => 10,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tenant_configs');
    }

    public function down()
    {
        $this->forge->dropTable('tenant_configs');
    }
}
