<?php if (!empty($youtubeVideos)): ?>
    <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Video Laporan</h2>
            <span class="text-sm text-gray-500"><?= count($youtubeVideos) ?> video</span>
        </div>
        <div class="space-y-6">
            <?php foreach ($youtubeVideos as $video): ?>
                <div class="flex flex-col md:flex-row gap-4 p-4 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors">
                    <!-- Video di kiri -->
                    <div class="w-full md:w-1/2 lg:w-2/5 flex-shrink-0">
                        <div class="aspect-video rounded-xl overflow-hidden bg-black">
                            <iframe
                                src="https://www.youtube.com/embed/<?= esc($video['video_id']) ?>"
                                title="<?= esc($video['title'] ?? 'Video laporan urunan') ?>"
                                class="h-full w-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                    
                    <!-- Judul dan konten di kanan -->
                    <div class="flex-1 flex flex-col justify-start">
                        <?php if (!empty($video['title'])): ?>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <?= esc($video['title']) ?>
                            </h3>
                        <?php endif; ?>
                        
                        <?php if (!empty($video['content'])): ?>
                            <div class="text-sm text-gray-600 leading-relaxed">
                                <?= nl2br(esc($video['content'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 italic">Tidak ada deskripsi</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

