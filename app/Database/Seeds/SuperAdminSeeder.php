<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $email = 'admin@root.test';
        $exists = $this->db->table('users')->where('email', $email)->get()->getRowArray();
        $data = [
            'name' => 'Super Admin',
            'email' => $email,
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'superadmin',
            'status' => 'active',
        ];
        if ($exists) {
            // Update minimal agar konsisten
            $this->db->table('users')->where('email', $email)->update([
                'name' => $data['name'],
                'role' => $data['role'],
                'status' => $data['status'],
            ]);
        } else {
            $this->db->table('users')->insert($data);
        }
    }
}


