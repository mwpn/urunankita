<?php if (!empty($allImages)): ?>
    <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Galeri Foto</h2>
            <span class="text-sm text-gray-500"><?= count($allImages) ?> media</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($allImages as $index => $imageUrl): ?>
                <button type="button" onclick="openGalleryModal(<?= $index ?>)"
                    class="group block overflow-hidden rounded-xl border border-gray-100 bg-gray-50 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#055b16] focus:ring-offset-2">
                    <img src="<?= esc($imageUrl) ?>" alt="Dokumentasi urunan"
                        class="h-40 w-full object-cover transition duration-300 group-hover:scale-105"
                        onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center'); this.parentElement.innerHTML='<span class=\'text-sm text-gray-400\'>Gambar tidak tersedia</span>';">
                </button>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

