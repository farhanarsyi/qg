# 🔧 Troubleshooting Guide - Quality Gates Dashboard

## 🚨 Masalah yang Dilaporkan: Halaman Blank setelah Login SSO

### 📊 Status Perbaikan
✅ **Error handling ditambahkan** di semua file utama  
✅ **Debug tools dibuat** untuk membantu troubleshooting  
✅ **Logging ditambahkan** untuk tracking error  
✅ **Bug di sso_callback.php diperbaiki** (variabel tidak terdefinisi)

---

## 🔍 Langkah Troubleshooting

### 1. Test Akses File Debug
Setelah upload file terbaru, akses URL berikut untuk debugging:

**🧪 Test Session SSO:**
```
https://dashboardqg.web.bps.go.id/main/test_session.php
```

**🐛 Debug Full Info:**
```
https://dashboardqg.web.bps.go.id/main/debug.php?show=debug
```

### 2. Cek Error Logs
Di cPanel, buka **Error Logs** dan cari pesan error terbaru dari:
- `main.php`
- `sso_callback.php` 
- `index.php`

### 3. Test Step-by-step
1. **Akses halaman utama:**
   ```
   https://dashboardqg.web.bps.go.id/main/
   ```
   - Seharusnya redirect ke `sso_login.php`

2. **Login SSO:**
   - Masuk dengan akun BPS
   - Setelah login, seharusnya redirect ke `sso_callback.php`

3. **Cek callback:**
   - Jika error, akan ditampilkan error page yang informatif
   - Jika sukses, redirect ke `index.php`

4. **Test session:**
   ```
   https://dashboardqg.web.bps.go.id/main/test_session.php
   ```

---

## 🛠️ File yang Sudah Diperbaiki

### ✅ sso_callback.php
- **Fixed:** Bug variabel `$unit_kerja` tidak terdefinisi
- **Added:** Error reporting dan detailed error page
- **Added:** Comprehensive logging

### ✅ main.php  
- **Added:** Try-catch error handling
- **Added:** Detailed logging
- **Added:** User-friendly error page

### ✅ index.php
- **Added:** Try-catch error handling  
- **Added:** Error reporting
- **Added:** Debug logging

### ✅ New Debug Files
- **debug_helper.php** - Helper functions untuk debugging
- **debug.php** - Halaman debug yang bisa diakses
- **test_session.php** - Test session SSO secara detail

---

## 🔧 Kemungkinan Penyebab Masalah

### 1. **PHP Extensions Missing**
Pastikan extensions berikut terinstall:
- `curl` - untuk koneksi SSO
- `json` - untuk parsing data
- `openssl` - untuk SSL/HTTPS
- `session` - untuk session handling

### 2. **Session Configuration**
Di cPanel, cek PHP configuration:
```ini
session.auto_start = Off
session.use_cookies = On
session.cookie_httponly = On
```

### 3. **Memory Limit**
Pastikan PHP memory limit cukup:
```ini
memory_limit = 256M
```

### 4. **File Permissions**
Pastikan permissions berikut:
- Folder `/main/`: **755**
- File `*.php`: **644**
- File `.htaccess`: **644**

---

## 🆘 Quick Fix Actions

### Jika Masih Blank:

1. **Upload file terbaru** dari `qg-cpanel` (yang sudah diperbaiki)

2. **Akses test session:**
   ```
   https://dashboardqg.web.bps.go.id/main/test_session.php
   ```

3. **Cek debug info:**
   ```
   https://dashboardqg.web.bps.go.id/main/debug.php?show=debug
   ```

4. **Reset session:**
   ```
   https://dashboardqg.web.bps.go.id/main/sso_logout.php
   ```

5. **Login ulang:**
   ```
   https://dashboardqg.web.bps.go.id/main/sso_login.php
   ```

---

## 📋 Checklist Upload File Terbaru

Pastikan file berikut sudah diupload dengan versi terbaru:

- [ ] `sso_callback.php` *(DIPERBAIKI - bug variabel)*
- [ ] `main.php` *(DITAMBAHKAN - error handling)*
- [ ] `index.php` *(DITAMBAHKAN - error handling)*
- [ ] `debug_helper.php` *(BARU)*
- [ ] `debug.php` *(BARU)*
- [ ] `test_session.php` *(BARU)*
- [ ] `.htaccess` *(DIPERBARUI - allow debug files)*

---

## 📞 Support Lanjutan

Jika masih ada masalah setelah langkah di atas:

1. **Share hasil** dari `test_session.php`
2. **Share error logs** dari cPanel
3. **Share screenshot** error page
4. **Cek PHP version** (minimal 7.4)

---

## 🎯 Expected Result

Setelah perbaikan, flow yang benar:

1. `main/` → redirect to `sso_login.php`
2. SSO login → redirect to `sso_callback.php`
3. Callback sukses → redirect to `index.php`
4. Dashboard loading → show dashboard dengan data user

**Tidak ada lagi halaman blank!** ✅ 