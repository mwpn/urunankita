<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'phone',
                'comment' => 'Path foto profil user',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'avatar');
    }
}
