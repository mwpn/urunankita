<?php

namespace Modules\Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentIdToMenuItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('menu_items', [
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
                'comment'    => 'ID parent menu item untuk sub menu (NULL jika menu utama)',
            ],
        ]);

        // Add index for parent_id
        $this->forge->addKey('parent_id');
    }

    public function down()
    {
        $this->forge->dropColumn('menu_items', 'parent_id');
    }
}

