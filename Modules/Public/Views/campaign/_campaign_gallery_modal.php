<!-- Gallery Modal -->
<?php if (!empty($allImages)): ?>
    <div id="galleryModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center">
        <button id="galleryCloseBtn" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 p-2" aria-label="Tutup">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <button id="galleryPrevBtn" class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 z-10 p-2 bg-black/50 rounded-full" aria-label="Sebelumnya">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <button id="galleryNextBtn" class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 z-10 p-2 bg-black/50 rounded-full" aria-label="Selanjutnya">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

        <div class="relative w-full h-full flex items-center justify-center px-20 py-16">
            <img id="galleryImage" src="" alt="Gallery" class="max-w-4xl max-h-[85vh] w-auto h-auto object-contain rounded-lg shadow-2xl">
        </div>

        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm">
            <span id="galleryCounter">1 / <?= count($allImages) ?></span>
        </div>
    </div>
<?php endif; ?>

