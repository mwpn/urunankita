# Module Setting

Module untuk manage settings dengan **multi-level support** (Global, Tenant, User) dengan fallback mechanism.

## Fitur

✅ **Multi-Level Settings**
- **Global**: System-wide settings
- **Tenant**: Per-tenant settings (dengan fallback ke global)
- **User**: Per-user settings (dengan fallback ke tenant → global)

✅ **Auto Type Detection**
- String, Integer, Float, Boolean, JSON
- Auto encoding/decoding

✅ **Cache Support**
- In-memory cache untuk performance
- Auto cache invalidation

✅ **Tenant Isolation**
- Settings terisolasi per tenant
- Fallback ke global jika tenant setting tidak ada

## Struktur Hierarchy

```
User Setting (highest priority)
  ↓ (fallback)
Tenant Setting
  ↓ (fallback)
Global Setting (lowest priority)
```

## Penggunaan

### Via Service

```php
use Config\Services;

$settingService = Services::setting();

// Get setting (auto-detect scope: tenant → global)
$value = $settingService->get('app_name', 'Default App Name');

// Get tenant setting (dengan fallback ke global)
$value = $settingService->getTenant('currency', 'IDR', $tenantId);

// Set tenant setting
$settingService->setTenant('currency', 'USD', $tenantId);
$settingService->setTenant('timezone', 'Asia/Jakarta');

// Set global setting
$settingService->set('app_version', '1.0.0', 'global');

// Get all settings
$allSettings = $settingService->getAll('tenant', $tenantId);

// Delete setting
$settingService->delete('old_setting', 'tenant', $tenantId);
```

### Multi-Type Support

```php
// String
$settingService->set('app_name', 'My App');

// Integer
$settingService->set('max_users', 100);

// Boolean
$settingService->set('feature_enabled', true);

// JSON/Array
$settingService->set('notifications', [
    'email' => true,
    'sms' => false,
    'whatsapp' => true,
], 'tenant', $tenantId);

// Get with type auto-detection
$appName = $settingService->get('app_name'); // string
$maxUsers = $settingService->get('max_users'); // int
$enabled = $settingService->get('feature_enabled'); // bool
$notifications = $settingService->get('notifications'); // array
```

### Scoped Settings

```php
// Global setting
$settingService->set('maintenance_mode', false, 'global');

// Tenant setting (override global)
$settingService->set('maintenance_mode', true, 'tenant', $tenantId);

// User setting (override tenant & global)
$settingService->set('theme', 'dark', 'user', $userId);

// Get with fallback
$theme = $settingService->get('theme', 'light', 'user', $userId);
// Returns: user setting → tenant setting → global setting → default
```

## API Endpoints

```bash
# Get setting
GET /setting/get/{key}?scope=tenant&scope_id=1

# Set setting
POST /setting/set
{
    "key": "app_name",
    "value": "My App",
    "scope": "tenant",
    "scope_id": 1,
    "type": "string",
    "description": "Application name"
}

# Get all settings
GET /setting/all?scope=tenant&scope_id=1

# Delete setting
DELETE /setting/delete/{key}?scope=tenant&scope_id=1

# Tenant shortcuts
GET /setting/tenant/{key}
POST /setting/tenant/set
{
    "key": "currency",
    "value": "USD"
}
```

## Database Migration

Jalankan migration:

```bash
php spark migrate
```

Table `settings` akan dibuat dengan kolom:
- key, value, type
- scope (global, tenant, user)
- scope_id (tenant_id atau user_id, NULL untuk global)
- description, created_at, updated_at

## Best Practices

1. **Use descriptive keys**: `app.name`, `payment.currency`, `feature.notifications`
2. **Set defaults**: Selalu provide default value saat get
3. **Use tenant scope**: Untuk settings yang berbeda per tenant
4. **Cache wisely**: Service sudah punya built-in cache
5. **Type explicitly**: Specify type jika perlu (auto-detect usually works)

