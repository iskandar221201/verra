<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHandoverKeywordsTable extends Migration
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
            'keyword' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => '1',
                'default' => '1',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('handover_keywords');
    }

    public function down()
    {
        $this->forge->dropTable('handover_keywords');
    }
}
