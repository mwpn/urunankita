# 🚀 Deployment Checklist untuk Produksi

## ⚠️ PENTING: Setelah Pull di Produksi

### Langkah-Langkah Setelah `git pull origin master`

```bash
# 1. Pull latest code
git pull origin master

# 2. Install/update dependencies (jika ada perubahan composer.json)
composer install --no-dev --optimize-autoloader

# 3. Jalankan migrations (WAJIB!)
php spark migrate

# 4. Clear cache
php spark cache:clear

# 5. Optimize (opsional, untuk performance)
php spark optimize
```

---

### 0. Jalankan Database Migrations (WAJIB!)

**Cara 1: Menggunakan Spark Command (Recommended)**

```bash
# Jalankan semua migrations (App + Modules)
php spark migrate

# Atau jalankan module migrations secara spesifik
php spark migrate:modules
```

**Cara 2: Jika Migration Error (Duplicate Column, dll)**

Jika ada error seperti "Duplicate column" atau migration tidak terdeteksi, jalankan migration secara manual:

```bash
# Jalankan migration untuk module Campaign (termasuk campaign_staff)
php spark migrate -n Modules\Campaign -g default
```

**Cara 3: Manual SQL (Jika Migration Command Bermasalah)**

Jika migration command tidak berjalan, buat tabel secara manual:

```sql
-- Buat tabel campaign_staff untuk fitur assign staff ke campaign
CREATE TABLE IF NOT EXISTS `campaign_staff` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID Urunan',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID Staff User',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `campaign_id` (`campaign_id`),
    KEY `user_id` (`user_id`),
    UNIQUE KEY `campaign_user_unique` (`campaign_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Verifikasi Tabel Sudah Dibuat:**

```sql
-- Cek apakah tabel campaign_staff sudah ada
SHOW TABLES LIKE 'campaign_staff';

-- Atau cek struktur tabel
DESCRIBE campaign_staff;
```

**Catatan Penting:**

- Backup database sebelum menjalankan migration
- Jika ada error, cek log di `writable/logs/`
- Migration akan otomatis skip jika tabel sudah ada (jika menggunakan `CREATE TABLE IF NOT EXISTS`)

---

### 1. Pastikan Template Notifikasi Enabled (WAJIB!)

**Cara TERMUDAH - Gunakan PHP script ini:**

```bash
php app/Database/Scripts/ensure_tenant_notification_template.php
```

Script ini akan:

- Memastikan template `whatsapp_template_tenant_donation_new` ada
- Memastikan setting `whatsapp_template_tenant_donation_new_enabled` = '1' (enabled)
- Update value jika berbeda

**Atau jalankan seeder (jika template belum ada):**

```bash
php spark db:seed SettingSeeder
```

**Atau gunakan SQL script (alternatif):**

```bash
# Via MySQL command line
mysql -u [username] -p [database_name] < app/Database/Scripts/ensure_tenant_notification_template.sql

# Atau via phpMyAdmin / database tool, copy-paste isi file:
# app/Database/Scripts/ensure_tenant_notification_template.sql
```

### 2. Verifikasi Template Ada di Database

Cek apakah template sudah ada:

```sql
SELECT `key`, `value`, `scope` FROM settings
WHERE `key` IN (
    'whatsapp_template_tenant_donation_new',
    'whatsapp_template_tenant_donation_new_enabled'
);
```

### 3. Pastikan Setting Enabled = '1'

```sql
UPDATE settings
SET `value` = '1'
WHERE `key` = 'whatsapp_template_tenant_donation_new_enabled';
```

### 4. Clear Cache

```bash
php spark cache:clear
```

### 5. Verifikasi Migration Berhasil

Setelah migration, pastikan tabel `campaign_staff` sudah ada:

```sql
-- Cek tabel campaign_staff
SELECT COUNT(*) as total FROM campaign_staff;
```

Atau via command line:

```bash
php spark db:table campaign_staff
```

### 6. Test Notifikasi

Buat donasi baru dan cek log:

```bash
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep -i "tenant.*notification"
```

## 🔍 Troubleshooting

### Jika notifikasi masih tidak terkirim:

1. **Cek log file** untuk melihat error:

   ```bash
   tail -100 writable/logs/log-$(date +%Y-%m-%d).log
   ```

2. **Cek apakah owner/superadmin punya phone**:

   ```sql
   SELECT id, name, email, phone, role, tenant_id
   FROM users
   WHERE role IN ('superadmin', 'super_admin', 'admin')
   AND phone IS NOT NULL AND phone != '';
   ```

3. **Cek konfigurasi WhatsApp**:

   ```sql
   SELECT `key`, `value` FROM settings
   WHERE `key` IN ('whatsapp_api_url', 'whatsapp_api_token', 'whatsapp_from_number');
   ```

4. **Cek apakah tenant punya owner**:
   ```sql
   SELECT t.id, t.name, t.owner_id, u.id as user_id, u.name as user_name, u.phone
   FROM tenants t
   LEFT JOIN users u ON t.owner_id = u.id
   WHERE t.id = 4;  -- Ganti dengan tenant_id yang bermasalah
   ```
