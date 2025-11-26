<!-- Donation Success/Fail Alerts (Deprecated - now using modal) -->
<!-- These alerts are kept for backward compatibility but should not be displayed -->
<style>
    #donateSuccess {
        display: none !important;
        visibility: hidden !important;
    }
</style>
<div id="donateSuccess" class="hidden max-w-4xl mx-auto" style="display: none !important;">
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
        <div class="font-medium" id="donateSuccessMsg">Donasi berhasil.</div>
    </div>
    <?php if (!empty($tenant['bank_accounts'])): ?>
        <div class="mb-8 bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Instruksi Pembayaran (Transfer Bank)</h3>
            <div class="space-y-3 text-sm text-gray-700">
                <?php foreach (($tenant['bank_accounts'] ?? []) as $acc): ?>
                    <div class="p-3 border border-gray-100 rounded-lg">
                        <div class="font-medium text-gray-900"><?= esc($acc['bank'] ?? '-') ?> - <?= esc($acc['account_name'] ?? '-') ?></div>
                        <div class="text-gray-700">No. Rekening: <span class="font-semibold"><?= esc($acc['account_number'] ?? '-') ?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-gray-500 mt-3">Setelah transfer, mohon tunggu verifikasi atau unggah bukti transfer melalui tautan konfirmasi yang akan kami kirimkan.</p>
        </div>
    <?php endif; ?>
</div>

<div id="donateError" class="hidden max-w-4xl mx-auto">
    <div class="mb-8 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
        <div class="font-medium" id="donateErrorMsg">Terjadi kesalahan.</div>
    </div>
</div>

