<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Pengaturan Menu') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Pengaturan Menu</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" id="btn-save-menu">
                        <span class="fe fe-save fe-12 mr-1"></span>Simpan Menu
                    </button>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Menu Items -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="card-title">Menu <?= $location === 'footer' ? 'Footer' : 'Header' ?></strong>
                            <p class="text-muted mb-0 small">Atur menu yang akan ditampilkan di <?= $location === 'footer' ? 'footer' : 'header' ?> website</p>
                        </div>
                        <div>
                            <a href="<?= base_url('tenant/content/menu' . ($location === 'footer' ? '' : '?location=footer')) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <?= $location === 'footer' ? 'Atur Menu Header' : 'Atur Menu Footer' ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="menuForm" method="POST" action="<?= base_url('tenant/content/menu/store') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="location" value="<?= esc($location ?? 'header') ?>">
                        <div id="menuItemsContainer">
                            <?php if (!empty($menuItems)): ?>
                                <?php foreach ($menuItems as $index => $item): ?>
                                    <?php
                                    // Normalize URL - remove base_url if present
                                    $itemUrl = $item['url'] ?? '';
                                    $itemUrl = preg_replace('~^' . preg_quote(base_url(), '~') . '~', '', $itemUrl);
                                    $itemUrl = ltrim($itemUrl, '/');
                                    if (!empty($itemUrl) && $itemUrl !== '/') {
                                        $itemUrl = '/' . $itemUrl;
                                    } elseif (empty($itemUrl)) {
                                        $itemUrl = '/';
                                    }
                                    ?>
                                    <div class="menu-item-row border rounded p-3 mb-3" data-index="<?= $index ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-1 text-center">
                                                <span class="drag-handle text-muted" style="cursor: move;">
                                                    <i class="fe fe-move fe-16"></i>
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" name="menu_items[<?= $index ?>][label]" placeholder="Label Menu" value="<?= esc($item['label'] ?? '') ?>" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control form-control-sm" name="menu_items[<?= $index ?>][url]" placeholder="URL (contoh: /campaigns atau https://example.com)" value="<?= esc($itemUrl) ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="active_<?= $index ?>" name="menu_items[<?= $index ?>][is_active]" <?= (!isset($item['is_active']) || $item['is_active']) ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="active_<?= $index ?>">Aktif</label>
                                                </div>
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input type="checkbox" class="custom-control-input" id="external_<?= $index ?>" name="menu_items[<?= $index ?>][is_external]" <?= (isset($item['is_external']) && $item['is_external']) ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="external_<?= $index ?>">External</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 text-right">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-menu-item">
                                                    <span class="fe fe-trash-2 fe-12"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3 d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-menu-item">
                                <span class="fe fe-plus fe-12 mr-1"></span>Tambah Menu
                            </button>
                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0 small text-muted">Tambah dari Halaman:</label>
                                <select class="form-control form-control-sm" id="select-page" style="width: auto; min-width: 200px;">
                                    <option value="">Pilih Halaman...</option>
                                    <?php if (!empty($pages)): ?>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= esc($page['slug']) ?>" data-title="<?= esc($page['title']) ?>">
                                                <?= esc($page['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-from-page">
                                    <span class="fe fe-plus fe-12"></span> Tambah
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    let menuItemIndex = <?= !empty($menuItems) ? count($menuItems) : 0 ?>;
    
    $(document).ready(function() {
        // Initialize Sortable
        const container = document.getElementById('menuItemsContainer');
        if (container) {
            new Sortable(container, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function() {
                    // Update order numbers if needed
                    updateMenuOrder();
                }
            });
        }

        // Add menu item
        $('#btn-add-menu-item').on('click', function() {
            const html = `
                <div class="menu-item-row border rounded p-3 mb-3" data-index="${menuItemIndex}">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="drag-handle text-muted" style="cursor: move;">
                                <i class="fe fe-move fe-16"></i>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][label]" placeholder="Label Menu" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][url]" placeholder="URL" required>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="active_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_active]" checked>
                                <label class="custom-control-label" for="active_${menuItemIndex}">Aktif</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" class="custom-control-input" id="external_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_external]">
                                <label class="custom-control-label" for="external_${menuItemIndex}">External</label>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-menu-item">
                                <span class="fe fe-trash-2 fe-12"></span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#menuItemsContainer').append(html);
            menuItemIndex++;
        });

        // Remove menu item
        $(document).on('click', '.remove-menu-item', function() {
            $(this).closest('.menu-item-row').remove();
        });

        // Add menu item from page
        $('#btn-add-from-page').on('click', function() {
            const selectPage = $('#select-page');
            const selectedOption = selectPage.find('option:selected');
            const pageSlug = selectedOption.val();
            const pageTitle = selectedOption.data('title');
            
            if (!pageSlug) {
                showNotification('warning', 'Pilih halaman terlebih dahulu');
                return;
            }
            
            // Check if page already in menu
            let alreadyExists = false;
            $('#menuItemsContainer input[name*="[url]"]').each(function() {
                const url = $(this).val();
                if (url === '/page/' + pageSlug || url === 'page/' + pageSlug) {
                    alreadyExists = true;
                    return false;
                }
            });
            
            if (alreadyExists) {
                showNotification('warning', 'Halaman ini sudah ada di menu');
                return;
            }
            
            const html = `
                <div class="menu-item-row border rounded p-3 mb-3" data-index="${menuItemIndex}">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="drag-handle text-muted" style="cursor: move;">
                                <i class="fe fe-move fe-16"></i>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][label]" placeholder="Label Menu" value="${pageTitle}" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][url]" placeholder="URL" value="/page/${pageSlug}" required>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="active_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_active]" checked>
                                <label class="custom-control-label" for="active_${menuItemIndex}">Aktif</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" class="custom-control-input" id="external_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_external]">
                                <label class="custom-control-label" for="external_${menuItemIndex}">External</label>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-menu-item">
                                <span class="fe fe-trash-2 fe-12"></span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#menuItemsContainer').append(html);
            menuItemIndex++;
            
            // Reset select
            selectPage.val('');
        });

        // Save menu
        $('#btn-save-menu').on('click', function() {
            const form = document.getElementById('menuForm');
            if (form.checkValidity() === false) {
                form.classList.add('was-validated');
                return false;
            }
            form.submit();
        });

        function updateMenuOrder() {
            // Re-index menu items if needed
            $('#menuItemsContainer .menu-item-row').each(function(index) {
                $(this).attr('data-index', index);
            });
        }
    });
</script>
<?= $this->endSection() ?>

