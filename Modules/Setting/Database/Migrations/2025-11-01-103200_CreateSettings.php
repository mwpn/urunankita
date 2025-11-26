<?php

namespace Modules\Setting\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettings extends Migration
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
            'key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Setting key',
            ],
            'value' => [
                'type' => 'TEXT',
                'comment' => 'Setting value (encoded)',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'string',
                'comment' => 'Value type: string, integer, float, boolean, json',
            ],
            'scope' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Scope: global, tenant, user',
            ],
            'scope_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Scope ID (tenant_id or user_id), NULL for global',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Setting description',
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
        $this->forge->addKey('key');
        $this->forge->addKey('scope');
        $this->forge->addKey('scope_id');
        
        // Unique constraint: key + scope + scope_id
        $this->forge->addUniqueKey(['key', 'scope', 'scope_id'], 'setting_unique');
        
        // Index for tenant settings lookup
        $this->forge->addKey(['scope', 'scope_id'], false, false, 'scope_lookup');

        $this->forge->createTable('settings');
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}

