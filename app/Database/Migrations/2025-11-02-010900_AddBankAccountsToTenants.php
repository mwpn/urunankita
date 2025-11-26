<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankAccountsToTenants extends Migration
{
    public function up()
    {
        $fields = [
            'bank_accounts' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array: List rekening bank tenant untuk menerima donasi',
            ],
        ];

        $this->forge->addColumn('tenants', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tenants', 'bank_accounts');
    }
}

