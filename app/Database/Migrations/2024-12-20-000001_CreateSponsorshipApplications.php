<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSponsorshipApplications extends Migration
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
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'pic_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'pic_position' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
            ],
            'sponsor_type' => [
                'type' => 'ENUM',
                'constraint' => ['donasi', 'barang', 'jasa', 'kolaborasi'],
                'default' => 'donasi',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'categories' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'public_visibility' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no'],
                'default' => 'yes',
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'website_link' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'reason' => [
                'type' => 'TEXT',
            ],
            'expectations' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'special_terms' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'partnership_letter' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'company_profile' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
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
        $this->forge->createTable('sponsorship_applications');
    }

    public function down()
    {
        $this->forge->dropTable('sponsorship_applications');
    }
}

