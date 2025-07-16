# ✅ Deployment Checklist - Quality Gates Dashboard

## 📦 File Ready untuk Upload ke cPanel

Folder `qg-cpanel` berisi **17 file** dan **2 folder** yang siap di-upload ke:
**https://dashboardqg.web.bps.go.id/main**

### ✅ File Utama (5 file)
- [x] `main.php` - Entry point (950B)
- [x] `index.php` - Dashboard (41KB) 
- [x] `api.php` - API endpoints (44KB)
- [x] `monitoring.php` - Monitoring page (74KB)
- [x] `profile.php` - Profile page (20KB)

### ✅ File SSO (5 file)
- [x] `sso_config.php` - Config (9KB) **[SUDAH DIUBAH ke PRODUCTION URL]**
- [x] `sso_login.php` - Login (7KB)
- [x] `sso_logout.php` - Logout (706B)
- [x] `sso_callback.php` - Callback (6KB)
- [x] `sso_integration.php` - Integration (10KB)

### ✅ File Assets & Config (5 file)
- [x] `navbar.css` - Styling (4KB)
- [x] `.htaccess` - Routing & Security (1KB) **[BARU DIBUAT]**
- [x] `composer.json` - Dependencies (69B)
- [x] `composer.lock` - Lock file (29KB)
- [x] `README_DEPLOYMENT.md` - Panduan (3KB) **[BARU DIBUAT]**

### ✅ Folder Dependencies (2 folder)
- [x] `vendor/` - PHP Libraries (455 files)
- [x] `assets/` - Static assets (kosong/akan dibuat)

## 🔧 Konfigurasi yang Sudah Diubah

### ✅ URL Production
```php
// BEFORE (localhost):
define('SSO_REDIRECT_URI', 'http://localhost/qg/sso_callback.php');

// AFTER (production):
define('SSO_REDIRECT_URI', 'https://dashboardqg.web.bps.go.id/main/sso_callback.php');
```

### ✅ Entry Point Routing
- Root URL → `main.php`
- `main.php` → Redirect ke dashboard atau SSO login

## 🚀 Langkah Upload ke cPanel

### 1. Akses cPanel
- Login: `dashboardqg.web.bps.go.id/cpanel`
- Buka: **File Manager**

### 2. Navigasi Folder
- Masuk: `public_html/`
- Buat: folder `main/` (jika belum ada)
- Masuk: `public_html/main/`

### 3. Upload Files
- Select semua file di folder `qg-cpanel`
- Upload & Extract (jika zip)
- Atau upload satu per satu

### 4. Set Permissions
```
Folder main/: 755
File *.php: 644
File .htaccess: 644
Folder vendor/: 755
```

## 🌐 URL Testing

Setelah upload, test URLs berikut:
- [ ] https://dashboardqg.web.bps.go.id/main/ (Entry point)
- [ ] https://dashboardqg.web.bps.go.id/main/index.php (Dashboard)
- [ ] https://dashboardqg.web.bps.go.id/main/sso_login.php (SSO Login)
- [ ] https://dashboardqg.web.bps.go.id/main/api.php (API)

## ⚠️ Important Notes

1. **Jangan upload folder `.git`** - tidak diperlukan
2. **File vendor/ harus diupload** - berisi dependencies
3. **File .htaccess wajib ada** - untuk routing
4. **SSO config sudah production-ready** - siap pakai

## 📞 Jika Ada Masalah

1. Cek PHP version >= 7.4
2. Pastikan extension: curl, json, openssl
3. Cek error logs di cPanel
4. Verifikasi permissions file

---
**Total Size:** ~200MB (karena vendor folder)
**Ready to Deploy:** ✅ YA
**Production URL:** https://dashboardqg.web.bps.go.id/main 