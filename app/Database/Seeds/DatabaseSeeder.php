<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('SuperAdminSeeder');
        $this->call('PlanSeeder');
        
        // Seed tenant sample data jika diminta
        // php spark db:seed TenantSampleDataSeeder
        // atau untuk tenant tertentu:
        // php spark db:seed TenantSampleDataSeeder --tenant=jerry
    }
}


