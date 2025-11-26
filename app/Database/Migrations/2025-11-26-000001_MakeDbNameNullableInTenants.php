<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeDbNameNullableInTenants extends Migration
{
    public function up()
    {
        // Make db_name nullable since we're using single database now
        $this->forge->modifyColumn('tenants', [
            'db_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Revert db_name to NOT NULL
        $this->forge->modifyColumn('tenants', [
            'db_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => false,
            ],
        ]);
    }
}

