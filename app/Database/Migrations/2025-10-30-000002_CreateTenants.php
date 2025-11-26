<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 150 ],
            'slug' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'unique' => true ],
            'db_name' => [ 'type' => 'VARCHAR', 'constraint' => 150 ],
            'domain' => [ 'type' => 'VARCHAR', 'constraint' => 191, 'null' => true ],
            'owner_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true ],
            'status' => [ 'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active' ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tenants');
    }

    public function down()
    {
        $this->forge->dropTable('tenants');
    }
}


