<?php

namespace Modules\Donation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDonations extends Migration
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
                'comment' => 'Tenant dari campaign',
            ],
            'donor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID user jika terdaftar, NULL untuk anonim',
            ],
            'donor_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Nama Orang Baik (jika anonim bisa kosong)',
            ],
            'donor_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Email untuk notifikasi',
            ],
            'donor_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'comment' => 'Jumlah donasi',
            ],
            'is_anonymous' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Orang Baik Tanpa Nama',
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'bank_transfer, e_wallet, qris, dll',
            ],
            'bank_account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'ID rekening tenant yang digunakan (index dari bank_accounts array)',
            ],
            'confirmed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User tenant yang konfirmasi pembayaran manual',
            ],
            'confirmed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu konfirmasi pembayaran manual',
            ],
            'payment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
                'comment' => 'pending, paid, failed, cancelled',
            ],
            'payment_proof' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'Bukti pembayaran',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Pesan/doa dari Orang Baik',
            ],
            'invoice_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Link ke invoice jika ada',
            ],
            'paid_at' => [
                'type' => 'DATETIME',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('donor_id');
        $this->forge->addKey('payment_status');
        $this->forge->addKey('created_at');
        
        // Index untuk query
        $this->forge->addKey(['campaign_id', 'payment_status'], false, false, 'campaign_payment');
        $this->forge->addKey(['tenant_id', 'created_at'], false, false, 'tenant_date');

        $this->forge->createTable('donations');
    }

    public function down()
    {
        $this->forge->dropTable('donations');
    }
}

