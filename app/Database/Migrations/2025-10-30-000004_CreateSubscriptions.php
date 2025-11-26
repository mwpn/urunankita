<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'tenant_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
            'plan_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true ],
            'started_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'expired_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'status' => [ 'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active' ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('subscriptions');
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions');
    }
}


