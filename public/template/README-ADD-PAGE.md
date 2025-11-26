# Cara Menambahkan Halaman View Baru

## Langkah-langkah:

### 1. Buat File Content di `includes/`

Buat file content baru dengan format: `includes/nama-halaman-content.html`

**Contoh:** `includes/urunan-list-content.html`

```html
<main role="main" class="main-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-12">
        <!-- Page Header -->
        <div class="row align-items-center mb-2">
          <div class="col">
            <h2 class="h5 page-title">Urunan Saya</h2>
          </div>
          <div class="col-auto">
            <a href="#" class="btn btn-sm btn-primary" data-content="includes/urunan-create-content.html">
              <span class="fe fe-plus fe-12 mr-1"></span>Buat Urunan Baru
            </a>
          </div>
        </div>
        
        <!-- Content Here -->
        <div class="card shadow">
          <div class="card-body">
            <!-- Your content here -->
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
```

### 2. Tambahkan Menu di Sidebar

Edit `includes/sidebar.html`, tambahkan link dengan format:

```html
<li class="nav-item">
  <a class="nav-link" href="urunan/list.html" data-content="includes/urunan-list-content.html">
    <i class="fe fe-list fe-16"></i>
    <span class="ml-3 item-text">Urunan Saya</span>
  </a>
</li>
```

**Atribut penting:**
- `href` - URL untuk SEO dan browser history
- `data-content` - Path ke file content (wajib untuk navigasi dinamis)

### 3. (Opsional) Update PHP Page Titles

Jika menggunakan `index.php`, tambahkan title di mapping:

Edit `index.php`:
```php
$pageTitles = [
    'dashboard' => 'Dashboard',
    'urunan-create' => 'Buat Urunan Baru',
    'urunan-list' => 'Urunan Saya',  // ← Tambahkan ini
    // ...
];
```

### 4. (Opsional) Update Active Menu Mapping

Edit `js/set-active-menu.php` untuk active state:

```php
$pageMap = [
    'dashboard' => 'index.html',
    'urunan-create' => 'urunan/create.html',
    'urunan-list' => 'urunan/list.html',  // ← Tambahkan ini
    // ...
];
```

## Contoh Lengkap:

### File: `includes/urunan-list-content.html`
```html
<main role="main" class="main-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="row align-items-center mb-2">
          <div class="col">
            <h2 class="h5 page-title">Urunan Saya</h2>
          </div>
        </div>
        
        <div class="card shadow">
          <div class="card-header">
            <strong class="card-title">Daftar Urunan</strong>
          </div>
          <div class="card-body">
            <p>Content halaman di sini...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
```

### File: `includes/sidebar.html` (tambahkan link)
```html
<li class="nav-item">
  <a class="nav-link" href="urunan/list.html" data-content="includes/urunan-list-content.html">
    <i class="fe fe-list fe-16"></i>
    <span class="ml-3 item-text">Urunan Saya</span>
  </a>
</li>
```

## Tips:

1. **Naming Convention:**
   - Content file: `includes/urunan-list-content.html`
   - Href: `urunan/list.html`
   - Page name (PHP): `urunan-list`

2. **Komponen yang perlu di-initialize:**
   - Select2: `.select2`
   - Quill Editor: `#deskripsi-editor`
   - Dropzone: `#dropzone-single`, `#dropzone-multiple`
   - ApexCharts: `#donasiChart`
   - Date Range Picker: `#reportrange`
   - Form Validation: `.needs-validation`

3. **Link Internal di Content:**
   Jika di content ada link ke halaman lain, gunakan:
   ```html
   <a href="#" data-content="includes/target-content.html" class="nav-link">Link</a>
   ```
   Script akan otomatis handle navigasi.

## Struktur File:

```
ci4/
├── includes/
│   ├── header.html
│   ├── sidebar.html
│   ├── scripts.html
│   ├── dashboard-content.html
│   ├── urunan-create-content.html
│   └── urunan-list-content.html    ← Halaman baru
├── index.html
├── index.php
└── js/
    ├── sidebar-navigation.js
    ├── load-content.js
    └── set-active-menu.php
```

Selamat membuat halaman baru! 🎉

