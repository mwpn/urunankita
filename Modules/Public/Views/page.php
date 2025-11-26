<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title><?= esc($page['title'] ?? 'Halaman') ?> — UrunanKita.id</title>
<meta name="description" content="<?= esc($page['description'] ?? '') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Hero -->
<section class="bg-gradient-to-b from-white to-slate-100">
    <div class="max-w-6xl mx-auto px-4 py-12 text-center">
        <?php if (!empty($page['badge_text'])): ?>
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-medium mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> <?= esc($page['badge_text']) ?>
        </div>
        <?php endif; ?>
        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900"><?= esc($page['title']) ?></h2>
        <?php if (!empty($page['description'])): ?>
        <p class="mt-4 text-slate-600"><?= esc($page['description']) ?></p>
        <?php endif; ?>
        <?php if (!empty($page['updated_at'])): ?>
        <div class="mt-6 text-xs text-slate-500">Terakhir diperbarui: <?= date('d F Y', strtotime($page['updated_at'])) ?></div>
        <?php endif; ?>
    </div>
</section>

<!-- Main Content -->
<main class="max-w-6xl mx-auto px-4 py-12">
    <div class="prose prose-slate max-w-none">
        <?= $page['content'] ?>
    </div>
    <?php if (!empty($page['sidebar_content'])): ?>
    <div class="mt-12 pt-8 border-t border-gray-200">
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <?= $page['sidebar_content'] ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<style>
.prose {
    color: #334155;
    line-height: 1.8;
}

.prose h3 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #0f172a;
}

.prose h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #1e293b;
}

.prose p {
    margin-bottom: 1rem;
    color: #475569;
}

.prose ul, .prose ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.prose ul {
    list-style-type: disc;
}

.prose ol {
    list-style-type: decimal;
}

.prose ul ul {
    list-style-type: circle;
    margin-top: 0.5rem;
}

.prose ul ul ul {
    list-style-type: square;
}

.prose ol ol {
    list-style-type: lower-alpha;
    margin-top: 0.5rem;
}

.prose ol ol ol {
    list-style-type: lower-roman;
}

.prose li {
    margin-bottom: 0.5rem;
    color: #475569;
    display: list-item;
}

.prose table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.prose table thead {
    background-color: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    text-transform: uppercase;
}

.prose table th,
.prose table td {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.prose table tbody tr:hover {
    background-color: #f8fafc;
}

.prose details {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.prose summary {
    font-weight: 500;
    cursor: pointer;
    color: #0f172a;
}

.prose .alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.prose .alert-success {
    background-color: #d1fae5;
    border: 1px solid #6ee7b7;
    color: #065f46;
}
</style>
<?= $this->endSection() ?>
