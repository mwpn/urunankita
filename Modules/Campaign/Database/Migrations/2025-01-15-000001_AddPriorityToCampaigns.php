<?php

namespace Modules\Campaign\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriorityToCampaigns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaigns', [
            'is_priority' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'comment' => 'Urunan prioritas (1 = prioritas, 0 = tidak)',
                'after' => 'status',
            ],
        ]);

        // Add index for better query performance
        $this->forge->addKey(['is_priority', 'status'], false, false, 'priority_status');
    }

    public function down()
    {
        $this->forge->dropKey('campaigns', 'priority_status');
        $this->forge->dropColumn('campaigns', 'is_priority');
    }
}

