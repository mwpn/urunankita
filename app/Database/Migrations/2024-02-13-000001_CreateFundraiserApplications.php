<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFundraiserApplications extends Migration
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
            'full_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'ktp_document' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'entity_type' => [
                'type' => 'ENUM',
                'constraint' => ['personal', 'foundation'],
                'default' => 'personal',
            ],
            'foundation_document' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'youtube_channel' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'instagram' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'social_youtube' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'twitter' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'facebook' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'reviewed', 'approved', 'rejected'],
                'default' => 'pending',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('fundraiser_applications', true);
    }

    public function down()
    {
        $this->forge->dropTable('fundraiser_applications', true);
    }
}

