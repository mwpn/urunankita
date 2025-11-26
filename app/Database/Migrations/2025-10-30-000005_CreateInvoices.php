<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoices extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'tenant_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
            'amount' => [ 'type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00' ],
            'status' => [ 'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid' ],
            'paid_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('invoices');
    }

    public function down()
    {
        $this->forge->dropTable('invoices');
    }
}


