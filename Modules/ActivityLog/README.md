# Module ActivityLog

Module untuk tracking aktivitas user dengan **tenant isolation** yang ketat untuk audit trail dan compliance.

## Fitur

✅ **Comprehensive Tracking**
- CRUD operations (create, update, delete)
- Login/Logout tracking
- View/access tracking
- Custom actions

✅ **Tenant Isolation**
- Setiap tenant hanya bisa lihat log miliknya sendiri
- Auto-detect tenant dari session
- Database-level isolation

✅ **Rich Data Capture**
- Old value & New value (untuk update)
- IP address tracking
- User agent tracking
- Metadata support
- Timestamps

## Penggunaan

### Via Service

```php
use Config\Services;

$activityLogService = Services::activityLog();

// Log create
$activityLogService->logCreate('Product', $productId, $productData, 'Product created');

// Log update
$activityLogService->logUpdate('Product', $productId, $oldData, $newData, 'Product updated');

// Log delete
$activityLogService->logDelete('Product', $productId, $productData, 'Product deleted');

// Log login
$activityLogService->logLogin($userId, ['ip' => $ipAddress]);

// Log logout
$activityLogService->logLogout($userId);

// Log custom action
$activityLogService->log('export', 'Order', null, [
    'description' => 'Exported orders to Excel',
    'metadata' => ['file' => 'orders.xlsx'],
]);
```

### Via Helper Functions

```php
helper('Modules\\ActivityLog\\Helpers\\activity_log');

// Quick logging
log_create('Product', $productId, $data);
log_update('Product', $productId, $oldData, $newData);
log_delete('Product', $productId, $data);

// Custom log
log_activity('approve', 'Order', $orderId, ['description' => 'Order approved']);
```

### Get Logs

```php
$activityLogService = Services::activityLog();

// Get all logs (with tenant isolation)
$logs = $activityLogService->getLogs([
    'action' => 'create',
    'entity' => 'Product',
    'date_from' => '2025-01-01',
    'date_to' => '2025-12-31',
    'limit' => 50,
]);

// Get logs for specific entity
$logs = $activityLogService->getEntityLogs('Product', $productId);
```

## API Endpoints

```bash
# Get logs
GET /activity-log/list?action=create&entity=Product&limit=50

# Get summary
GET /activity-log/summary

# Get entity logs
GET /activity-log/entity/Product/123
```

## Database Migration

Jalankan migration:

```bash
php spark migrate
```

Table `activity_logs` akan dibuat dengan kolom:
- tenant_id, user_id
- action, entity, entity_id
- old_value, new_value (JSON)
- description, ip_address, user_agent
- metadata (JSON)
- created_at

## Auto-Logging Example

Untuk auto-logging di model, bisa ditambahkan di afterInsert, afterUpdate, afterDelete hooks.

