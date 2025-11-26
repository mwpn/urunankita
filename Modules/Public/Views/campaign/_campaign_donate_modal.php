<!-- Donation Modal -->
<div id="donateModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-6 w-full max-w-lg relative mx-4 shadow-2xl transform transition-all duration-300 scale-95" id="donateModalContent">
        <button id="btnCloseModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1.5 transition-colors duration-200" aria-label="Tutup modal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Success Message (hidden by default) -->
        <div id="donateSuccessModal" class="hidden">
            <div class="text-center py-4">
                <div class="mb-6 animate-bounce">
                    <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-900">Donasi Berhasil!</h3>
                <p id="donateSuccessModalMsg" class="text-sm text-gray-600 mb-8 leading-relaxed px-2">Donasi berhasil dibuat. silakan melakukan transfer sesuai instruksi pada pesan Whatsapp yang terkirim</p>
                <button type="button" id="btnCloseSuccessModal" class="w-full sm:w-auto px-6 py-3 rounded-xl text-white bg-primary-600 hover:bg-primary-700 font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">Tutup</button>
            </div>
        </div>

        <!-- Donation Form (shown by default) -->
        <div id="donateFormContainer">
            <div class="mb-5 pb-3 border-b border-gray-200">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Ikut Urunan</h3>
                </div>
                <p class="text-sm text-gray-600 ml-12"><?= esc($campaign['title']) ?></p>
            </div>
            <form id="donateForm" onsubmit="submitDonation(event)">
            <input type="hidden" id="donate_campaign_id" name="campaign_id" value="<?= (int) ($campaign['id'] ?? 0) ?>">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Donasi (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium text-sm">Rp</span>
                        </div>
                        <input type="number" min="1000" step="1000" name="amount" required class="block w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="50000">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="donor_name" required class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                        <input type="text" name="donor_phone" required class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="081234567890">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="donor_email" required class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="email@contoh.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pesan (opsional)</label>
                    <textarea name="message" rows="3" class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 resize-none text-sm" placeholder="Tulis dukungan Anda..."></textarea>
                </div>

                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                    <input id="is_anonymous" type="checkbox" name="is_anonymous" value="true" class="mt-1 h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <div>
                        <label for="is_anonymous" class="text-sm font-medium text-gray-700 cursor-pointer">Sembunyikan nama saya dari publik (Anonim)</label>
                        <p class="text-xs text-gray-500 mt-1">Catatan: Data Anda tetap diperlukan untuk keperluan administrasi, namun tidak akan ditampilkan di halaman publik.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <div class="space-y-2" id="paymentMethodsContainer">
                        <?php if (!empty($active_payment_methods)): ?>
                            <?php foreach ($active_payment_methods as $idx => $method): ?>
                                <label class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl hover:border-primary-400 hover:bg-primary-50 cursor-pointer transition-all duration-200 <?= $idx === 0 ? 'border-primary-400 bg-primary-50' : '' ?>">
                                    <input type="radio" name="payment_method" value="<?= esc($method['code'] ?? 'bank_transfer') ?>"
                                        data-type="<?= esc($method['type'] ?? 'bank-transfer') ?>"
                                        class="text-primary-600 payment-method-radio w-5 h-5"
                                        <?= $idx === 0 ? 'checked' : '' ?>>
                                    <div class="flex-1">
                                        <span class="text-sm font-semibold text-gray-800 block"><?= esc($method['name'] ?? 'Transfer Bank') ?></span>
                                        <?php if (!empty($method['description'])): ?>
                                            <p class="text-xs text-gray-600 mt-1"><?= esc($method['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <label class="flex items-center gap-3 p-2.5 border border-primary-400 bg-primary-50 rounded-xl">
                                <input type="radio" name="payment_method" value="bank_transfer" class="text-primary-600 payment-method-radio w-5 h-5" checked>
                                <span class="text-sm font-semibold text-gray-800">Transfer Bank</span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="bankAccountContainer" class="<?= (!empty($active_payment_methods) && ($active_payment_methods[0]['type'] ?? '') !== 'bank-transfer') ? 'hidden' : '' ?>">
                    <?php if (!empty($tenant['bank_accounts'])): ?>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih Rekening Tujuan</label>
                            <select name="bank_account_id" class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm">
                                <option value="">-- Pilih Rekening --</option>
                                <?php foreach (($tenant['bank_accounts'] ?? []) as $idx => $acc): ?>
                                    <option value="<?= esc($acc['id'] ?? $idx) ?>"><?= esc(($acc['bank'] ?? '-') . ' - ' . ($acc['account_number'] ?? '-') . ' a.n ' . ($acc['account_name'] ?? '-')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-5 border-t border-gray-200">
                <button type="button" id="btnCancelModal" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400 font-medium transition-all duration-200 text-center">Batal</button>
                <button id="donateSubmit" type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-white bg-primary-600 hover:bg-primary-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Kirim Donasi
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

