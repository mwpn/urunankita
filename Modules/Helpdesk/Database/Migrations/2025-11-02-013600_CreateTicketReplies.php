<?php

namespace Modules\Helpdesk\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketReplies extends Migration
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
            'ticket_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID Ticket',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'User yang reply (tenant atau admin)',
            ],
            'user_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'tenant, admin',
            ],
            'message' => [
                'type' => 'TEXT',
                'comment' => 'Isi reply',
            ],
            'attachments' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array: File attachments',
            ],
            'is_internal' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Internal note (hanya visible untuk admin)',
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
        $this->forge->addKey('ticket_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['ticket_id', 'created_at'], false, false, 'ticket_date');

        $this->forge->createTable('ticket_replies');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_replies');
    }
}

