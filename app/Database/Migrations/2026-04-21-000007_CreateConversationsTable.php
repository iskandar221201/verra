<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversationsTable extends Migration
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
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['user', 'assistant'],
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'channel_id', 'wa_number'], false, false, 'idx_lookup');
        $this->forge->addKey('created_at');
        $this->forge->createTable('conversations');
    }

    public function down()
    {
        $this->forge->dropTable('conversations');
    }
}
