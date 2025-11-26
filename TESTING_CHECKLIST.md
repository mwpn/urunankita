# 🧪 TESTING CHECKLIST - URUNANKITA

## 📋 Urutan Testing (Dari Dasar ke Kompleks)

---

## ✅ **PHASE 1: Basic Access & Routing** (Priority: HIGHEST)

### 1.1 Main Domain Access
- [ ] Akses `https://urunankita.test`
- [ ] **Expected**: Homepage aggregator tampil
- [ ] **Check**: Tidak ada error, layout dasar muncul

### 1.2 Tenant Subdomain Detection
- [ ] Akses `https://jerry.urunankita.test`
- [ ] **Expected**: 
  - Tenant jerry terdeteksi
  - Session `tenant_slug` = `jerry`
  - Session `is_subdomain` = `true`
- [ ] **Check**: Tidak redirect ke main domain

- [ ] Akses `https://dendenny.urunankita.test`
- [ ] **Expected**: 
  - Tenant dendenny terdeteksi
  - Session `tenant_slug` = `dendenny`
  - Session `is_subdomain` = `true`
- [ ] **Check**: Tidak redirect ke main domain

### 1.3 Invalid Subdomain Handling
- [ ] Akses `https://invalid.urunankita.test`
- [ ] **Expected**: Redirect ke main domain atau 404
- [ ] **Check**: Tidak error fatal

---

## ✅ **PHASE 2: Authentication** (Priority: HIGH)

### 2.1 Login Page Access
- [ ] Akses `/auth/login` dari main domain
- [ ] **Expected**: Login form tampil
- [ ] **Check**: Form bisa diakses

### 2.2 Tenant Owner Login
- [ ] Login di `jerry.urunankita.test`
  - Email: `owner@jerry.test`
  - Password: `admin123`
- [ ] **Expected**: 
  - Login berhasil
  - Redirect ke dashboard tenant
  - Session tenant tersimpan
- [ ] **Check**: Bisa akses dashboard

- [ ] Login di `dendenny.urunankita.test`
  - Email: `owner@dendenny.test`
  - Password: `admin123`
- [ ] **Expected**: Login berhasil
- [ ] **Check**: Bisa akses dashboard

### 2.3 Logout
- [ ] Click logout
- [ ] **Expected**: Session cleared, redirect ke login
- [ ] **Check**: Harus login lagi untuk akses dashboard

---

## ✅ **PHASE 3: Database & Tenant Isolation** (Priority: HIGH)

### 3.1 Tenant Database Connection
- [ ] Buat data di tenant jerry
- [ ] **Check**: Data hanya muncul di jerry, tidak di dendenny
- [ ] **Check**: Data tidak muncul di tenant lain

### 3.2 Multi-Tenant Isolation
- [ ] Login sebagai jerry, buat campaign
- [ ] Login sebagai dendenny
- [ ] **Check**: Campaign jerry tidak terlihat di dendenny
- [ ] **Check**: Hanya campaign dendenny yang muncul

---

## ✅ **PHASE 4: Campaign Features** (Priority: MEDIUM)

### 4.1 Create Campaign
- [ ] Login sebagai tenant owner
- [ ] Create campaign baru:
  - Title, Description
  - Campaign Type (Target Based / Ongoing)
  - Target Amount (jika Target Based)
  - Category
- [ ] **Expected**: Campaign created dengan status `draft` atau `pending_verification`
- [ ] **Check**: Campaign muncul di "My Campaigns"

### 4.2 View Campaign (Public)
- [ ] Lihat campaign di public web (tanpa login)
- [ ] **Expected**: Campaign detail tampil
- [ ] **Check**: 
  - Progress bar (jika Target Based)
  - Donation stats
  - Comments section

### 4.3 Campaign List
- [ ] Main domain: List semua campaign
- [ ] Tenant subdomain: List campaign tenant saja
- [ ] **Check**: Filter bekerja dengan benar

---

## ✅ **PHASE 5: Donation Flow** (Priority: MEDIUM)

### 5.1 Donasi (Public/Guest)
- [ ] Akses campaign detail (tanpa login)
- [ ] Click "Donasi Sekarang"
- [ ] Fill form:
  - Nama
  - Email (optional untuk guest)
  - Amount
  - Anonymous (optional)
  - Message (optional)
