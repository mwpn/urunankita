<?php

namespace Modules\Tenant\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 100 ],
            'email' => [ 'type' => 'VARCHAR', 'constraint' => 191 ],
            'password' => [ 'type' => 'VARCHAR', 'constraint' => 255 ],
            'role' => [ 'type' => 'VARCHAR', 'constraint' => 50, 'null' => true ],
            'status' => [ 'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active' ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
