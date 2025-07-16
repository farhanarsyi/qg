# Panduan Deployment Quality Gates Dashboard ke cPanel

## 📋 Daftar File yang Sudah Dikonfigurasi

Folder ini berisi semua file yang sudah dikonfigurasi untuk deployment ke production server dengan URL:
**https://dashboardqg.web.bps.go.id/main**

### File Utama:
- `main.php` - Entry point utama aplikasi  
- `index.php` - Dashboard utama
- `api.php` - API endpoints
- `monitoring.php` - Halaman monitoring
- `profile.php` - Halaman profil user
- `navbar.css` - Styling navigasi

### File SSO:
- `sso_config.php` - Konfigurasi SSO (sudah diubah ke production URL)
- `sso_login.php` - Halaman login SSO
- `sso_logout.php` - Proses logout SSO
- `sso_callback.php` - Callback handler SSO
- `sso_integration.php` - Library integrasi SSO

### File Konfigurasi:
- `.htaccess` - Routing dan security headers
- `composer.json` - Dependencies PHP
- `composer.lock` - Lock file dependencies
- `vendor/` - Library dependencies

## 🚀 Langkah-langkah Deployment

### 1. Upload File ke cPanel
1. Login ke cPanel hosting `dashboardqg.web.bps.go.id`
2. Buka File Manager
3. Navigasi ke folder `public_html/main/` (buat folder main jika belum ada)
4. Upload semua file dan folder dari `qg-cpanel` ke dalam folder `main/`

### 2. Set Permissions
Pastikan permissions berikut:
- Folder `main/`: 755
- Semua file `.php`: 644  
- File `.htaccess`: 644
- Folder `vendor/`: 755

### 3. Konfigurasi Database (jika diperlukan)
Jika aplikasi menggunakan database, sesuaikan konfigurasi database di file yang relevan.

### 4. Verifikasi SSO Configuration
File `sso_config.php` sudah dikonfigurasi dengan:
```php
define('SSO_REDIRECT_URI', 'https://dashboardqg.web.bps.go.id/main/sso_callback.php');
```

Pastikan konfigurasi SSO di server Keycloak BPS juga mengenali URL redirect ini.

## 🔧 Konfigurasi yang Sudah Diubah

### URL Configuration
- **Dari:** `http://localhost/qg/sso_callback.php`
- **Ke:** `https://dashboardqg.web.bps.go.id/main/sso_callback.php`

### Routing Configuration  
- Root URL `https://dashboardqg.web.bps.go.id/main/` akan diarahkan ke `main.php`
- `main.php` akan redirect berdasarkan status login SSO

## 🌐 URL Access

Setelah deployment berhasil:
- **URL Utama:** https://dashboardqg.web.bps.go.id/main/
- **Dashboard:** https://dashboardqg.web.bps.go.id/main/index.php
- **Monitoring:** https://dashboardqg.web.bps.go.id/main/monitoring.php
- **Profile:** https://dashboardqg.web.bps.go.id/main/profile.php
- **API:** https://dashboardqg.web.bps.go.id/main/api.php

## 🛡️ Security Features

File `.htaccess` sudah dikonfigurasi dengan:
- Security headers (X-Frame-Options, XSS Protection, dll)
- Block akses ke file sensitif (vendor/, composer files)
- Compression untuk performance
- Browser caching optimization

## 🔍 Testing

Setelah upload, test:
1. Akses https://dashboardqg.web.bps.go.id/main/
2. Pastikan redirect ke SSO login berjalan
3. Test login dengan akun BPS
4. Verifikasi callback SSO berfungsi
5. Test semua fitur dashboard

## 📞 Support

Jika ada masalah deployment:
1. Cek error logs di cPanel
2. Pastikan PHP version >= 7.4
3. Pastikan semua extensions PHP terinstall (curl, json, openssl)
4. Verifikasi permissions file/folder

## 📝 Notes

- Folder `vendor/` berisi library dependencies yang diperlukan
- Jangan hapus file `.htaccess` karena berisi konfigurasi penting  
- File `composer.json` dan `composer.lock` diperlukan jika ingin menjalankan `composer install` di server
- SSO client credentials sudah dikonfigurasi untuk environment production BPS 