<div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Urunan</h3>
    <?php if (isset($campaign['progress_percentage'])): ?>
        <div class="mb-4">
            <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                <span>Terkumpul</span>
                <span><?= round($campaign['progress_percentage'], 1) ?>%</span>
            </div>
            <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="text-center p-3 bg-emerald-50 rounded-lg">
            <p class="text-xl font-bold text-[#055b16]">Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></p>
            <p class="text-xs text-gray-600">Terkumpul</p>
        </div>
        <div class="text-center p-3 bg-emerald-50 rounded-lg">
            <p class="text-base font-semibold text-gray-900">Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?></p>
            <p class="text-xs text-gray-600">Target</p>
        </div>
    </div>
    <button type="button" onclick="openDonateModal()" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#055b16] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#044512]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Ikut Urunan
    </button>
</div>

