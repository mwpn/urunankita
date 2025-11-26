<?php

namespace Modules\Announcement\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnnouncements extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Judul Pengumuman',
            ],
            'content' => [
                'type' => 'TEXT',
                'comment' => 'Isi Pengumuman',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'info',
                'comment' => 'info, warning, success, error',
            ],
            'priority' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'normal',
                'comment' => 'low, normal, high, urgent',
            ],
            'is_published' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0: draft, 1: published',
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu publish',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Tanggal kadaluarsa pengumuman',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Admin yang membuat pengumuman',
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
        $this->forge->addKey('is_published');
        $this->forge->addKey('priority');
        $this->forge->addKey('type');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['is_published', 'expires_at'], false, false, 'published_expires');

        $this->forge->createTable('announcements');
    }

    public function down()
    {
        $this->forge->dropTable('announcements');
    }
}

