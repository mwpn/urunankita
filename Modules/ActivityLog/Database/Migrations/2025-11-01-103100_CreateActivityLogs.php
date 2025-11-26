<?php

namespace Modules\ActivityLog\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Tenant ID for isolation',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User who performed the action',
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'Action type: create, update, delete, login, logout, view, etc',
            ],
            'entity' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Entity/Model name: User, Product, Order, etc',
            ],
            'entity_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Entity ID',
            ],
            'old_value' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: Old value before change',
            ],
            'new_value' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: New value after change',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Human readable description',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP address of user',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'User agent string',
            ],
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: Additional metadata',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('entity');
        $this->forge->addKey(['entity', 'entity_id'], false, false, 'entity_lookup');
        $this->forge->addKey('created_at');
        
        // Index untuk query performance
        $this->forge->addKey(['tenant_id', 'created_at'], false, false, 'tenant_date');
        $this->forge->addKey(['tenant_id', 'action'], false, false, 'tenant_action');

        $this->forge->createTable('activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}

