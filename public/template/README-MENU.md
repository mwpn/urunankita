# Struktur Menu Platform Urunankita

## Konsep Platform

Platform crowdfunding profesional dengan fitur lengkap:
- **Platform Utama (urunankita.test)**: Admin bisa menggalang dana dengan fitur lengkap
- **Subdomain Penggalang ({penggalang}.urunankita.test)**: Admin mendaftarkan penggalang lain yang bisa menggalang dana di subdomain mereka
- **Pendaftaran oleh Admin**: Admin yang mendaftarkan penggalang dana langsung dengan user & password
- **Rekening Mandiri**: Setiap penggalang (termasuk admin) menggunakan rekening mereka sendiri
- **Metode Urunan**: Targeted (target tertentu) atau Open (tanpa target)
- **Laporan Detail**: Laporan penggunaan dana secara detail untuk setiap urunan
- **Konten Web**: Admin kelola konten web utama, penggalang kelola konten subdomain mereka
- **Akses Semua User**: Semua user bisa lihat semua urunan, laporan, dan riwayat

---

## Struktur Menu

### Dashboard (Semua User)
- Overview statistik platform

### Urunan Kita (Semua User)
- **Buat Urunan Baru**: Buat urunan baru (targeted/open)
- **Urunan Saya**: Daftar urunan yang dibuat user
- **Donasi Masuk**: Donasi yang masuk ke urunan user
- **Laporan Penggunaan Dana**: Laporan detail penggunaan dana untuk urunan user

### Penggalang Dana (Admin Only)
- **Tambah Penggalang Baru**: Admin mendaftarkan penggalang baru dengan user & password & subdomain
- **Daftar Penggalang**: Lihat semua penggalang yang sudah terdaftar

### Semua Urunan (Semua User)
- **Semua Urunan**: Lihat semua urunan dari platform utama & dari semua penggalang di subdomain
- **Laporan Semua Urunan**: Laporan dan analitik semua urunan dari semua penggalang
- **Riwayat & Log**: Riwayat aktivitas dan log sistem untuk semua urunan

### Konten Web (Semua User)
- **Banner & Slider**: Kelola banner dan slider (admin: web utama, penggalang: subdomain mereka)
- **Halaman**: Kelola halaman statis (About, FAQ, Terms, dll)
- **Artikel/Blog**: Kelola artikel dan blog (admin: web utama, penggalang: subdomain mereka)

### Pengaturan
- **Pengaturan Umum** (Admin Only): Konfigurasi platform
- **Metode Pembayaran** (Semua User): Kelola metode pembayaran untuk donasi
- **Daftar Rekening** (Semua User): Kelola rekening untuk menerima donasi

### Profil (Semua User)
- **Overview**: Overview profil user
- **Settings**: Pengaturan profil

### Bantuan (Semua User)
- **Support**: Support dan bantuan

---

## Alur Kerja Platform

### 1. Admin Menggalang Dana (Platform Utama)
```
Admin Login → Urunan Kita → Buat Urunan Baru (Targeted/Open) 
→ Set Metode Pembayaran → Pakai Rekening Platform 
→ Donasi Masuk → Buat Laporan Penggunaan Dana
```

### 2. Pendaftaran Penggalang Dana (dengan Subdomain)
```
Admin → Penggalang Dana → Tambah Penggalang Baru 
→ Set User & Password → Set Subdomain (jerry.urunankita.test) → Berikan Akses
```

### 3. Penggalang Membuat Urunan (di Subdomain)
```
Penggalang Login → Urunan Kita → Buat Urunan Baru (Targeted/Open) 
→ Set Metode Pembayaran → Pakai Rekening Mereka 
→ Donasi Masuk → Buat Laporan
```

### 4. Semua User Akses Semua Data
```
Semua User → Semua Urunan → Lihat semua urunan dari platform & penggalang
Semua User → Laporan Semua Urunan → Lihat laporan semua penggalang
Semua User → Riwayat & Log → Lihat aktivitas semua penggalang
```

### 5. Laporan Penggunaan Dana
```
User → Urunan Kita → Laporan Penggunaan Dana → Buat Laporan 
→ Input Detail Penggunaan → Upload Bukti → Publish
```

---

## Fitur Khusus

### 1. Metode Urunan
- **Targeted**: Urunan dengan target tertentu (contoh: Rp 10.000.000)
- **Open**: Urunan tanpa target, bisa menerima donasi tanpa batas

### 2. Laporan Penggunaan Dana
- Detail penggunaan dana per item
- Upload bukti penggunaan
- Transparansi untuk donatur

### 3. Metode Pembayaran
- Bank Transfer (BCA, Mandiri, BNI, dll)
- E-Wallet (OVO, GoPay, DANA, dll)
- Virtual Account
- QR Code

### 4. Konten Web
- **Admin**: Kelola konten web utama urunankita.test
- **Penggalang**: Kelola konten subdomain mereka (jerry.urunankita.test)

---

## Cara Set User Role

### Di JavaScript (untuk testing):
```javascript
// Set sebagai Admin
window.userRole = 'admin';
localStorage.setItem('userRole', 'admin');

// Set sebagai Penggalang Dana
window.userRole = 'penggalang_dana';
localStorage.setItem('userRole', 'penggalang_dana');

// Refresh sidebar
if (window.filterSidebarByRole) {
  window.filterSidebarByRole(window.userRole);
}
```

### Dari Backend (PHP/Laravel):
```php
// Di PHP
<script>
  window.userRole = '<?php echo $user->role; ?>';
</script>

// Di Laravel Blade
<script>
  window.userRole = '{{ Auth::user()->role }}';
</script>
```

---

## Struktur Folder yang Disarankan

```
ci4/
├── dashboard.html
├── urunan/
│   ├── create.html
│   ├── list.html
│   ├── donasi.html
│   ├── laporan.html
│   ├── all.html
│   ├── laporan-semua.html
│   └── riwayat.html
├── admin/
│   ├── penggalang-dana/
│   │   ├── add.html
│   │   └── list.html
│   └── settings/
│       └── general.html
├── content/
│   ├── banner.html
│   ├── pages.html
│   └── articles.html
├── settings/
│   ├── payment.html
│   └── rekening.html
├── profile/
│   ├── overview.html
│   └── settings.html
└── helpdesk/
    └── support.html
```

---

## Catatan Penting

1. **Pendaftaran Penggalang**: Admin yang mendaftarkan penggalang langsung, tidak ada pendaftaran mandiri
2. **User & Password**: Admin membuat user & password untuk penggalang saat mendaftarkan
3. **Subdomain**: Setiap penggalang dapat subdomain unik saat didaftarkan (contoh: jerry.urunankita.test)
4. **Rekening**: Penggalang menggunakan rekening mereka sendiri, platform punya rekening sendiri untuk urunan admin
5. **Urunan**: Tidak perlu verifikasi, langsung aktif setelah dibuat (baik admin maupun penggalang)
6. **Laporan**: Setiap urunan bisa dibuat laporan penggunaan dana secara detail
7. **Metode Pembayaran**: Bisa dikelola oleh semua user untuk urunan mereka
8. **Konten Web**: Admin kelola konten web utama, penggalang kelola konten subdomain mereka
9. **Akses Semua User**: Semua user (admin & penggalang) bisa lihat semua urunan, laporan, dan riwayat
