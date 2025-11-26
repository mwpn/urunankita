<?php

namespace Modules\Beneficiary\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBeneficiaries extends Migration
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
                'comment' => 'Penggalang Urunan',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'individual',
                'comment' => 'individual, family, institution, school, project',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nama Penerima Urunan',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi penerima',
            ],
            'identity_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'NIK/KTP untuk individu',
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bank_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Nama bank untuk penyaluran',
            ],
            'bank_account' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Nomor rekening',
            ],
            'bank_account_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Nama pemilik rekening',
            ],
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'active',
                'comment' => 'active, inactive',
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
        $this->forge->addKey('type');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->createTable('beneficiaries');
    }

    public function down()
    {
        $this->forge->dropTable('beneficiaries');
    }
}

