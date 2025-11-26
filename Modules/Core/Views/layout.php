<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'UrunanKita') ?></title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Preline UI CSS -->
    <link rel="stylesheet" href="/css/output.css?v=<?= time() ?>">
    
    <?= $this->renderSection('head') ?>
</head>
<body class="font-outfit bg-gray-50">
    <?= $this->renderSection('content') ?>
    
    <!-- Preline UI JS -->
    <script src="/js/preline/preline.js?v=<?= time() ?>"></script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>

