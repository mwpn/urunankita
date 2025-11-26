<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [ 'name' => 'Free', 'price' => 0, 'description' => 'Paket gratis', 'features' => json_encode(['basic']) ],
            [ 'name' => 'Pro', 'price' => 199000, 'description' => 'Paket profesional', 'features' => json_encode(['pro']) ],
            [ 'name' => 'Enterprise', 'price' => 999000, 'description' => 'Paket enterprise', 'features' => json_encode(['enterprise']) ],
        ];

        foreach ($plans as $plan) {
            $exists = $this->db->table('plans')->where('name', $plan['name'])->get()->getRowArray();
            if ($exists) {
                $this->db->table('plans')->where('name', $plan['name'])->update([
                    'price' => $plan['price'],
                    'description' => $plan['description'],
                    'features' => $plan['features'],
                ]);
            } else {
                $this->db->table('plans')->insert($plan);
            }
        }
    }
}


