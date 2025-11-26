<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationToCampaigns extends Migration
{
    public function up()
    {
        $fields = [
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
                'comment' => 'Koordinat latitude lokasi',
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
                'comment' => 'Koordinat longitude lokasi',
            ],
            'location_address' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'Alamat lengkap lokasi',
            ],
        ];

        $this->forge->addColumn('campaigns', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', ['latitude', 'longitude', 'location_address']);
    }
}
