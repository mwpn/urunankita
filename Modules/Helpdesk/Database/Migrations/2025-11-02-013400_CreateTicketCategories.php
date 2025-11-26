<?php

namespace Modules\Helpdesk\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketCategories extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nama kategori: Technical, Billing, General, dll',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'icon' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Icon untuk kategori',
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Color code untuk UI',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
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
        $this->forge->addKey('is_active');
        $this->forge->addKey('sort_order');

        $this->forge->createTable('ticket_categories');

        // Insert default categories
        $categories = [
            ['name' => 'Technical', 'description' => 'Masalah teknis dan bug', 'icon' => 'fa-cog', 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'Billing', 'description' => 'Masalah pembayaran dan invoice', 'icon' => 'fa-credit-card', 'color' => '#10B981', 'sort_order' => 2],
            ['name' => 'General', 'description' => 'Pertanyaan umum', 'icon' => 'fa-question-circle', 'color' => '#6B7280', 'sort_order' => 3],
            ['name' => 'Feature Request', 'description' => 'Permintaan fitur baru', 'icon' => 'fa-lightbulb', 'color' => '#F59E0B', 'sort_order' => 4],
        ];

        foreach ($categories as $cat) {
            $cat['created_at'] = date('Y-m-d H:i:s');
            $cat['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('ticket_categories')->insert($cat);
        }
    }

    public function down()
    {
        $this->forge->dropTable('ticket_categories');
    }
}