- [ ] **Expected**: Donation created dengan status `pending`
- [ ] **Check**: Donation muncul di campaign

### 5.2 Donation Confirmation (Manual)
- [ ] Login sebagai tenant owner
- [ ] View donations untuk campaign
- [ ] Confirm payment manually
- [ ] **Expected**: 
  - Status berubah jadi `paid`
  - Campaign `current_amount` bertambah
  - Campaign progress update (jika Target Based)

---

## ✅ **PHASE 6: Discussion/Comments** (Priority: LOW)

### 6.1 Add Comment (Public)
- [ ] Akses campaign detail (tanpa login)
- [ ] Add comment:
  - Nama (required untuk guest)
  - Comment content
- [ ] **Expected**: Comment muncul di campaign
- [ ] **Check**: Comment ter-sort by date (pinned first)

### 6.2 Reply to Comment
- [ ] Reply ke comment yang sudah ada
- [ ] **Expected**: Reply muncul sebagai nested comment
- [ ] **Check**: `replies_count` bertambah di parent

### 6.3 Like Comment
- [ ] Like comment
- [ ] **Expected**: `likes_count` bertambah
- [ ] **Check**: Tidak bisa like 2x (user/IP)

---

## ✅ **PHASE 7: Advanced Features** (Priority: LOW)

### 7.1 Campaign Updates
- [ ] Login sebagai tenant owner
- [ ] Create campaign update (Laporan Kabar Terbaru)
- [ ] **Expected**: Update muncul di campaign detail
- [ ] **Check**: Update ter-sort by date (pinned first)

### 7.2 Reports/Transparency
- [ ] Akses public report untuk campaign
- [ ] **Expected**: 
  - Financial summary
  - Recent donations (privacy-respecting)
  - Withdrawals info
- [ ] **Check**: Data akurat

### 7.3 Export Data
- [ ] Login sebagai tenant owner
- [ ] Export donations ke CSV
- [ ] **Expected**: File CSV download dengan data benar
- [ ] **Check**: Format Excel-compatible

---

## 🚀 **QUICK START TESTING**

### Step 1: Basic Access (5 menit)
```bash
# Test main domain
1. Buka: https://urunankita.test
2. Check: Homepage muncul

# Test tenant subdomain
3. Buka: https://jerry.urunankita.test
4. Check: Tenant homepage muncul

5. Buka: https://dendenny.urunankita.test
6. Check: Tenant homepage muncul
```

### Step 2: Login (5 menit)
```bash
# Test login jerry
1. Buka: https://jerry.urunankita.test/auth/login
2. Login: owner@jerry.test / admin123
3. Check: Redirect ke dashboard

# Test login dendenny
4. Buka: https://dendenny.urunankita.test/auth/login
5. Login: owner@dendenny.test / admin123
6. Check: Redirect ke dashboard
```

### Step 3: Create Campaign (10 menit)
```bash
1. Login sebagai jerry
2. Create campaign:
   - Title: "Test Campaign"
   - Type: Target Based
   - Target: 1000000
   - Category: Social
3. Check: Campaign created
4. View di public: https://jerry.urunankita.test/campaign/{slug}
```

---

## 📊 **Testing Priority**

1. **🔴 CRITICAL**: Phase 1 & 2 (Basic Access & Login)
2. **🟡 IMPORTANT**: Phase 3 & 4 (Database Isolation & Campaigns)
3. **🟢 NICE TO HAVE**: Phase 5, 6, 7 (Donation, Discussion, Advanced)

---

## 🐛 **Common Issues to Watch**

1. **Subdomain tidak terdeteksi**
   - Check virtual hosts
   - Check `.env` file
   - Check SubdomainFilter

2. **Login gagal**
   - Check user exists di tenant database
   - Check password hash
   - Check session

3. **Database error**
   - Check tenant database connection
   - Check migrations sudah jalan
   - Check tenant_id di session

4. **Tenant isolation broken**
   - Check session tenant_id
   - Check query filter by tenant_id
   - Check TenantFilter

---

## ✅ **Setelah Testing**

Jika semua Phase 1-2 berhasil:
- ✅ Basic functionality OK
- ✅ Ready untuk development lebih lanjut

Jika ada error:
- 📝 Catat error message
- 📝 Screenshot (jika perlu)
- 🔧 Fix berdasarkan error

