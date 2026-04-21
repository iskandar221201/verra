<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHandoverLogTable extends Migration
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
            'channel_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'wa_number' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'trigger_message' => [
                'type' => 'TEXT',
            ],
            'trigger_type' => [
                'type' => 'ENUM',
                'constraint' => ['keyword', 'ai_unable', 'manual'],
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'handled'],
                'default' => 'pending',
            ],
            'mode' => [
                'type' => 'ENUM',
                'constraint' => ['ai', 'agent'],
                'default' => 'ai',
            ],
            'claimed_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'claimed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'returned_to_ai_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'handled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'status']);
        $this->forge->addKey(['tenant_id', 'wa_number']);
        $this->forge->createTable('handover_log');
    }

    public function down()
    {
        $this->forge->dropTable('handover_log');
    }
}
