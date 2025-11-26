<?php
// Get content from URL parameter or default to dashboard
$content = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$contentFile = 'includes/' . $content . '-content.html';

// Validate content file exists
if (!file_exists($contentFile)) {
    $contentFile = 'includes/dashboard-content.html';
    $content = 'dashboard';
}

// Page titles mapping
$pageTitles = [
    'dashboard' => 'Dashboard',
    'urunan-create' => 'Buat Urunan Baru',
    'urunan-list' => 'Urunan Saya',
    'urunan-detail' => 'Detail Urunan',
    'urunan-all' => 'Semua Urunan',
    'urunan-donasi' => 'Donasi Masuk',
    'urunan-laporan' => 'Laporan Penggunaan Dana',
    'urunan-laporan-semua' => 'Laporan Semua Urunan',
    'urunan-riwayat' => 'Riwayat & Log',
    'urunan-diskusi' => 'Diskusi & Komentar',
    'admin-penggalang-dana-add' => 'Tambah Penggalang Dana',
    'admin-penggalang-dana-list' => 'Daftar Penggalang Dana',
    'content-banner' => 'Banner & Slider',
    'content-halaman' => 'Halaman',
    'content-blog' => 'Artikel/Blog',
    'profile-overview' => 'Profil Saya',
    'profile-settings' => 'Pengaturan',
    'admin-settings-general' => 'Pengaturan Umum',
    'settings-payment' => 'Metode Pembayaran',
    'settings-rekening' => 'Daftar Rekening',
    'helpdesk-support' => 'Support'
];

$pageTitle = isset($pageTitles[$content]) ? $pageTitles[$content] : 'Dashboard';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <title><?php echo $pageTitle; ?> - Urunankita</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="css/simplebar.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="css/feather.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="css/select2.css">
    <link rel="stylesheet" href="css/dropzone.css">
    <link rel="stylesheet" href="css/uppy.min.css">
    <link rel="stylesheet" href="css/jquery.steps.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/quill.snow.css">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="css/daterangepicker.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="css/app-light.css" id="lightTheme">
    <link rel="stylesheet" href="css/app-dark.css" id="darkTheme" disabled>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">
</head>

<body class="vertical  light  ">
    <div class="wrapper">
        <!-- Header/Navbar -->
        <?php include 'includes/header.html'; ?>

        <!-- Sidebar -->
        <?php include 'includes/sidebar.html'; ?>

        <!-- Main Content -->
        <div id="main-content-placeholder">
            <?php include $contentFile; ?>
        </div>
        <!-- Content loaded flag -->
        <script>
            window.contentLoaded = true;
            window.currentContent = '<?php echo $content; ?>';
        </script>
    </div> <!-- .wrapper -->

    <!-- Scripts -->
    <?php include 'includes/scripts.html'; ?>

    <!-- Initialize components -->
    <script>
        // Set active menu item
        const scriptActive = document.createElement('script');
        scriptActive.src = 'js/set-active-menu.php?page=<?php echo $content; ?>';
        document.body.appendChild(scriptActive);

        // Initialize sidebar role filter
        const scriptRole = document.createElement('script');
        scriptRole.src = 'js/sidebar-role.js';
        scriptRole.onload = function() {
            // Load sidebar navigation handler
            const scriptNav = document.createElement('script');
            scriptNav.src = 'js/sidebar-navigation.js';
            scriptNav.onload = function() {
                // Load content loader
                const scriptLoad = document.createElement('script');
                scriptLoad.src = 'js/load-content.js';
                scriptLoad.onload = function() {
                    // Initialize sidebar navigation
                    if (window.initSidebarNavigation) {
                        setTimeout(function() {
                            window.initSidebarNavigation();
                        }, 100);
                    }
                    // Initialize content components (for PHP loaded content)
                    if (window.contentLoaded) {
                        // Load load-content.js first to get initializeContent function
                        const scriptLoadContent = document.createElement('script');
                        scriptLoadContent.src = 'js/load-content.js';
                        scriptLoadContent.onload = function() {
                            setTimeout(function() {
                                if (window.initializeContent) {
                                    window.initializeContent();
                                } else {
                                    console.error('initializeContent function not found');
                                }
                            }, 500);
                        };
                        document.body.appendChild(scriptLoadContent);
                    }
                };
                document.body.appendChild(scriptLoad);
            };
            document.body.appendChild(scriptNav);
        };
        document.body.appendChild(scriptRole);

        // Initialize chart after scripts loaded (for dashboard)
        setTimeout(function() {
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#donasiChart")) {
                var options = {
                    series: [{
                        name: 'Donasi',
                        data: [45000000, 52000000, 48000000, 61000000, 55000000, 67000000, 63000000]
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "Rp " + val.toLocaleString('id-ID');
                            }
                        }
                    }
                };
                var chart = new ApexCharts(document.querySelector("#donasiChart"), options);
                chart.render();
            }
        }, 1000);

        // Initialize date range picker
        setTimeout(function() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.daterangepicker && document.getElementById('reportrange')) {
                jQuery('#reportrange').daterangepicker({
                    opens: 'left',
                    locale: {
                        format: 'DD/MM/YYYY'
                    }
                }, function(start, end, label) {
                    console.log("Date range selected");
                });
            }
        }, 1500);
    </script>
</body>

</html>