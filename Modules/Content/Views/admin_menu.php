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
                            <a href="<?= base_url('admin/content/menu' . ($location === 'footer' ? '' : '?location=footer')) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <?= $location === 'footer' ? 'Atur Menu Header' : 'Atur Menu Footer' ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="menuForm" method="POST" action="<?= base_url('admin/content/menu/store') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="location" value="<?= esc($location ?? 'header') ?>">
                        <div id="menuItemsContainer">
                            <?php 
                            // Build flat list with parent_id info for rendering
                            $flatMenuItems = [];
                            $buildFlatMenu = function($items, $parentId = null, $level = 0) use (&$flatMenuItems, &$buildFlatMenu) {
                                foreach ($items as $item) {
                                    $item['_level'] = $level;
                                    $item['_parent_id'] = $parentId;
                                    $flatMenuItems[] = $item;
                                    
                                    // If item has children, recursively add them
                                    if (!empty($item['children'])) {
                                        $buildFlatMenu($item['children'], $item['id'], $level + 1);
                                    }
                                }
                            };
                            
                            // Get hierarchical menu
                            $menuModel = new \Modules\Content\Models\MenuItemModel();
                            $menuLocation = $location ?? 'header';
                            $hierarchicalMenu = $menuModel->getMenuItemsHierarchical(null, false, $menuLocation);
                            
                            if (!empty($hierarchicalMenu)) {
                                $buildFlatMenu($hierarchicalMenu);
                            } elseif (!empty($menuItems)) {
                                // Fallback to flat menu if no hierarchy
                                foreach ($menuItems as $item) {
                                    $item['_level'] = 0;
                                    $item['_parent_id'] = $item['parent_id'] ?? null;
                                    $flatMenuItems[] = $item;
                                }
                            }
                            
                            if (!empty($flatMenuItems)):
                                foreach ($flatMenuItems as $index => $item):
                                    $level = $item['_level'] ?? 0;
                                    $parentId = $item['_parent_id'] ?? ($item['parent_id'] ?? null);
                                    
                                    // Normalize URL - remove base_url if present
                                    $itemUrl = $item['url'] ?? '';
                                    $itemUrl = preg_replace('~^' . preg_quote(base_url(), '~') . '~', '', $itemUrl);
                                    $itemUrl = ltrim($itemUrl, '/');
                                    if (!empty($itemUrl) && $itemUrl !== '/') {
                                        $itemUrl = '/' . $itemUrl;
                                    } elseif (empty($itemUrl)) {
                                        $itemUrl = '/';
                                    }
                                    
                                    $itemId = $item['id'] ?? 'new_' . $index;
                            ?>
                                    <div class="menu-item-row border rounded p-3 mb-3" data-index="<?= $index ?>" data-id="<?= $itemId ?>" data-level="<?= $level ?>" style="margin-left: <?= $level * 30 ?>px;">
                                        <input type="hidden" name="menu_items[<?= $index ?>][id]" value="<?= $itemId ?>">
                                        <input type="hidden" name="menu_items[<?= $index ?>][parent_id]" value="<?= $parentId ?>" class="parent-id-input">
                                        <div class="row align-items-center">
                                            <div class="col-md-1 text-center">
                                                <span class="drag-handle text-muted" style="cursor: move;">
                                                    <i class="fe fe-move fe-16"></i>
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control form-control-sm" name="menu_items[<?= $index ?>][label]" placeholder="Nama Menu" value="<?= esc($item['label'] ?? '') ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control form-control-sm" name="menu_items[<?= $index ?>][url]" placeholder="Route (contoh: /campaigns)" value="<?= esc($itemUrl) ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control form-control-sm select-page-dropdown" name="menu_items[<?= $index ?>][page_slug]" data-index="<?= $index ?>">
                                                    <option value="">Pilih Halaman...</option>
                                                    <?php if (!empty($pages)): ?>
                                                        <?php foreach ($pages as $page): ?>
                                                            <?php 
                                                            $pageUrl = '/page/' . $page['slug'];
                                                            $isSelected = ($itemUrl === $pageUrl) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= esc($page['slug']) ?>" data-url="<?= esc($pageUrl) ?>" <?= $isSelected ?>>
                                                                <?= esc($page['title']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="active_<?= $index ?>" name="menu_items[<?= $index ?>][is_active]" <?= (!isset($item['is_active']) || $item['is_active']) ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="active_<?= $index ?>">Aktif</label>
                                                </div>
                                                <div class="custom-control custom-checkbox mt-1">
                                                    <input type="checkbox" class="custom-control-input" id="external_<?= $index ?>" name="menu_items[<?= $index ?>][is_external]" <?= (isset($item['is_external']) && $item['is_external']) ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="external_<?= $index ?>">New Tab</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-right">
                                                <button type="button" class="btn btn-sm btn-outline-info add-submenu-item" data-parent-id="<?= $itemId ?>" title="Tambah Sub Menu">
                                                    <span class="fe fe-plus-circle fe-12"></span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-menu-item">
                                                    <span class="fe fe-trash-2 fe-12"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
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
    let menuItemIndex = <?= isset($flatMenuItems) && !empty($flatMenuItems) ? count($flatMenuItems) : 0 ?>;
    
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

        // Add menu item (root level)
        $('#btn-add-menu-item').on('click', function() {
            addMenuItem(null, 0);
        });

        // Add submenu item
        $(document).on('click', '.add-submenu-item', function() {
            const parentId = $(this).data('parent-id');
            const parentRow = $(this).closest('.menu-item-row');
            const parentLevel = parseInt(parentRow.data('level') || 0);
            addMenuItem(parentId, parentLevel + 1, parentRow);
        });

        function addMenuItem(parentId, level, insertAfter = null) {
            const itemId = 'new_' + menuItemIndex;
            const marginLeft = level * 30;
            const html = `
                <div class="menu-item-row border rounded p-3 mb-3" data-index="${menuItemIndex}" data-id="${itemId}" data-level="${level}" style="margin-left: ${marginLeft}px;">
                    <input type="hidden" name="menu_items[${menuItemIndex}][id]" value="${itemId}">
                    <input type="hidden" name="menu_items[${menuItemIndex}][parent_id]" value="${parentId || ''}" class="parent-id-input">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="drag-handle text-muted" style="cursor: move;">
                                <i class="fe fe-move fe-16"></i>
                            </span>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][label]" placeholder="Nama Menu" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][url]" placeholder="Route" required>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control form-control-sm select-page-dropdown" name="menu_items[${menuItemIndex}][page_slug]" data-index="${menuItemIndex}">
                                <option value="">Pilih Halaman...</option>
                                <?php if (!empty($pages)): ?>
                                    <?php foreach ($pages as $page): ?>
                                        <option value="<?= esc($page['slug']) ?>" data-url="/page/<?= esc($page['slug']) ?>">
                                            <?= esc($page['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="active_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_active]" checked>
                                <label class="custom-control-label" for="active_${menuItemIndex}">Aktif</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" class="custom-control-input" id="external_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_external]">
                                <label class="custom-control-label" for="external_${menuItemIndex}">New Tab</label>
                            </div>
                        </div>
                        <div class="col-md-3 text-right">
                            <button type="button" class="btn btn-sm btn-outline-info add-submenu-item" data-parent-id="${itemId}" title="Tambah Sub Menu">
                                <span class="fe fe-plus-circle fe-12"></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-menu-item">
                                <span class="fe fe-trash-2 fe-12"></span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            if (insertAfter) {
                insertAfter.after(html);
            } else {
                $('#menuItemsContainer').append(html);
            }
            menuItemIndex++;
        }

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
            
            const itemId = 'new_' + menuItemIndex;
            const html = `
                <div class="menu-item-row border rounded p-3 mb-3" data-index="${menuItemIndex}" data-id="${itemId}" data-level="0" style="margin-left: 0px;">
                    <input type="hidden" name="menu_items[${menuItemIndex}][id]" value="${itemId}">
                    <input type="hidden" name="menu_items[${menuItemIndex}][parent_id]" value="" class="parent-id-input">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="drag-handle text-muted" style="cursor: move;">
                                <i class="fe fe-move fe-16"></i>
                            </span>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][label]" placeholder="Nama Menu" value="${pageTitle}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="menu_items[${menuItemIndex}][url]" placeholder="Route" value="/page/${pageSlug}" required>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control form-control-sm select-page-dropdown" name="menu_items[${menuItemIndex}][page_slug]" data-index="${menuItemIndex}">
                                <option value="">Pilih Halaman...</option>
                                <?php if (!empty($pages)): ?>
                                    <?php foreach ($pages as $page): ?>
                                        <option value="<?= esc($page['slug']) ?>" data-url="/page/<?= esc($page['slug']) ?>" ${pageSlug === '<?= esc($page['slug']) ?>' ? 'selected' : ''}>
                                            <?= esc($page['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="active_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_active]" checked>
                                <label class="custom-control-label" for="active_${menuItemIndex}">Aktif</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" class="custom-control-input" id="external_${menuItemIndex}" name="menu_items[${menuItemIndex}][is_external]">
                                <label class="custom-control-label" for="external_${menuItemIndex}">New Tab</label>
                            </div>
                        </div>
                        <div class="col-md-3 text-right">
                            <button type="button" class="btn btn-sm btn-outline-info add-submenu-item" data-parent-id="${itemId}" title="Tambah Sub Menu">
                                <span class="fe fe-plus-circle fe-12"></span>
                            </button>
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

        // Handle page dropdown change - auto fill URL and label
        $(document).on('change', '.select-page-dropdown', function() {
            const selectedOption = $(this).find('option:selected');
            const pageUrl = selectedOption.data('url');
            const pageTitle = selectedOption.text();
            const row = $(this).closest('.menu-item-row');
            const urlInput = row.find('input[name*="[url]"]');
            const labelInput = row.find('input[name*="[label]"]');
            
            if (pageUrl && selectedOption.val()) {
                urlInput.val(pageUrl);
                // Auto fill label if empty
                if (!labelInput.val() || labelInput.val().trim() === '') {
                    labelInput.val(pageTitle);
                }
            }
        });

        // Save menu
        $('#btn-save-menu').on('click', function() {
            // Update order before submit to ensure correct order
            updateMenuOrder();
            
            const form = document.getElementById('menuForm');
            if (form.checkValidity() === false) {
                form.classList.add('was-validated');
                return false;
            }
            form.submit();
        });

        function updateMenuOrder() {
            // Re-index menu items based on DOM order
            $('#menuItemsContainer .menu-item-row').each(function(newIndex) {
                const $row = $(this);
                
                // Update data-index
                $row.attr('data-index', newIndex);
                
                // Update all input/select names to use new index
                $row.find('input, select').each(function() {
                    const $field = $(this);
                    let name = $field.attr('name');
                    
                    if (name && name.includes('menu_items[')) {
                        // Extract field name (e.g., "menu_items[0][label]" -> "label")
                        const match = name.match(/menu_items\[\d+\]\[(.+)\]$/);
                        if (match) {
                            const fieldName = match[1];
                            // Update name with new index
                            const newName = `menu_items[${newIndex}][${fieldName}]`;
                            $field.attr('name', newName);
                            
                            // Update id for checkboxes/labels
                            if (fieldName === 'is_active') {
                                const newId = `active_${newIndex}`;
                                $field.attr('id', newId);
                                const $label = $field.next('label');
                                if ($label.length) {
                                    $label.attr('for', newId);
                                }
                            } else if (fieldName === 'is_external') {
                                const newId = `external_${newIndex}`;
                                $field.attr('id', newId);
                                const $label = $field.next('label');
                                if ($label.length) {
                                    $label.attr('for', newId);
                                }
                            }
                        }
                    }
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>

