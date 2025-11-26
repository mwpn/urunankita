<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentMethods extends Migration
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
                'comment' => 'ID tenant pemilik payment method',
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Kode unik payment method (bank_transfer, e_wallet, dll)',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nama payment method',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'bank-transfer',
                'comment' => 'Tipe payment method (bank-transfer, e-wallet, qris, dll)',
            ],
            'enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Status aktif (1=aktif, 0=nonaktif)',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi payment method',
            ],
            'provider' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Provider payment method (BCA, Mandiri, OVO, dll)',
            ],
            'admin_fee_percent' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
                'comment' => 'Biaya admin dalam persen',
            ],
            'admin_fee_fixed' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Biaya admin tetap (Rp)',
            ],
            'require_verification' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Perlu verifikasi manual (1=ya, 0=tidak)',
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
        $this->forge->addKey('code');
        $this->forge->addKey('enabled');
        
        // Unique constraint: tenant_id + code (satu tenant tidak boleh punya code yang sama)
        $this->forge->addUniqueKey(['tenant_id', 'code'], 'tenant_code_unique');
        
        // Index untuk query aktif per tenant
        $this->forge->addKey(['tenant_id', 'enabled'], false, false, 'tenant_enabled_idx');

        $this->forge->createTable('payment_methods');
    }

    public function down()
    {
        $this->forge->dropTable('payment_methods');
    }
}
