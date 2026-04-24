<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLeadConfigToTenantConfigs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tenant_configs', [
            'lead_auto_assign' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
                'after' => 'max_history',
            ],
            'lead_wa_group_id' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'lead_auto_assign',
            ],
            'lead_round_robin_counter' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'lead_wa_group_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tenant_configs', ['lead_auto_assign', 'lead_wa_group_id', 'lead_round_robin_counter']);
    }
}
