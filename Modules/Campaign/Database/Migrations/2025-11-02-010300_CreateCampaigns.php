<?php

namespace Modules\Campaign\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaigns extends Migration
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
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Penggalang Urunan (Tenant/Campaign Creator)',
            ],
            'creator_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User yang membuat urunan',
            ],
            'beneficiary_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Penerima Urunan',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Judul Urunan',
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
                'comment' => 'URL slug',
            ],
            'description' => [
                'type' => 'TEXT',
                'comment' => 'Deskripsi lengkap urunan',
            ],
            'campaign_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'target_based',
                'comment' => 'target_based: ada target dana, ongoing: urunan terus berjalan',
            ],
            'target_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'null' => true,
                'comment' => 'Target dana yang dibutuhkan (wajib untuk target_based, null untuk ongoing)',
            ],
            'current_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Total Urunan Terkumpul',
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Kategori: Pendidikan, Kesehatan, Bencana, dll',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'draft',
                'comment' => 'draft, pending_verification, active, completed, rejected, closed',
            ],
            'featured_image' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'Foto utama urunan',
            ],
            'images' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array: Galeri foto',
            ],
            'deadline' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Batas waktu penggalangan',
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu verifikasi oleh Tim UrunanKita',
            ],
            'verified_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Admin yang verifikasi',
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Alasan penolakan jika rejected',
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu urunan selesai',
            ],
            'views_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Jumlah view',
            ],
            'donors_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Jumlah Orang Baik yang berdonasi',
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
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('creator_user_id');
        $this->forge->addKey('beneficiary_id');
        // slug already has unique key from field definition, no need to add again
        $this->forge->addKey('status');
        $this->forge->addKey('category');
        $this->forge->addKey('campaign_type');
        $this->forge->addKey('created_at');
        
        // Index untuk query performance
        $this->forge->addKey(['status', 'verified_at'], false, false, 'status_verified');
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'tenant_status');
        $this->forge->addKey(['campaign_type', 'status'], false, false, 'type_status');

        $this->forge->createTable('campaigns');
    }

    public function down()
    {
        $this->forge->dropTable('campaigns');
    }
}

