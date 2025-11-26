<?php
// Get platform settings for header and footer (with error handling)
$settings = [];
$isMainDomain = true;

try {
    $db = \Config\Database::connect();
    $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
    $platformTenantId = $platform ? (int) $platform['id'] : null;

    $settingService = \Config\Services::setting();
    $settingKeys = [
        'site_name',
        'site_tagline',
        'site_description',
        'site_logo',
        'site_favicon',
        'site_email',
        'site_phone',
        'site_address',
        'site_facebook',
        'site_instagram',
        'site_twitter',
    ];

    foreach ($settingKeys as $key) {
        $settings[$key] = $settingService->get($key, null, 'global', null);
    }

    // Determine if main domain or subdomain
    $isSubdomain = session()->get('is_subdomain') === true;
    $isMainDomain = !$isSubdomain;
} catch (\Exception $e) {
    // Fallback if database or services are not available
    $settings = [
        'site_name' => 'UrunanKita',
        'site_tagline' => 'Mari Berbagi Kebaikan',
    ];
}

// Helper function untuk base_url
if (!function_exists('base_url')) {
    function base_url($path = '') {
        // Try to get from config
        if (class_exists('\Config\App')) {
            $config = new \Config\App();
            $baseURL = $config->baseURL ?? 'http://localhost';
        } else {
            // Fallback: construct from current request
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseURL = $protocol . '://' . $host;
        }
        return rtrim($baseURL, '/') . '/' . ltrim($path, '/');
    }
}

// Helper function untuk esc
if (!function_exists('esc')) {
    function esc($string, $context = 'html') {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($settings['site_name'] ?? 'UrunanKita') ?> - Halaman Tidak Ditemukan</title>
    <?php if (!empty($settings['site_description'])): ?>
    <meta name="description" content="<?= esc($settings['site_description']) ?>">
    <?php endif; ?>
    <?php if (!empty($settings['site_favicon'])): ?>
    <?php 
    $faviconUrl = $settings['site_favicon'];
    if (preg_match('~^https?://~', $faviconUrl)) {
        $faviconSrc = $faviconUrl;
    } else {
        $faviconSrc = base_url(ltrim($faviconUrl, '/'));
    }
    ?>
    <link rel="icon" type="image/x-icon" href="<?= esc($faviconSrc) ?>">
    <?php endif; ?>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
                        'inter': ['Outfit', 'sans-serif'],
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
</head>
<body class="font-inter bg-gray-50 text-gray-800">
    <!-- Header -->
    <?php
    try {
        $headerPath = ROOTPATH . 'Modules/Public/Views/partials/header.php';
        if (file_exists($headerPath)):
            // Include header dengan settings
            $authUser = session()->get('auth_user') ?? [];
            $userRole = $authUser['role'] ?? null;
            $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);
            $isLoggedIn = !empty($authUser);
            include $headerPath;
        else:
            // Simple header fallback
            ?>
            <header class="sticky top-0 z-50 bg-white shadow-sm">
                <div class="container mx-auto px-4">
                    <nav class="flex justify-between items-center py-4">
                        <a href="<?= base_url('/') ?>" class="flex items-center space-x-3">
                            <span class="text-2xl font-bold text-primary-600"><?= esc($settings['site_name'] ?? 'UrunanKita') ?></span>
                        </a>
                        <a href="<?= base_url('/') ?>" class="bg-primary-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">Beranda</a>
                    </nav>
                </div>
            </header>
            <?php
        endif;
    } catch (\Exception $e) {
        // Simple header fallback on error
        ?>
        <header class="sticky top-0 z-50 bg-white shadow-sm">
            <div class="container mx-auto px-4">
                <nav class="flex justify-between items-center py-4">
                    <a href="<?= base_url('/') ?>" class="flex items-center space-x-3">
                        <span class="text-2xl font-bold text-primary-600"><?= esc($settings['site_name'] ?? 'UrunanKita') ?></span>
                    </a>
                    <a href="<?= base_url('/') ?>" class="bg-primary-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">Beranda</a>
                </nav>
            </div>
        </header>
        <?php
    }
    ?>

    <!-- Main Content - 404 Error -->
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-primary-600 mb-4">404</h1>
                <h2 class="text-3xl font-semibold text-gray-800 mb-4">Halaman Tidak Ditemukan</h2>
                <p class="text-gray-600 mb-8">
                    <?php if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') : ?>
                        <?= nl2br(esc($message ?? 'Halaman yang Anda cari tidak ditemukan.')) ?>
                    <?php else : ?>
                        Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="space-y-4">
                <a href="<?= base_url('/') ?>" class="inline-block bg-primary-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    <i class="fas fa-home mr-2"></i>Kembali ke Beranda
                </a>
                <div class="text-sm text-gray-500">
                    <a href="javascript:history.back()" class="hover:text-primary-600 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Halaman Sebelumnya
                    </a>
                </div>
            </div>
            
            <?php if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production' && !empty($message)): ?>
            <div class="mt-8 p-4 bg-gray-100 rounded-lg text-left">
                <p class="text-xs text-gray-600 font-mono break-all"><?= esc($message) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php
    try {
        $footerPath = ROOTPATH . 'Modules/Public/Views/partials/footer.php';
        if (file_exists($footerPath)):
            include $footerPath;
        else:
            // Simple footer fallback
            ?>
            <footer class="bg-gray-900 text-gray-300 pt-8 pb-4">
                <div class="container mx-auto px-4 text-center">
                    <p>© <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'UrunanKita') ?>. All rights reserved.</p>
                </div>
            </footer>
            <?php
        endif;
    } catch (\Exception $e) {
        // Simple footer fallback on error
        ?>
        <footer class="bg-gray-900 text-gray-300 pt-8 pb-4">
            <div class="container mx-auto px-4 text-center">
                <p>© <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'UrunanKita') ?>. All rights reserved.</p>
            </div>
        </footer>
        <?php
    }
    ?>
</body>
</html>
