<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKnowledgeBaseTable extends Migration
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
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'content' => [
                'type' => 'TEXT',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => '1',
                'default' => '1',
            ],
            'sort_order' => [
                'type' => 'INT',
                'default' => 0,
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
        $this->forge->addKey(['tenant_id', 'is_active']);
        $this->forge->createTable('knowledge_base');
    }

    public function down()
    {
        $this->forge->dropTable('knowledge_base');
    }
}
