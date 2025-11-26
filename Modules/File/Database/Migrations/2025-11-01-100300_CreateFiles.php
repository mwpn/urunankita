<?php

namespace Modules\File\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFiles extends Migration
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
            'original_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Original filename from user',
            ],
            'filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Generated unique filename',
            ],
            'path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'comment' => 'Relative path from uploads directory',
            ],
            'full_path' => [
                'type' => 'VARCHAR',
                'constraint' => 1000,
                'comment' => 'Full absolute path',
            ],
            'size' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'comment' => 'File size in bytes',
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'extension' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Tenant owner',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User who uploaded',
            ],
            'folder' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Folder/category',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'general',
                'comment' => 'File type: image, document, etc',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('filename');
        $this->forge->addKey(['tenant_id', 'filename'], false, false, 'tenant_filename');
        $this->forge->addKey('created_at');
        $this->forge->createTable('files');
    }

    public function down()
    {
        $this->forge->dropTable('files');
    }
}

