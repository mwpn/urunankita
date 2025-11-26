<?php

namespace Modules\Helpdesk\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTickets extends Migration
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
            'ticket_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'comment' => 'Ticket number format: TKT-YYYYMMDD-XXXXX',
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Tenant yang membuat ticket',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User yang membuat ticket',
            ],
            'category_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Kategori ticket',
            ],
            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Judul ticket',
            ],
            'description' => [
                'type' => 'TEXT',
                'comment' => 'Deskripsi masalah/pertanyaan',
            ],
            'priority' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'medium',
                'comment' => 'low, medium, high, urgent',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'open',
                'comment' => 'open, in_progress, resolved, closed',
            ],
            'assigned_to' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Admin yang ditugaskan',
            ],
            'attachments' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array: File attachments',
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'resolved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'closed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'closed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'last_replied_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Last reply timestamp',
            ],
            'last_replied_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        // ticket_number already has unique key from field definition, no need to add again
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('status');
        $this->forge->addKey('priority');
        $this->forge->addKey('assigned_to');
        $this->forge->addKey('created_at');
        
        // Index untuk query performance
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'tenant_status');
        $this->forge->addKey(['status', 'priority'], false, false, 'status_priority');

        $this->forge->createTable('tickets');
    }

    public function down()
    {
        $this->forge->dropTable('tickets');
    }
}

