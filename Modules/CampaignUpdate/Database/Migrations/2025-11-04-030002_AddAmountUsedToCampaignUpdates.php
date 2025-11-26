<?php

namespace Modules\CampaignUpdate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAmountUsedToCampaignUpdates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaign_updates', [
            'amount_used' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'default' => null,
                'after' => 'content',
                'comment' => 'Jumlah dana yang digunakan dalam laporan ini',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaign_updates', 'amount_used');
    }
}

