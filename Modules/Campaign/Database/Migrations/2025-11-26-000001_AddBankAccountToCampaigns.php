<?php

namespace Modules\Campaign\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankAccountToCampaigns extends Migration
{
    public function up()
    {
        $fields = [
            'use_tenant_bank_account' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Gunakan rekening tenant sendiri (1=ya, 0=tidak/gunakan rekening platform)',
            ],
            'bank_account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID rekening bank yang dipilih (index dari array bank_accounts tenant)',
            ],
        ];

        // Check if columns exist before adding
        $db = \Config\Database::connect();
        $columns = $db->getFieldNames('campaigns');
        
        if (!in_array('use_tenant_bank_account', $columns)) {
            $this->forge->addColumn('campaigns', ['use_tenant_bank_account' => $fields['use_tenant_bank_account']]);
        }
        
        if (!in_array('bank_account_id', $columns)) {
            $this->forge->addColumn('campaigns', ['bank_account_id' => $fields['bank_account_id']]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', ['use_tenant_bank_account', 'bank_account_id']);
    }
}

