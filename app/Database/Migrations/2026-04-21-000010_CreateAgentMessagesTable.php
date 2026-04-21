<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentMessagesTable extends Migration
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
            'handover_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'wa_number' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'agent_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('handover_id');
        $this->forge->addKey(['tenant_id', 'channel_id', 'wa_number'], false, false, 'idx_lookup');
        $this->forge->createTable('agent_messages');
    }

    public function down()
    {
        $this->forge->dropTable('agent_messages');
    }
}
