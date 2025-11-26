# Migration Checklist

Daftar migration yang perlu dijalankan nanti dengan command: `php spark migrate`

## Central Database Migrations (app/Database/Migrations)

✅ **Core Tables:**

1. `2025-10-30-000001_CreateUsers` - Users table (central)
2. `2025-10-30-000002_CreateTenants` - Tenants table
3. `2025-10-30-000003_CreatePlans` - Plans table
4. `2025-10-30-000004_CreateSubscriptions` - Subscriptions table
5. `2025-10-30-000005_CreateInvoices` - Invoices table
6. `2025-10-30-000006_CreateRoles` - Roles table
7. `2025-10-30-000007_CreatePermissions` - Permissions table
8. `2025-11-02-010900_AddBankAccountsToTenants` - Add bank_accounts field to tenants table

## Module Migrations

### Notification Module

✅ `2025-11-01-094600_CreateNotificationLogs` - Notification logs tracking

### File Module

✅ `2025-11-01-100300_CreateFiles` - File storage tracking dengan tenant isolation

### ActivityLog Module

✅ `2025-11-01-103100_CreateActivityLogs` - Activity logs untuk audit trail

### Setting Module

✅ `2025-11-01-103200_CreateSettings` - Settings dengan multi-level (global/tenant/user)

### Helpdesk Module

✅ `2025-11-02-013400_CreateTicketCategories` - Kategori ticket dengan default data
✅ `2025-11-02-013500_CreateTickets` - Tickets table dengan support attachments
✅ `2025-11-02-013600_CreateTicketReplies` - Replies untuk komunikasi ticket

### Report Module

✅ `2025-11-02-014000_CreateReports` - Reports table untuk laporan transparansi

### Discussion Module

✅ `2025-11-02-014200_CreateComments` - Comments table untuk diskusi per urunan (dengan nested replies & likes)

### Tenant Module (Jalankan per tenant setelah tenant dibuat)

⚠️ **Note:** Migrations ini dijalankan via `php spark tenant:migrate <slug>` setelah tenant dibuat

1. `2025-10-30-000101_CreateTenantUsers` - Users table per tenant
2. `2025-10-30-000102_CreateTenantRoles` - Roles table per tenant
3. `2025-10-30-000103_CreateTenantPermissions` - Permissions table per tenant
4. `2025-10-30-000104_CreateTenantAuditLogs` - Audit logs table per tenant

## Cara Menjalankan

### 1. Central Database Migrations

```bash
php spark migrate
```

Ini akan menjalankan semua migrations di `app/Database/Migrations` dan semua module migrations.

### 2. Tenant-Specific Migrations

Setelah membuat tenant baru:

```bash
php spark tenant:create <slug>
php spark tenant:migrate <slug>
```

## Urutan Eksekusi

1. ✅ Jalankan central migrations dulu (users, tenants, plans, dll)
2. ✅ Jalankan module migrations (notification, file, activity_log, setting)
3. ✅ Seed data awal (plans, superadmin) - `php spark db:seed`
4. ✅ Buat tenant pertama - `php spark tenant:create <slug>`
5. ✅ Jalankan tenant migrations - `php spark tenant:migrate <slug>`

## Status

- [x] Migrations sudah dibuat
- [ ] Migrations belum dijalankan (akan dijalankan nanti sekaligus)
- [ ] Seeders perlu dijalankan setelah migrations

## Catatan

- Semua migration menggunakan timestamp untuk ordering
- Central migrations dijalankan sekali untuk semua tenant
- Tenant migrations dijalankan per tenant database
- Migration akan auto-detect namespace dari module
