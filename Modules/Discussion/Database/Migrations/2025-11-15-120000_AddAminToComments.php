<?php

namespace Modules\Discussion\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAminToComments extends Migration
{
    public function up()
    {
        // Check if amins_count column exists
        $fields = $this->db->getFieldData('comments');
        $columnExists = false;
        foreach ($fields as $field) {
            if ($field->name === 'amins_count') {
                $columnExists = true;
                break;
            }
        }
        
        if (!$columnExists) {
            $this->forge->addColumn('comments', [
                'amins_count' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'after' => 'likes_count',
                    'comment' => 'Jumlah amin',
                ],
            ]);
        }

        // Create comment_amins table untuk tracking amins
        if (!$this->db->tableExists('comment_amins')) {
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
                    'comment' => 'User yang amin (NULL jika guest)',
                ],
                'guest_ip' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                    'comment' => 'IP guest yang amin',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['comment_id', 'user_id'], false, true); // Unique constraint
            $this->forge->addKey(['comment_id', 'guest_ip'], false, true); // Unique constraint untuk guest

            $this->forge->createTable('comment_amins');
        }
    }

    public function down()
    {
        // Drop comment_amins table
        if ($this->db->tableExists('comment_amins')) {
            $this->forge->dropTable('comment_amins');
        }
        
        // Drop amins_count column
        $fields = $this->db->getFieldData('comments');
        $columnExists = false;
        foreach ($fields as $field) {
            if ($field->name === 'amins_count') {
                $columnExists = true;
                break;
            }
        }
        
        if ($columnExists) {
            $this->forge->dropColumn('comments', 'amins_count');
        }
    }
}

