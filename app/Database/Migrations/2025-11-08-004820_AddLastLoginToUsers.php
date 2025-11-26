<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastLoginToUsers extends Migration
{
    public function up()
    {
        // Check if column exists first
        $fields = $this->db->getFieldData('users');
        $columnExists = false;
        foreach ($fields as $field) {
            if ($field->name === 'last_login') {
                $columnExists = true;
                break;
            }
        }
        
        if (!$columnExists) {
            $this->forge->addColumn('users', [
                'last_login' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'updated_at',
                    'comment' => 'Waktu terakhir user login',
                ],
            ]);
        }
    }

    public function down()
    {
        // Check if column exists before dropping
        $fields = $this->db->getFieldData('users');
        $columnExists = false;
        foreach ($fields as $field) {
            if ($field->name === 'last_login') {
                $columnExists = true;
                break;
            }
        }
        
        if ($columnExists) {
            $this->forge->dropColumn('users', 'last_login');
        }
    }
}

