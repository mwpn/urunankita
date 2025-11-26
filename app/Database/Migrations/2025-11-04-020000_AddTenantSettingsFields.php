<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTenantSettingsFields extends Migration
{
    public function up()
    {
        $fields = [
            'can_create_without_verification' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Tenant bisa membuat urunan tanpa verifikasi (1=ya, 0=tidak)',
            ],
            'can_use_own_bank_account' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Tenant bisa menggunakan rekening sendiri untuk donasi (1=ya, 0=tidak)',
            ],
        ];

        // Check if columns exist before adding
        $db = \Config\Database::connect();
        $columns = $db->getFieldNames('tenants');
        
        if (!in_array('can_create_without_verification', $columns)) {
            $this->forge->addColumn('tenants', ['can_create_without_verification' => $fields['can_create_without_verification']]);
        }
        
        if (!in_array('can_use_own_bank_account', $columns)) {
            $this->forge->addColumn('tenants', ['can_use_own_bank_account' => $fields['can_use_own_bank_account']]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tenants', ['can_create_without_verification', 'can_use_own_bank_account']);
    }
}

