# Module Notification

Module untuk mengirim notifikasi melalui berbagai channel (WhatsApp, Email, SMS).

## Fitur

- ✅ WhatsApp API Integration (whappi.biz.id)
- ✅ Notification Logging
- ✅ Bulk Notification Support
- ✅ Multi-tenant Support

## Konfigurasi

Tambahkan konfigurasi di `.env`:

```env
whatsapp.api_url = https://app.whappi.biz.id/api/qr/rest/send_message
whatsapp.api_token = your_token_here
whatsapp.from_number = 6282119339330
```

## Penggunaan

### Via Service

```php
use Config\Services;

// Send single WhatsApp
$notificationService = Services::notification();
$result = $notificationService->sendWhatsApp(
    '6281234567890',
    'Hello, this is a test message!',
    [
        'type' => 'order',
        'user_id' => 1,
        'tenant_id' => session()->get('tenant_id'),
    ]
);

// Send bulk WhatsApp
$result = $notificationService->sendBulkWhatsApp(
    ['6281234567890', '6289876543210'],
    'Bulk notification message',
    ['type' => 'announcement']
);

// Get logs
$logs = $notificationService->getLogs([
    'tenant_id' => 1,
    'channel' => 'whatsapp',
    'limit' => 10,
]);
```

### Via Helper Function

```php
helper('Modules\\Notification\\Helpers\\notification');

// Send single
$result = send_whatsapp('6281234567890', 'Hello!', ['type' => 'order']);

// Send bulk
$result = send_bulk_whatsapp(
    ['6281234567890', '6289876543210'],
    'Bulk message'
);
```

### Via Controller

```php
// POST /notification/whatsapp/send
{
    "to": "6281234567890",
    "message": "Hello from API!",
    "type": "order"
}

// GET /notification/logs?limit=50
```

## Database Migration

Jalankan migration untuk membuat table `notification_logs`:

```bash
php spark migrate
```

## Struktur

```
Modules/Notification/
├── Services/
│   ├── NotificationService.php  # Main service
│   └── WhatsAppService.php      # WhatsApp API implementation
├── Models/
│   └── NotificationLogModel.php # Log model
├── Controllers/
│   └── NotificationController.php
├── Helpers/
│   └── notification_helper.php
├── Config/
│   ├── Routes.php
│   └── Services.php
└── Database/
    └── Migrations/
        └── CreateNotificationLogs.php
```

