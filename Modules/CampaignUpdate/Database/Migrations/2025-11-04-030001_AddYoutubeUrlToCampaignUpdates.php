<?php

namespace Modules\CampaignUpdate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddYoutubeUrlToCampaignUpdates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaign_updates', [
            'youtube_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'URL Video YouTube untuk Laporan Transparansi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaign_updates', 'youtube_url');
    }
}

