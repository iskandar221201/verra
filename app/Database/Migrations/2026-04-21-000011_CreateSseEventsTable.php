<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSseEventsTable extends Migration
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
            'event_type' => [
                'type' => 'ENUM',
                'constraint' => ['new_message', 'handover_created', 'handover_claimed', 'returned_to_ai'],
            ],
            'payload' => [
                'type' => 'JSON',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'channel_id', 'created_at'], false, false, 'idx_lookup');
        $this->forge->createTable('sse_events');
    }

    public function down()
    {
        $this->forge->dropTable('sse_events');
    }
}
