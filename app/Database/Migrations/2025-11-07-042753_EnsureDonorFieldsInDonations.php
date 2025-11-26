<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureDonorFieldsInDonations extends Migration
{
    public function up()
    {
        // Check if columns exist, if not add them
        $fields = $this->db->getFieldData('donations');
        $fieldNames = array_column($fields, 'name');
        
        // Add donor_name if it doesn't exist
        if (!in_array('donor_name', $fieldNames)) {
            $this->forge->addColumn('donations', [
                'donor_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'donor_id',
                    'comment' => 'Nama donatur (untuk donasi non-user)',
                ],
            ]);
        } else {
            // Modify existing column to ensure it has correct structure
            $this->forge->modifyColumn('donations', [
                'donor_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'comment' => 'Nama donatur (untuk donasi non-user)',
                ],
            ]);
        }
        
        // Add donor_phone if it doesn't exist
        if (!in_array('donor_phone', $fieldNames)) {
            $this->forge->addColumn('donations', [
                'donor_phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'donor_email',
                    'comment' => 'Nomor telepon donatur (untuk donasi non-user)',
                ],
            ]);
        } else {
            // Modify existing column to ensure it has correct structure
            $this->forge->modifyColumn('donations', [
                'donor_phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'comment' => 'Nomor telepon donatur (untuk donasi non-user)',
                ],
            ]);
        }
        
        // Ensure donor_email exists and has correct structure
        if (!in_array('donor_email', $fieldNames)) {
            $this->forge->addColumn('donations', [
                'donor_email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'donor_name',
                    'comment' => 'Email donatur (untuk donasi non-user)',
                ],
            ]);
        } else {
            // Modify existing column to ensure it has correct structure
            $this->forge->modifyColumn('donations', [
                'donor_email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'comment' => 'Email donatur (untuk donasi non-user)',
                ],
            ]);
        }
    }

    public function down()
    {
        // Optionally remove columns if needed (commented out for safety)
        // $this->forge->dropColumn('donations', ['donor_name', 'donor_phone', 'donor_email']);
    }
}
