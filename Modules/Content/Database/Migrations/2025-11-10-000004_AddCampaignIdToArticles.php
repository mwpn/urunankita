<?php

namespace Modules\Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCampaignIdToArticles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('articles', [
            'campaign_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tenant_id',
                'comment'    => 'ID campaign yang terkait dengan artikel ini',
            ],
        ]);
        
        $this->forge->addKey('campaign_id');
    }

    public function down()
    {
        $this->forge->dropColumn('articles', 'campaign_id');
    }
}

