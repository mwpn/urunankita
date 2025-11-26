<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 150, 'unique' => true ],
            'description' => [ 'type' => 'VARCHAR', 'constraint' => 191, 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('permissions');
    }

    public function down()
    {
        $this->forge->dropTable('permissions');
    }
}


