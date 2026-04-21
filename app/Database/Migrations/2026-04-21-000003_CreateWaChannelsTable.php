<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWaChannelsTable extends Migration
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
            'uuid' => [
                'type' => 'VARCHAR',
                'constraint' => '36',
                'unique' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'wa_number' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'fonnte_token' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => '1',
                'default' => '1',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('wa_channels');
    }

    public function down()
    {
        $this->forge->dropTable('wa_channels');
    }
}
