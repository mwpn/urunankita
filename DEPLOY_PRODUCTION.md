# 🚀 Deployment Checklist untuk Produksi

## ⚠️ PENTING: Setelah Pull di Produksi

### 1. Pastikan Template Notifikasi Enabled (WAJIB!)

**Cara TERMUDAH - Gunakan command ini:**

```bash
php spark ensure:notification-templates
```

Command ini akan:

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
```

Atau jika ingin insert manual, jalankan SQL script:

```bash
mysql -u [username] -p [database_name] < app/Database/Scripts/ensure_tenant_notification_template.sql
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

### 5. Test Notifikasi

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
