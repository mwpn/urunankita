<?php

namespace Modules\Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationToMenuItems extends Migration
{
    public function up()
    {
        // Add location column to menu_items table
        // 'header' for header menu, 'footer' for footer menu
        $fields = [
            'location' => [
                'type' => 'ENUM',
                'constraint' => ['header', 'footer'],
                'default' => 'header',
                'null' => false,
                'after' => 'tenant_id',
            ],
        ];

        $this->forge->addColumn('menu_items', $fields);

        // Update existing records to have 'header' as default
        $this->db->table('menu_items')
            ->where('location IS NULL')
            ->update(['location' => 'header']);
    }

    public function down()
    {
        $this->forge->dropColumn('menu_items', 'location');
    }
}

