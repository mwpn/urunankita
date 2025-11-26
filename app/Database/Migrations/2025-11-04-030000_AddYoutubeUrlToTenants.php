<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddYoutubeUrlToTenants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tenants', [
            'youtube_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'URL Channel YouTube Konten Kreator',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tenants', 'youtube_url');
    }
}

