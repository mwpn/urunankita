<?php

namespace Modules\Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL untuk menu platform (admin), tenant_id untuk menu tenant',
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_external' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 jika link external, 0 jika internal',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('order');
        $this->forge->createTable('menu_items');
    }

    public function down()
    {
        $this->forge->dropTable('menu_items');
    }
}

