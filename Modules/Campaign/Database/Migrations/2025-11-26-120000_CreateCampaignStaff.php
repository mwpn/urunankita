<?php

namespace Modules\Campaign\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignStaff extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID Staff User',
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
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey(['campaign_id', 'user_id'], 'campaign_user_unique');
        
        $this->forge->createTable('campaign_staff');
    }

    public function down()
    {
        $this->forge->dropTable('campaign_staff');
    }
}

