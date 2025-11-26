<?php
// Hitung data real dari campaign dan updates
$totalTerkumpul = (float) ($campaign['current_amount'] ?? 0);
$totalDonationsCount = (int) ($donation_stats['total_donations'] ?? 0);

// Hitung total urunan terpakai dari updates
$totalTerpakai = 0;
$updatesCount = 0;
if (!empty($updates) && is_array($updates)) {
    foreach ($updates as $update) {
        if (!empty($update['amount_used']) && $update['amount_used'] !== null && $update['amount_used'] !== '') {
            $totalTerpakai += (float) $update['amount_used'];
        }
        $updatesCount++;
    }
}

// Sisa urunan
$sisaUrunan = $totalTerkumpul - $totalTerpakai;
$balancePercentage = $totalTerkumpul > 0 ? ($totalTerpakai / $totalTerkumpul) * 100 : 0;

$reportLink = $report_path ?? (!empty($campaign['slug']) ? '/campaign/' . $campaign['slug'] . '/report' : null);
?>
<section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Ringkasan Penggunaan</h2>
        </div>
        <?php if ($reportLink): ?>
            <a href="<?= esc($reportLink) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#055b16] text-white text-sm font-semibold hover:bg-[#044512] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Lihat Laporan Lengkap
            </a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
            <p class="text-xs text-gray-500 uppercase">Urunan Terkumpul</p>
            <p class="text-2xl font-semibold text-[#055b16] mt-1">Rp <?= number_format($totalTerkumpul, 0, ',', '.') ?></p>
            <p class="text-xs text-gray-500 mt-2"><?= $totalDonationsCount ?> transaksi</p>
        </div>
        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
            <p class="text-xs text-gray-500 uppercase">Urunan Terpakai</p>
            <p class="text-2xl font-semibold text-[#b45309] mt-1">Rp <?= number_format($totalTerpakai, 0, ',', '.') ?></p>
            <p class="text-xs text-gray-500 mt-2"><?= $updatesCount ?> laporan penggunaan</p>
        </div>
        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
            <p class="text-xs text-gray-500 uppercase">Sisa Urunan</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">Rp <?= number_format($sisaUrunan, 0, ',', '.') ?></p>
            <p class="text-xs text-gray-500 mt-2">≈ <?= number_format(100 - $balancePercentage, 1) ?>% dana siap disalurkan</p>
        </div>
    </div>
</section>

