# 🌐 Virtual Hosts Setup Guide - Laragon

## Setup Virtual Hosts di Laragon

### 1. **Main Domain (Aggregator)**

**Menu**: Laragon → Menu → Tools → Quick add → Domain

- **Domain**: `urunankita.test`
- **Path**: `C:\laragon\www\urunankita\public`

**Fungsi**: Menampilkan semua urunan dari semua tenant (aggregator)

---

### 2. **Tenant jerry**

**Menu**: Laragon → Menu → Tools → Quick add → Domain

- **Domain**: `jerry.urunankita.test`
- **Path**: `C:\laragon\www\urunankita\public`

**Fungsi**: Web khusus tenant jerry

---

### 3. **Tenant dendenny**

**Menu**: Laragon → Menu → Tools → Quick add → Domain

- **Domain**: `dendenny.urunankita.test`
- **Path**: `C:\laragon\www\urunankita\public`

**Fungsi**: Web khusus tenant dendenny

---

## ✅ Setelah Setup

1. **Restart Laragon** (atau restart Apache)
2. **Test akses**:
   - Main: `https://urunankita.test`
   - Jerry: `https://jerry.urunankita.test`
   - Dendenny: `https://dendenny.urunankita.test`

---

## 📋 Alternatif: Wildcard Subdomain

Jika ingin setup sekali untuk semua tenant, bisa gunakan wildcard:

**Domain**: `*.urunankita.test`  
**Path**: `C:\laragon\www\urunankita\public`

**Note**: Pastikan Apache mendukung wildcard subdomain di Laragon.

---

## 🔍 Troubleshooting

### Subdomain tidak terdeteksi?
1. Pastikan format: `{tenant}.urunankita.test`
2. Cek `.env` file, tambahkan:
   ```env
   app.baseDomain = urunankita.id
   ```
3. Clear browser cache
4. Restart Laragon

### Error 404?
1. Pastikan path ke `public` folder benar
2. Cek Apache error log
3. Pastikan `.htaccess` ada di folder `public`

