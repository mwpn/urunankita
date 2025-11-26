<?php

namespace Modules\Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSponsors extends Migration
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
                'comment'    => 'NULL untuk platform sponsors, tenant_id untuk tenant-specific sponsors',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Nama sponsor',
            ],
            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'comment'    => 'Path to sponsor logo image',
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
                'comment'    => 'Website URL sponsor (optional)',
            ],
            'order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Display order (lower number = higher priority)',
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('active');
        $this->forge->addKey('order');
        $this->forge->createTable('sponsors');
    }

    public function down()
    {
        $this->forge->dropTable('sponsors');
    }
}

