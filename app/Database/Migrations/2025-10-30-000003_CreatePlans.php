<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlans extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true ],
            'name' => [ 'type' => 'VARCHAR', 'constraint' => 100, 'unique' => true ],
            'price' => [ 'type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00' ],
            'description' => [ 'type' => 'TEXT', 'null' => true ],
            'features' => [ 'type' => 'TEXT', 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('plans');
    }

    public function down()
    {
        $this->forge->dropTable('plans');
    }
}


