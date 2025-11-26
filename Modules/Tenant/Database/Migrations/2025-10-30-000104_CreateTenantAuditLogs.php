<?php

namespace Modules\Tenant\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [ 'type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true ],
            'user_id' => [ 'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true ],
            'action' => [ 'type' => 'VARCHAR', 'constraint' => 100 ],
            'meta' => [ 'type' => 'TEXT', 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}


