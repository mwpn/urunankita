<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('content') ?>
<?php
$reportSummary = $report_summary ?? ($financial_report['summary'] ?? []);
$donations = $financial_report['donations'] ?? [];
$usageEntries = $financial_report['updates'] ?? [];

$datePool = [];
foreach ($donations as $donation) {
    if (!empty($donation['paid_at'])) {
        $datePool[] = $donation['paid_at'];
    }
}
foreach ($usageEntries as $usage) {
    if (!empty($usage['created_at'])) {
        $datePool[] = $usage['created_at'];
    }
}
if (!empty($campaign['created_at'])) {
    $datePool[] = $campaign['created_at'];
}
if (!empty($report_last_updated)) {
    $datePool[] = $report_last_updated;
}
$periodStart = !empty($datePool) ? min($datePool) : null;
$periodEnd = !empty($datePool) ? max($datePool) : null;

$detailPath = $detail_path ?? (!empty($campaign['slug']) ? '/campaign/' . $campaign['slug'] : '/campaigns');
?>
<section class="max-w-6xl mx-auto px-4 py-10 space-y-8">
    <div class="flex flex-col gap-4 border-b border-gray-200 pb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-500">Laporan Transparansi</p>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2"><?= esc($campaign['title'] ?? 'Urunan') ?></h1>
                <p class="text-sm text-gray-500 mt-2">
                    Penggalang: <?= esc($tenant['name'] ?? 'UrunanKita') ?>
                    <?php if (!empty($campaign['created_at'])): ?>
                        · Dibuat <?= date('d M Y', strtotime($campaign['created_at'])) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="text-sm text-gray-500 text-right">
                <p>Terakhir diperbarui</p>
                <p class="font-semibold text-gray-900"><?= date('d M Y, H:i', strtotime($report_last_updated ?? 'now')) ?></p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="text-sm text-gray-600">
                <p>Periode data</p>
                <p class="font-semibold text-gray-900">
                    <?= $periodStart ? date('d M Y', strtotime($periodStart)) : '-' ?>
                    —
                    <?= $periodEnd ? date('d M Y', strtotime($periodEnd)) : '-' ?>
                </p>
            </div>
            <a href="<?= esc($detailPath) ?>" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Halaman Kampanye
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase text-gray-500">Total Urunan Masuk</p>
            <p class="text-3xl font-bold text-[#055b16] mt-2">Rp <?= number_format((float) ($reportSummary['total_donations'] ?? 0), 0, ',', '.') ?></p>
            <p class="text-sm text-gray-500 mt-2"><?= (int) ($reportSummary['donations_count'] ?? 0) ?> transaksi</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase text-gray-500">Urunan Terpakai</p>
            <p class="text-3xl font-bold text-[#b45309] mt-2">Rp <?= number_format((float) ($reportSummary['total_amount_used'] ?? 0), 0, ',', '.') ?></p>
            <p class="text-sm text-gray-500 mt-2"><?= (int) ($reportSummary['updates_count'] ?? 0) ?> catatan penyaluran</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase text-gray-500">Saldo Tersisa</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">Rp <?= number_format((float) ($reportSummary['balance'] ?? 0), 0, ',', '.') ?></p>
            <p class="text-sm text-gray-500 mt-2">≈ <?= number_format((float) ($reportSummary['balance_percentage'] ?? 0), 1) ?>% dari total dana</p>
        </div>
    </div>

    <?php
    // Gabungkan semua transaksi (masuk & keluar) dalam satu array
    $allTransactions = [];

    // Tambahkan donasi sebagai transaksi masuk
    foreach ($donations as $donation) {
        $donorName = $donation['donor_name'] ?? 'Orang Baik';
        // Hapus "Tanpa Nama" jika ada
        $donorName = str_replace('Tanpa Nama', '', $donorName);
        $donorName = trim($donorName);
        if (empty($donorName)) {
            $donorName = 'Orang Baik';
        }

        $allTransactions[] = [
            'date' => $donation['paid_at'] ?? $donation['created_at'] ?? null,
            'type' => 'masuk',
            'description' => 'Donasi dari ' . $donorName,
            'detail' => !empty($donation['message']) ? $donation['message'] : null,
            'amount' => (float) ($donation['amount'] ?? 0),
            'payment_method' => $donation['payment_method'] ?? null,
            'donor_email' => $donation['donor_email'] ?? null,
        ];
    }

    // Tambahkan penggunaan dana sebagai transaksi keluar
    foreach ($usageEntries as $usage) {
        $allTransactions[] = [
            'date' => $usage['created_at'] ?? null,
            'type' => 'keluar',
            'description' => $usage['title'] ?? 'Penggunaan Dana',
            'detail' => !empty($usage['content']) ? strip_tags($usage['content']) : null,
            'amount' => (float) ($usage['amount_used'] ?? 0),
        ];
    }

    // Sort berdasarkan tanggal (terlama dulu untuk menghitung saldo)
    usort($allTransactions, function ($a, $b) {
        $dateA = $a['date'] ?? '';
        $dateB = $b['date'] ?? '';
        return strtotime($dateA) - strtotime($dateB);
    });

    // Hitung running balance (saldo) dari awal ke akhir
    $runningBalance = 0;
    foreach ($allTransactions as &$trans) {
        if ($trans['type'] === 'masuk') {
            $runningBalance += $trans['amount'];
        } else {
            $runningBalance -= $trans['amount'];
        }
        $trans['balance'] = $runningBalance;
    }
    unset($trans);

    // Reverse untuk tampilkan dari terbaru ke terlama
    $allTransactions = array_reverse($allTransactions);

    // Paginasi
    $perPage = 20;
    $currentPage = (int) ($_GET['page'] ?? 1);
    $totalTransactions = count($allTransactions);
    $totalPages = max(1, ceil($totalTransactions / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    $paginatedTransactions = array_slice($allTransactions, $offset, $perPage);

    // Build pagination URL
    $currentUrl = $_SERVER['REQUEST_URI'];
    $urlParts = parse_url($currentUrl);
    $queryParams = [];
    if (!empty($urlParts['query'])) {
        parse_str($urlParts['query'], $queryParams);
    }
    ?>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Mutasi Transaksi</h2>
                <p class="text-sm text-gray-500">Semua transaksi urunan masuk dan penggunaan dana</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                <?= number_format($totalTransactions, 0, ',', '.') ?> transaksi
            </span>
        </div>
        <?php if (!empty($allTransactions)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold">Keterangan</th>
                            <th class="px-4 py-3 text-right font-semibold">Masuk</th>
                            <th class="px-4 py-3 text-right font-semibold">Keluar</th>
                            <th class="px-4 py-3 text-right font-semibold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach ($paginatedTransactions as $trans): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-[12px] text-gray-500">
                                    <?= !empty($trans['date']) ? date('d M Y, H:i', strtotime($trans['date'])) : '-' ?>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="font-medium text-gray-900 text-sm"><?= esc($trans['description']) ?></p>
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-[#055b16] text-sm">
                                    <?php if ($trans['type'] === 'masuk'): ?>
                                        Rp <?= number_format($trans['amount'], 0, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-gray-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-[#b45309] text-sm">
                                    <?php if ($trans['type'] === 'keluar'): ?>
                                        Rp <?= number_format($trans['amount'], 0, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-gray-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900 text-sm">
                                    Rp <?= number_format($trans['balance'] ?? 0, 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        Menampilkan <?= number_format($offset + 1, 0, ',', '.') ?> - <?= number_format(min($offset + $perPage, $totalTransactions), 0, ',', '.') ?> dari <?= number_format($totalTransactions, 0, ',', '.') ?> transaksi
                    </div>
                    <div class="flex items-center gap-2">
                        <?php
                        $buildPageUrl = function ($page) use ($urlParts, $queryParams) {
                            $queryParams['page'] = $page;
                            $queryString = http_build_query($queryParams);
                            return ($urlParts['path'] ?? '') . ($queryString ? '?' . $queryString : '');
                        };
                        ?>

                        <?php if ($currentPage > 1): ?>
                            <a href="<?= esc($buildPageUrl($currentPage - 1)) ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Sebelumnya
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Sebelumnya
                            </span>
                        <?php endif; ?>

                        <div class="flex items-center gap-1">
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);

                            if ($startPage > 1): ?>
                                <a href="<?= esc($buildPageUrl(1)) ?>" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="px-2 text-sm text-gray-500">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="px-3 py-2 text-sm font-semibold text-white bg-[#055b16] border border-[#055b16] rounded-lg"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= esc($buildPageUrl($i)) ?>" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="px-2 text-sm text-gray-500">...</span>
                                <?php endif; ?>
                                <a href="<?= esc($buildPageUrl($totalPages)) ?>" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?= $totalPages ?></a>
                            <?php endif; ?>
                        </div>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= esc($buildPageUrl($currentPage + 1)) ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Selanjutnya
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                Selanjutnya
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500">
                Belum ada transaksi yang tercatat.
            </div>
        <?php endif; ?>
    </div>

    <p class="text-xs text-gray-400 text-center">
        Data ini diperbarui otomatis berdasarkan donasi yang berstatus paid dan laporan penggunaan dana yang dibuat penggalang.
    </p>
</section>
<?= $this->endSection() ?>