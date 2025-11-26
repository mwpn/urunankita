<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <?php
    // Get platform settings for favicon
    $settingService = \Config\Services::setting();
    $favicon = $settingService->get('site_favicon', null, 'global', null);
    if (!empty($favicon)) {
        $faviconUrl = preg_match('~^https?://~', $favicon) ? $favicon : base_url(ltrim($favicon, '/'));
    } else {
        $faviconUrl = base_url('favicon.ico');
    }
    ?>
    <link rel="icon" href="<?= esc($faviconUrl) ?>">
    <?php
    $siteName = $settingService->get('site_name', 'Urunankita', 'global', null);
    ?>
    <title><?= esc($pageTitle ?? 'Dashboard') ?> - <?= esc($siteName) ?></title>
    
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="<?= base_url('admin-template/css/simplebar.css') ?>">
    
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Icons CSS -->
    <link rel="stylesheet" href="<?= base_url('admin-template/css/feather.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/dataTables.bootstrap4.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/select2.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/dropzone.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/uppy.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/jquery.steps.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/jquery.timepicker.css') ?>">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/quill.snow.css') ?>">
    
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="<?= base_url('admin-template/css/daterangepicker.css') ?>">
    
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= base_url('admin-template/css/app-light.css') ?>" id="lightTheme">
    <link rel="stylesheet" href="<?= base_url('admin-template/css/app-dark.css') ?>" id="darkTheme" disabled>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('admin-template/css/custom.css') ?>">
    
    <?= $this->renderSection('head') ?>
</head>
<body class="vertical light">
    <div class="wrapper">
        <!-- Header/Navbar -->
        <?= $this->include('Modules\Core\Views\template\header') ?>
        
        <!-- Sidebar -->
        <?= $this->include('Modules\Core\Views\template\sidebar') ?>
        
        <!-- Main Content -->
        <main role="main" class="main-content">
            <?= $this->renderSection('content') ?>
        </main>
    </div> <!-- .wrapper -->
    
    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="notificationModalHeader">
                    <h5 class="modal-title" id="notificationModalTitle">Notifikasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="notificationModalBody">
                    <p id="notificationModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalTitle">Konfirmasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmationModalBody">
                    <p id="confirmationModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmationModalConfirm">Ya</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?= $this->include('Modules\Core\Views\template\scripts') ?>
    
    <!-- Initialize sidebar role filter -->
    <script>
        // Initialize sidebar role filter
        if (typeof window.filterSidebarByRole === 'undefined') {
            const scriptRole = document.createElement('script');
            scriptRole.src = '<?= base_url('admin-template/js/sidebar-role.js') ?>';
            document.body.appendChild(scriptRole);
        }

        // Modal Notification Helper
        window.showNotification = function(type, message) {
            const modal = $('#notificationModal');
            const header = $('#notificationModalHeader');
            const title = $('#notificationModalTitle');
            const body = $('#notificationModalBody');
            
            // Remove existing classes
            header.removeClass('bg-success bg-danger bg-warning bg-info text-white text-dark');
            
            // Set type-specific styling
            if (type === 'success') {
                header.addClass('bg-success text-white');
                title.text('Berhasil');
            } else if (type === 'error') {
                header.addClass('bg-danger text-white');
                title.text('Error');
            } else if (type === 'warning') {
                header.addClass('bg-warning text-dark');
                title.text('Peringatan');
            } else {
                header.addClass('bg-info text-white');
                title.text('Informasi');
            }
            
            body.find('#notificationModalMessage').text(message);
            modal.modal('show');
        };

        // Modal Confirmation Helper (replaces confirm())
        window.showConfirmation = function(title, message, onConfirm) {
            const modal = $('#confirmationModal');
            $('#confirmationModalTitle').text(title || 'Konfirmasi');
            $('#confirmationModalMessage').text(message);
            
            // Remove previous event listeners
            $('#confirmationModalConfirm').off('click');
            
            // Add new event listener
            $('#confirmationModalConfirm').on('click', function() {
                modal.modal('hide');
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
            
            modal.modal('show');
        };

        // Replace native alert() with modal
        window.alert = function(message) {
            showNotification('info', message);
        };
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
