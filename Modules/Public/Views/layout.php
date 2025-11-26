<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? ($settings['site_name'] ?? 'UrunanKita') . ' - ' . ($settings['site_tagline'] ?? 'Mari Berbagi Kebaikan')) ?></title>
    <?php if (!empty($settings['site_description'])): ?>
    <meta name="description" content="<?= esc($settings['site_description']) ?>">
    <?php endif; ?>
    <?php if (!empty($settings['site_favicon'])): ?>
    <?php 
    $faviconUrl = $settings['site_favicon'];
    // If already full URL, use as is. Otherwise, prepend base_url
    if (preg_match('~^https?://~', $faviconUrl)) {
        $faviconSrc = $faviconUrl;
    } else {
        // Remove leading slash if exists, then add base_url
        $faviconSrc = base_url(ltrim($faviconUrl, '/'));
    }
    ?>
    <link rel="icon" type="image/x-icon" href="<?= esc($faviconSrc) ?>">
    <?php endif; ?>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    // Get font from settings
    $fontName = $settings['frontend_font'] ?? 'Outfit';
    $fontWeights = $settings['frontend_font_weights'] ?? '300;400;500;600;700';
    $fontWeightsArray = explode(';', $fontWeights);
    $fontWeightsArray = array_filter(array_map('trim', $fontWeightsArray));
    $fontWeightsString = !empty($fontWeightsArray) ? implode(';', $fontWeightsArray) : '300;400;500;600;700';
    $fontUrl = 'https://fonts.googleapis.com/css2?family=' . urlencode($fontName) . ':wght@' . $fontWeightsString . '&display=swap';
    ?>
    <link href="<?= esc($fontUrl) ?>" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9f0',
                            100: '#dcf2dc',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        secondary: {
                            50: '#fff7ed',
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    },
                    fontFamily: {
                        'inter': ['<?= esc($fontName) ?>', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        .hero-bg {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }
        .campaign-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }
    </style>
    
    <?= $this->renderSection('head') ?>
</head>
<body class="font-inter bg-gray-50 text-gray-800">
    <!-- Header -->
    <?php
    $headerPath = ROOTPATH . 'Modules/Public/Views/partials/header.php';
    if (file_exists($headerPath)):
    ?>
        <?= $this->include('Modules\Public\Views\partials\header') ?>
    <?php endif; ?>

    <!-- Main Content -->
    <?= $this->renderSection('content') ?>

    <!-- Footer -->
    <?php
    $footerPath = ROOTPATH . 'Modules/Public/Views/partials/footer.php';
    if (file_exists($footerPath)):
    ?>
        <?= $this->include('Modules\Public\Views\partials\footer') ?>
    <?php endif; ?>

    <!-- Page Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
