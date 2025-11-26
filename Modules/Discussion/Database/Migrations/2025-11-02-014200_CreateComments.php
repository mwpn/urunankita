<?php

namespace Modules\Discussion\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComments extends Migration
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
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID Urunan',
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Tenant dari campaign',
            ],
            'parent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Parent comment untuk reply (nested comments)',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID user jika terdaftar, NULL untuk guest',
            ],
            'commenter_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Nama commenter (guest atau user)',
            ],
            'commenter_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Email commenter (untuk guest)',
            ],
            'content' => [
                'type' => 'TEXT',
                'comment' => 'Isi komentar/diskusi',
            ],
            'is_guest' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Komentar dari guest (tidak terdaftar)',
            ],
            'is_pinned' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Pin komentar penting',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'approved',
                'comment' => 'approved, pending, rejected',
            ],
            'likes_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Jumlah like',
            ],
            'replies_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Jumlah reply',
            ],
            'reported_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Jumlah report (untuk moderation)',
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
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('parent_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        // Index untuk query performance
        $this->forge->addKey(['campaign_id', 'status'], false, false, 'campaign_status');
        $this->forge->addKey(['campaign_id', 'parent_id'], false, false, 'campaign_parent');

        $this->forge->createTable('comments');

        // Create comment_likes table untuk tracking likes
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'comment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User yang like (NULL jika guest)',
            ],
            'guest_ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP guest yang like',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['comment_id', 'user_id'], false, true); // Unique constraint
        $this->forge->addKey(['comment_id', 'guest_ip'], false, true); // Unique constraint untuk guest

        $this->forge->createTable('comment_likes');
    }

    public function down()
    {
        $this->forge->dropTable('comment_likes');
        $this->forge->dropTable('comments');
    }
}

