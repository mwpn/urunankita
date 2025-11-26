<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard - UrunanKita') ?></title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Preline UI CSS -->
    <link rel="stylesheet" href="/css/output.css?v=<?= time() ?>">

    <?= $this->renderSection('head') ?>
</head>

<body class="font-outfit bg-gray-50">
    <!-- Sidebar -->
    <?= $this->include('Modules\Core\Views\partials\sidebar') ?>

    <!-- Main Content -->
    <div class="w-full lg:ps-64">
        <!-- Header -->
        <?= $this->include('Modules\Core\Views\partials\header') ?>

        <!-- Page Content -->
        <main class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Preline UI JS -->
    <script src="/js/preline/preline.js?v=<?= time() ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>