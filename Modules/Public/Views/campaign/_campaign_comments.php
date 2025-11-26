<!-- Section Komentar & Diskusi -->
<section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-6" style="display: block !important; width: 100% !important; max-width: 100% !important; column-count: 1 !important; columns: 1 !important;">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Diskusi & Komentar</h2>
        </div>
        <span id="commentsCount" class="text-xs sm:text-sm text-gray-500">
            0 komentar
        </span>
    </div>

    <!-- Form Komentar -->
    <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gray-50 rounded-xl border border-gray-200">
        <form id="commentForm" class="space-y-3 sm:space-y-4 p-0">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="campaign_id" value="<?= (int) ($campaign['id'] ?? 0) ?>">
            <input type="hidden" name="parent_id" id="reply_to_id" value="">

            <div id="replyIndicator" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-700">
                        <span id="replyToName"></span>
                    </span>
                    <button type="button" id="cancelReply" class="text-sm text-blue-600 hover:text-blue-800">Batal</button>
                </div>
            </div>

            <?php
            $user = auth_user();
            $isLoggedIn = !empty($user);
            ?>

            <?php if (!$isLoggedIn): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="commenter_name" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                        <input type="text" id="commenter_name" name="commenter_name" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-[#055b16] focus:border-[#055b16]"
                            placeholder="Masukkan nama Anda">
                    </div>
                    <div>
                        <label for="commenter_email" class="block text-sm font-medium text-gray-700 mb-1">Email (opsional)</label>
                        <input type="email" id="commenter_email" name="commenter_email"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-[#055b16] focus:border-[#055b16]"
                            placeholder="email@example.com">
                    </div>
                </div>
            <?php else: ?>
                <div class="text-sm text-gray-600 pb-2">
                    Berkomentar sebagai: <span class="font-semibold text-gray-900"><?= esc($user['name'] ?? 'User') ?></span>
                </div>
            <?php endif; ?>

            <div>
                <label for="comment_content" class="block text-sm font-medium text-gray-700 mb-1">Komentar *</label>
                <textarea id="comment_content" name="content" rows="4" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-[#055b16] focus:border-[#055b16]"
                    placeholder="Tulis komentar atau pertanyaan Anda di sini..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 sm:gap-3">
                <button type="submit" id="submitCommentBtn"
                    class="w-full sm:w-auto px-4 sm:px-5 py-2 rounded-lg bg-[#055b16] text-white text-sm sm:text-base font-semibold hover:bg-[#044512] transition-colors">
                    <span id="submitCommentText">Kirim Komentar</span>
                    <span id="submitCommentLoading" class="hidden">Mengirim...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Komentar -->
    <div id="commentsList" class="space-y-4">
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p>Memuat komentar...</p>
        </div>
    </div>
</section>