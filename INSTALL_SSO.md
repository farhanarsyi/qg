# 📦 Petunjuk Instalasi SSO BPS 
**Sesuai Chat Farhan & Fachrunisa**

## 🎯 Ringkasan Chat
**Fachrunisa:** "udah ngikutin panduan ini kah? https://git.bps.go.id/jkd-repo/sso-php"  
**Fachrunisa:** "tapi harus install dulu package nya, pake composer require irsadarief/jkd-sso"  
**Farhan:** "ini instal di cpanel web.bps nya ya?"  
**Fachrunisa:** "kalo di cpanel nanti waktu kamu dah ada composer.jsonnya biasanya bakalan ngikuti"

## 🚀 Langkah Instalasi

### 1️⃣ Install Composer (Jika Belum Ada)
**Untuk Development Lokal:**
```bash
# Download dan install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

**Untuk cPanel BPS:**
- cPanel BPS biasanya sudah ada Composer built-in
- Tidak perlu install manual

### 2️⃣ Install Package SSO BPS
**Di Terminal (Lokal):**
```bash
# Masuk ke folder project
cd /path/to/qg

# Install package SSO BPS (sesuai saran Fachrunisa)
composer require irsadarief/jkd-sso
```

**Di cPanel:**
- Upload file `composer.json` yang sudah dibuat
- cPanel akan otomatis download dependency
- Atau gunakan Terminal di cPanel

### 3️⃣ Verifikasi Instalasi
Setelah install, struktur folder harus seperti ini:
```
qg/
├── vendor/               ← Folder baru dari Composer
│   ├── autoload.php     ← File autoload
│   └── irsadarief/      ← Package SSO BPS
├── composer.json        ← Konfigurasi package
├── sso-auth.php         ← File SSO yang sudah dibuat
├── sso-callback.php     
├── auth-check.php       
└── ...
```

### 4️⃣ Test Instalasi
1. **Login ke aplikasi** melalui SSO BPS
2. **Akses debug page**: `https://dashboardqg.web.bps.go.id/sso-debug.php`
3. **Cek data satuan kerja** - apakah pusat/provinsi/kabupaten

## ✅ Checklist
- [ ] Composer terinstall
- [ ] Package `irsadarief/jkd-sso` terinstall
- [ ] Folder `vendor/` ada dan berisi autoload.php
- [ ] File SSO sudah diupdate dengan namespace yang benar
- [ ] Login SSO berhasil
- [ ] Debug page menampilkan data satuan kerja

## 🐛 Troubleshooting

### Error: "Class not found"
```php
// Pastikan file menggunakan autoload yang benar:
require_once 'vendor/autoload.php';
use IrsadArief\JKD\SSO\Client\Provider\Keycloak;
```

### Error: "Composer not found"
- Di cPanel: Gunakan path lengkap `/usr/local/bin/composer`
- Atau gunakan Terminal File Manager di cPanel

### Error: "Package not found"
```bash
# Coba install ulang
composer clear-cache
composer install
```

## 📞 Support
Jika ada masalah, bisa merujuk ke:
- **Panduan resmi**: https://git.bps.go.id/jkd-repo/sso-php
- **Chat dengan Fachrunisa** 😊
- **File debug**: `/sso-debug.php` untuk cek data satuan kerja

## 🎉 Hasil Akhir
Setelah instalasi berhasil:
- ✅ Login menggunakan SSO BPS  
- ✅ Otomatis detect apakah akun Pusat/Provinsi/Kabupaten
- ✅ Data satuan kerja lengkap tersedia
- ✅ Dashboard sesuai dengan level akses user 