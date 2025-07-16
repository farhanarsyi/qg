# 🚀 Quick Start Guide - Quality Gates Refactored

## ✅ Berhasil! Refactoring Selesai

Folder `qg-final` sudah berisi versi yang telah direfactor dengan struktur modular dan code yang lebih bersih.

## 📁 Struktur Baru

```
qg-final/
├── config/           # ✨ Konfigurasi modular
├── includes/         # ✨ Classes & helpers
├── assets/           # ✨ CSS & static files  
├── docs/             # ✨ Documentation
├── [main files]      # File utama aplikasi
```

## ⚡ Quick Setup (Pilih salah satu)

### Option 1: Replace qg-cpanel (Production)
```bash
# Backup folder lama
mv qg-cpanel qg-cpanel-backup

# Rename folder baru
mv qg-final qg-cpanel

# Upload ke cPanel root directory
```

### Option 2: Test dulu di qg-final
```bash
# Biarkan qg-cpanel tetap jalan
# Test di: dashboardqg.web.bps.go.id/qg-final
# Atau: localhost/qg/qg-final
```

## 🔧 Configuration

### Auto Environment Detection ✅
- **Production**: `dashboardqg.web.bps.go.id` → config otomatis
- **Local**: `localhost` → config localhost otomatis
- **Manual override**: Edit `config/environment.php`

### SSO Configuration ✅
- Production: `https://dashboardqg.web.bps.go.id/sso_callback.php`
- Local: `http://localhost/qg/qg-final/sso_callback.php`
- Credentials: Sama seperti qg-cpanel

## 🎯 Test Files

### Test SSO (New Files)
1. **Login**: `sso_login_new.php`
2. **Callback**: `sso_callback_new.php`

### Compare dengan yang lama:
- File lama: `sso_login.php`, `sso_callback.php`
- File baru: `sso_login_new.php`, `sso_callback_new.php`

## 🔍 Quick Test

1. **Akses folder**: 
   - Production: `https://dashboardqg.web.bps.go.id/qg-final/`
   - Local: `http://localhost/qg/qg-final/`

2. **Test SSO**:
   - Klik redirect otomatis ke `sso_login_new.php`
   - Login dengan akun BPS
   - Should redirect ke dashboard

3. **Check logs**: `logs/app.log` (auto-created)

## 🆘 Troubleshooting

### ❌ Class not found
```bash
# Check folder permissions
chmod 755 qg-final/
chmod 755 qg-final/includes/
chmod 755 qg-final/config/
```

### ❌ Logs error
```bash
# Create logs folder manually
mkdir logs
chmod 777 logs
```

### ❌ Environment issues
```php
// Force environment in config/environment.php
define('QG_ENVIRONMENT', 'production'); // or 'local'
```

## 🔄 Migration Step-by-Step

### Step 1: Test New Structure
- Access `qg-final/sso_login_new.php`
- Verify SSO works
- Check dashboard loads

### Step 2: Update Main Files (Optional)
Update existing files with new pattern:
```php
// OLD way
require_once 'sso_config.php';

// NEW way  
require_once 'config/bootstrap.php';
$ssoManager = new SSOManager();
```

### Step 3: Go Live
When ready, rename folders:
```bash
mv qg-cpanel qg-cpanel-old
mv qg-final qg-cpanel
```

## 📊 Benefits Achieved

✅ **Modular**: Config terpisah per environment  
✅ **Secure**: Better security headers & session  
✅ **Clean**: OOP structure dengan classes  
✅ **Maintainable**: Easier to modify & debug  
✅ **Documented**: Full documentation & comments  
✅ **Logging**: Centralized logging system  
✅ **Error Handling**: User-friendly error pages  

## 🎉 Next Steps

1. **Test thoroughly** in qg-final
2. **Migrate main files** using new pattern (optional)
3. **Go live** when confident
4. **Monitor logs** for any issues
5. **Enjoy cleaner codebase!** 🎊

---

## 📞 Need Help?

- Check `README_REFACTORING.md` for detailed docs
- Check `logs/app.log` for debug info
- Compare new vs old files for patterns

**Happy coding! 🚀** 