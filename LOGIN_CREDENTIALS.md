# Kredensial Login

## Super Admin (Central Database)

Untuk mengakses Admin Dashboard:

- **Email:** `admin@root.test`
- **Password:** `admin123`
- **Role:** `superadmin`
- **URL Login:** `https://urunankita.test/auth/login`
- **URL Dashboard:** `https://urunankita.test/admin`

## Tenant Users

### Tenant: jerry
**Database:** `tenant_jerry`

- **Owner:**
  - **Email:** `owner@jerry.test`
  - **Password:** `admin123`
  - **Role:** `tenant_owner`

- **Manager:**
  - **Email:** `manager1@test.com`
  - **Password:** `admin123`
  - **Role:** `manager`

- **Staff:**
  - **Email:** `staff1@test.com`
  - **Password:** `admin123`
  - **Role:** `staff`

**URL Login:** `https://jerry.urunankita.test/auth/login` atau `https://urunankita.test/auth/login`  
**URL Dashboard:** `https://urunankita.test/tenant/jerry/dashboard`

### Tenant: dendenny
**Database:** `tenant_dendenny`

- **Owner:**
  - **Email:** `owner@dendenny.test`
  - **Password:** `admin123`
  - **Role:** `tenant_owner`

- **Manager:**
  - **Email:** `manager2@test.com`
  - **Password:** `admin123`
  - **Role:** `manager`

- **Staff:**
  - **Email:** `staff2@test.com`
  - **Password:** `admin123`
  - **Role:** `staff`

**URL Login:** `https://dendenny.urunankita.test/auth/login` atau `https://urunankita.test/auth/login`  
**URL Dashboard:** `https://urunankita.test/tenant/dendenny/dashboard`

## Catatan

1. **Super Admin** dibuat oleh `SuperAdminSeeder` di database central (`urunankita_master`)
2. **Tenant Owner** dibuat otomatis saat menjalankan `php spark tenant:create {slug}`
3. **Manager & Staff** dibuat oleh `TenantSampleDataSeeder` jika dijalankan
4. Semua password default adalah: `admin123`
5. Pastikan sudah menjalankan:
   - `php spark db:seed SuperAdminSeeder` (untuk super admin)
   - `php spark tenant:create jerry` (sudah dibuat)
   - `php spark tenant:create dendenny` (sudah dibuat)
   - `php spark db:seed TenantSampleDataSeeder` (optional, untuk data sample + manager/staff)

