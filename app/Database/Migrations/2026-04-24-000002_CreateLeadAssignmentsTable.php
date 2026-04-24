<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadAssignmentsTable extends Migration
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
            'salesperson_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'assigned_by' => [
                'type' => 'ENUM',
                'constraint' => ['auto', 'manual'],
                'default' => 'auto',
            ],
            'assigned_by_user_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'notified' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'channel_id', 'wa_number'], false, false, 'idx_tenant_customer');
        $this->forge->addKey('salesperson_id', false, false, 'idx_salesperson');
        $this->forge->createTable('lead_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('lead_assignments');
    }
}
