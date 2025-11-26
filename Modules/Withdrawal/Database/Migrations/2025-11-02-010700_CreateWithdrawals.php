<?php

namespace Modules\Withdrawal\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWithdrawals extends Migration
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
            'beneficiary_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Penerima Urunan',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'comment' => 'Jumlah yang disalurkan',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'pending',
                'comment' => 'pending, approved, processing, completed, rejected, cancelled',
            ],
            'requested_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User yang request penyaluran',
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Tim UrunanKita yang approve',
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Alasan penolakan',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan penyaluran',
            ],
            'transfer_proof' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'Bukti transfer',
            ],
            'requested_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
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
        $this->forge->addKey('beneficiary_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->createTable('withdrawals');
    }

    public function down()
    {
        $this->forge->dropTable('withdrawals');
    }
}

