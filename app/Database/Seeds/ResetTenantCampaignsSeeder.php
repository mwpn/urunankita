<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResetTenantCampaignsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Ambil tenant aktif pertama sebagai target
        $tenant = $db->table('tenants')
            ->where('status', 'active')
            ->orderBy('id', 'ASC')
            ->get(1)
            ->getRowArray();

        if (!$tenant) {
            // Jika tidak ada yang active, ambil tenant pertama yang ada
            $tenant = $db->table('tenants')->orderBy('id', 'ASC')->get(1)->getRowArray();
        }

        if (!$tenant) {
            echo "Tidak ditemukan tenant. Seeder dibatalkan." . PHP_EOL;
            return;
        }

        $tenantId = (int) $tenant['id'];

        // Cari semua campaign id milik tenant ini
        $campaignIds = $db->table('campaigns')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->get()
            ->getResultArray();

        $campaignIdList = array_map(fn($r) => (int) $r['id'], $campaignIds);

        // Hapus donasi milik tenant ini
        $db->table('donations')->where('tenant_id', $tenantId)->delete();

        // Hapus laporan (campaign_updates) untuk campaign di tenant ini
        if (!empty($campaignIdList)) {
            $db->table('campaign_updates')->whereIn('campaign_id', $campaignIdList)->delete();
        }

        // Hapus campaign milik tenant ini
        $db->table('campaigns')->where('tenant_id', $tenantId)->delete();

        // Insert 10 campaign baru dengan status pending_verification
        $now = date('Y-m-d H:i:s');
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $title = "Urunan Perlu Konfirmasi #" . $i;
            $slug = 'urunan-perlu-konfirmasi-' . $i . '-' . substr(md5($now.$i), 0, 6);
            $rows[] = [
                'tenant_id' => $tenantId,
                'title' => $title,
                'slug' => $slug,
                'target_amount' => 10000000,
                'current_amount' => 0,
                'donors_count' => 0,
                'status' => 'pending_verification',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            $db->table('campaigns')->insertBatch($rows);
        }

        // Ambil 10 campaign terbaru untuk seed donasi pending
        $newCampaigns = $db->table('campaigns')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Insert 10 donasi pending (masing-masing ke campaign berbeda)
        $donationRows = [];
        $i = 1;
        foreach ($newCampaigns as $c) {
            $donationRows[] = [
                'campaign_id' => (int) $c['id'],
                'tenant_id' => $tenantId,
                'donor_id' => null,
                'donor_name' => 'Donatur ' . $i,
                'donor_email' => null,
                'donor_phone' => null,
                'amount' => 50000 * $i,
                'is_anonymous' => ($i % 3 === 0) ? 1 : 0,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'pending',
                'payment_proof' => null,
                'message' => 'Donasi uji coba #' . $i,
                'bank_account_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $i++;
        }

        if (!empty($donationRows)) {
            $db->table('donations')->insertBatch($donationRows);
        }

        echo "Seeder selesai. Tenant ID: {$tenantId}. Dihapus campaign, laporan, donasi lama; tambah 10 urunan pending & 10 donasi pending." . PHP_EOL;
    }
}


