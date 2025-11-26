# 🚀 Deployment Guide - UrunanKita.id

## 📋 Pre-Deployment Checklist

### 1. **Environment Configuration**
- [ ] Buat file `.env` di server production
- [ ] Set `CI_ENVIRONMENT = production`
- [ ] Update `app.baseURL` ke domain production (`https://urunankita.id`)
- [ ] Update `app.baseDomain` ke `urunankita.id`
- [ ] Konfigurasi database production
- [ ] Set encryption key
- [ ] Konfigurasi WhatsApp API credentials

### 2. **Server Requirements**
- [ ] PHP 8.1 atau lebih tinggi
- [ ] Extensions: intl, mbstring, mysqli, curl, gd
- [ ] MySQL/MariaDB
- [ ] Composer
- [ ] Web server (Apache/Nginx) dikonfigurasi untuk point ke folder `public/`

### 3. **Database**
- [ ] Database sudah dibuat
- [ ] User database dengan permission yang benar
- [ ] Backup database (jika update)

### 4. **File Permissions**
- [ ] Folder `writable/` harus writable (755 atau 775)
- [ ] File di `writable/` harus writable (644 atau 664)

### 5. **Security**
- [ ] File `.env` tidak boleh accessible dari web
- [ ] Pastikan `.htaccess` di root dan `public/` sudah benar
- [ ] SSL Certificate sudah terpasang (HTTPS)

---

## 🔧 Deployment Steps

### Step 1: Clone/Pull Repository
```bash
# Jika pertama kali
git clone https://github.com/mwpn/urunankita.git
cd urunankita

# Jika update
git pull origin master
```

### Step 2: Install Dependencies
```bash
# Install Composer dependencies (tanpa dev dependencies)
composer install --no-dev --optimize-autoloader

# Install NPM dependencies (jika ada)
npm install --production
```

### Step 3: Setup Environment File
```bash
# Copy env template
cp env .env

# Edit .env dengan konfigurasi production
nano .env
```

**Konfigurasi `.env` untuk Production:**
```env
CI_ENVIRONMENT = production

app.baseURL = https://urunankita.id
app.baseDomain = urunankita.id
app.forceGlobalSecureRequests = true

database.default.hostname = localhost
database.default.database = urunankita_master
database.default.username = your_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.port = 3306

# Generate encryption key
encryption.key = [generate dengan: php spark key:generate]

# WhatsApp API
whatsapp.api_url = https://app.whappi.biz.id/api/qr/rest/send_message
whatsapp.api_token = your_token
whatsapp.from_number = your_number
```

### Step 4: Generate Encryption Key
```bash
php spark key:generate
# Copy key yang dihasilkan ke .env (encryption.key)
```

### Step 5: Run Migrations
```bash
# Run semua migrations
php spark migrate

# Run module migrations (jika ada)
php spark migrate -n App
```

### Step 6: Set File Permissions
```bash
# Set permissions untuk writable folder
chmod -R 775 writable/
chown -R www-data:www-data writable/

# Atau jika menggunakan user lain
chown -R your_user:your_group writable/
```

### Step 7: Optimize Application
```bash
# Optimize CodeIgniter (cache routes, config, dll)
php spark optimize

# Clear cache
php spark cache:clear
```

### Step 8: Build Assets (jika ada)
```bash
# Build CSS/JS jika menggunakan build tools
npm run build
# atau
npm run production
```

### Step 9: Verify Deployment
- [ ] Akses `https://urunankita.id` - harus bisa diakses
- [ ] Cek subdomain tenant: `https://{tenant}.urunankita.id`
- [ ] Test login admin
- [ ] Test form pengajuan penggalang dana
- [ ] Test form pengajuan sponsorship
- [ ] Cek error logs di `writable/logs/`

---

## 🔄 Update Deployment (Untuk Update Selanjutnya)

```bash
# 1. Pull latest code
git pull origin master

# 2. Install/update dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations (jika ada)
php spark migrate

# 4. Clear cache
php spark cache:clear

# 5. Optimize
php spark optimize
```

---

## 🐛 Troubleshooting

### Error: "Database connection failed"
- Cek konfigurasi database di `.env`
- Pastikan database user memiliki permission yang benar
- Cek firewall/network

### Error: "Permission denied" pada writable/
```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

### Error: "Class not found"
```bash
composer dump-autoload
php spark optimize
```

### Error: "404 Not Found"
- Pastikan web server point ke folder `public/`
- Cek `.htaccess` di `public/`
- Enable mod_rewrite (Apache) atau konfigurasi Nginx dengan benar

### Error: "Encryption key not set"
```bash
php spark key:generate
# Copy key ke .env
```

---

## 📝 Web Server Configuration

### Apache (.htaccess sudah ada di public/)
Pastikan `mod_rewrite` enabled:
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

### Nginx Configuration

**File konfigurasi lengkap ada di: `nginx.conf.example`**

```nginx
server {
    listen 443 ssl http2;
    server_name urunankita.id *.urunankita.id;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    
    # PENTING: Document root harus point ke folder public/
    root /www/wwwroot/urunankita.id/urunankita/public;
    index index.php index.html;
    
    # CodeIgniter 4 Routing - PENTING!
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP Handler
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/tmp/php-cgi-83.sock;  # Sesuaikan dengan PHP version
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security - Deny access to .env and hidden files
    location ~ /\. {
        deny all;
    }
    location ~ /\.env {
        deny all;
    }
}
```

**Catatan Penting:**
- `root` harus point ke folder `public/`, bukan root project
- `try_files` harus ada `/index.php?$query_string` untuk routing CodeIgniter
- Pastikan PHP-FPM socket path sesuai dengan versi PHP Anda
- Untuk BT Panel, biasanya socket di: `/tmp/php-cgi-XX.sock` (XX = versi PHP)

---

## 🔐 Security Checklist

- [ ] `.env` file tidak accessible dari web
- [ ] `writable/` folder tidak accessible dari web (kecuali uploads)
- [ ] SSL Certificate terpasang dan valid
- [ ] Database password kuat
- [ ] Encryption key unik dan aman
- [ ] File permissions benar (tidak terlalu permissive)
- [ ] Error display disabled di production
- [ ] Debug mode disabled

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. Error logs: `writable/logs/log-YYYY-MM-DD.log`
2. Web server error logs
3. PHP error logs

---

**Last Updated:** 2025-01-20

