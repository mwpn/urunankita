# Module File/Storage

Module untuk mengelola file upload dengan **isolasi per tenant** yang ketat untuk keamanan multi-tenant.

## Fitur Keamanan

✅ **Isolasi Per Tenant**
- File disimpan di folder terpisah: `writable/uploads/tenant_{id}/files/`
- Setiap tenant hanya bisa akses file miliknya sendiri
- Double security check (database + filesystem)

✅ **Security Features**
- Path traversal prevention
- Tenant ownership validation
- File type validation
- File size limits
- Unique filename generation

## Struktur Penyimpanan

```
writable/uploads/
├── tenant_1/
│   └── files/
│       ├── 1_1699000000_a1b2c3d4.jpg
│       └── 1_1699000001_e5f6g7h8.pdf
├── tenant_2/
│   └── files/
│       └── 2_1699000002_i9j0k1l2.png
└── ...
```

## Penggunaan

### Via Service

```php
use Config\Services;

$storageService = Services::storage();
$tenantId = session()->get('tenant_id');

// Upload file
$file = $this->request->getFile('file');
$result = $storageService->upload($file, $tenantId, [
    'max_size' => 5242880, // 5MB
    'allowed_types' => ['jpg', 'png', 'pdf'],
]);

// Download file (with tenant check)
$file = $storageService->download('filename.jpg', $tenantId);
if ($file) {
    return $this->response->download($file->getRealPath(), null);
}

// Delete file (with tenant check)
$deleted = $storageService->delete('filename.jpg', $tenantId);

// List files
$files = $storageService->listFiles($tenantId);

// Get file info
$info = $storageService->getFileInfo('filename.jpg', $tenantId);
```

### Via API

```bash
# Upload
POST /file/upload
Content-Type: multipart/form-data
{
    "file": <file>,
    "folder": "documents",
    "type": "document",
    "description": "Optional description"
}

# Download
GET /file/download/{filename}

# Delete
DELETE /file/delete/{filename}

# List files
GET /file/list?type=image&folder=documents&limit=50

# Get file info
GET /file/info/{filename}
```

### Via Model

```php
use Modules\File\Models\FileModel;

$fileModel = new FileModel();

// Get files by tenant
$files = $fileModel->getByTenant($tenantId, [
    'type' => 'image',
    'folder' => 'products',
    'limit' => 20,
]);

// Find file (with tenant security check)
$file = $fileModel->findByTenantAndFilename('filename.jpg', $tenantId);
```

## Security Validation

Setiap operasi file melakukan validasi:

1. **Upload**: Cek tenant_id dari session
2. **Download**: Verifikasi di database bahwa file milik tenant tersebut
3. **Delete**: Verifikasi ownership sebelum delete
4. **Path Check**: Realpath validation untuk prevent directory traversal

## Allowed File Types

Default allowed types:
- **Images**: jpg, jpeg, png, gif, webp
- **Documents**: pdf, doc, docx, xls, xlsx, txt
- **Archives**: zip, rar, 7z

Custom types bisa di-set saat upload.

## Database Migration

Jalankan migration:

```bash
php spark migrate
```

Table `files` akan dibuat dengan kolom:
- id, original_name, filename, path, full_path
- size, mime_type, extension
- tenant_id, user_id (ownership tracking)
- folder, type, description
- created_at

## Struktur Module

```
Modules/File/
├── Services/
│   └── StorageService.php       # Main storage service
├── Models/
│   └── FileModel.php            # File database model
├── Controllers/
│   └── FileController.php       # API endpoints
├── Config/
│   ├── Routes.php               # Route definitions
│   └── Services.php             # Service registration
└── Database/
    └── Migrations/
        └── CreateFiles.php      # Migration
```

