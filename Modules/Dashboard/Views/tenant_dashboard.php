<?= $this->extend('Modules\Core\Views\dashboard_layout') ?>

<?= $this->section('head') ?>
<title>Dashboard - <?= esc($stats['tenant']['name'] ?? 'Tenant') ?></title>
<?= $this->endSection() ?>

<?= $this->section('sidebar_nav') ?>
<?php
$current_page = uri_string();
$nav_items = [
    ['title' => 'Dashboard', 'url' => "/tenant/dashboard", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>'],
    ['title' => 'Urunan', 'url' => "/tenant/campaigns", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>'],
    ['title' => 'Donasi', 'url' => "/tenant/donations", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>'],
    ['title' => 'Laporan', 'url' => "/tenant/reports", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
    ['title' => 'Pengaturan', 'url' => "/tenant/settings", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'],
];
?>
<?php foreach ($nav_items as $item): ?>
    <a class="<?= strpos($current_page, $item['url']) !== false ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100' ?> flex items-center gap-x-3 px-3 py-2 text-sm font-medium rounded-lg" href="<?= esc($item['url']) ?>">
        <?= $item['icon'] ?>
        <?= esc($item['title']) ?>
    </a>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('topbar_actions') ?>
<!-- Topbar actions bisa ditambahkan di sini -->
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 mb-1">Dashboard - <?= esc($stats['tenant']['name'] ?? 'Tenant') ?></h1>
    <p class="text-sm text-gray-600">Ringkasan aktivitas dan statistik urunan Anda</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Urunan Aktif</div>
        <div class="text-3xl font-semibold text-gray-900 mb-1"><?= number_format($stats['active_campaigns'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Campaign aktif</div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Donasi</div>
        <div class="text-3xl font-semibold text-gray-900 mb-1">Rp <?= number_format(($stats['total_donations'] ?? 0) / 1000000, 1) ?>M</div>
        <div class="text-xs text-gray-500">Rp <?= number_format($stats['total_donations'] ?? 0, 0, ',', '.') ?></div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Donatur</div>
        <div class="text-3xl font-semibold text-gray-900 mb-1"><?= number_format($stats['total_donors'] ?? 0) ?></div>
        <div class="text-xs text-gray-500">Orang Baik</div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Saldo Tersedia</div>
        <div class="text-3xl font-semibold text-gray-900 mb-1">Rp <?= number_format(($stats['balance'] ?? 0) / 1000000, 1) ?>M</div>
        <div class="text-xs text-gray-500">Rp <?= number_format($stats['balance'] ?? 0, 0, ',', '.') ?></div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Donasi 7 Hari</div>
        <div class="text-3xl font-semibold text-gray-900 mb-1">Rp <?= number_format(($stats['recent_donations'] ?? 0) / 1000000, 1) ?>M</div>
        <div class="text-xs text-gray-500">Terakhir seminggu</div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Penyaluran</div>
        <div class="text-3xl font-semibold text-gray-900 mb-1">Rp <?= number_format(($stats['total_withdrawals'] ?? 0) / 1000000, 1) ?>M</div>
        <div class="text-xs text-gray-500">Dana disalurkan</div>
    </div>
</div>

<!-- Recent Data Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php if (!empty($stats['recent_campaigns'])): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Urunan Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Terkumpul</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($stats['recent_campaigns'] as $campaign): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium"><?= esc(substr($campaign['title'], 0, 40)) ?><?= strlen($campaign['title']) > 40 ? '...' : '' ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium <?= $campaign['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= esc($campaign['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['recent_donations_list'])): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Donasi Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Donatur</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($stats['recent_donations_list'] as $donation): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= esc($donation['is_anonymous'] ? 'Orang Baik Tanpa Nama' : ($donation['donor_name'] ?? 'Anonymous')) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp <?= number_format($donation['amount'] ?? 0, 0, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium <?= $donation['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= esc($donation['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-500">Belum ada donasi</p>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Sidebar toggle untuk mobile
    document.addEventListener('DOMContentLoaded', function() {
        // Preline akan handle sidebar toggle otomatis
    });
</script>
<?= $this->endSection() ?>
