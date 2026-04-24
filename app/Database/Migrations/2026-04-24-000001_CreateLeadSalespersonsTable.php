<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadSalespersonsTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'wa_number' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'sort_order' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'default' => 0,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 1,
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
        $this->forge->addKey(['tenant_id', 'is_active', 'sort_order'], false, false, 'idx_tenant_active');
        $this->forge->createTable('lead_salespersons');
    }

    public function down()
    {
        $this->forge->dropTable('lead_salespersons');
    }
}
