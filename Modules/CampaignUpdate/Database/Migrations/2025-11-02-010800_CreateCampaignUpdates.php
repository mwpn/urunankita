<?php

namespace Modules\CampaignUpdate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignUpdates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID Urunan',
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Penggalang Urunan',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Judul update (opsional)',
            ],
            'content' => [
                'type' => 'TEXT',
                'comment' => 'Isi Laporan Kabar Terbaru',
            ],
            'images' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array: Foto update',
            ],
            'author_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User yang membuat update',
            ],
            'is_pinned' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Pin update penting',
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
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('created_at');
        
        // Index untuk query
        $this->forge->addKey(['campaign_id', 'created_at'], false, false, 'campaign_date');

        $this->forge->createTable('campaign_updates');
    }

    public function down()
    {
        $this->forge->dropTable('campaign_updates');
    }
}

