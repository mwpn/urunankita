<?php

namespace Modules\Notification\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationLogs extends Migration
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
            'recipient' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'Phone number or email',
            ],
            'channel' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'whatsapp, email, sms',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
                'comment' => 'sent, failed, pending',
            ],
            'response' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON response from API',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Type of notification: order, payment, reminder, etc',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('channel');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('notification_logs');
    }

    public function down()
    {
        $this->forge->dropTable('notification_logs');
    }
}

