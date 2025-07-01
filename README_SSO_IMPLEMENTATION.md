# Implementasi SSO BPS untuk Dashboard Quality Gates

## Overview
Implementasi Single Sign-On (SSO) BPS telah berhasil diterapkan pada aplikasi Dashboard Quality Gates menggunakan protokol OpenID Connect yang didasarkan pada OAuth2.

## Kredensial SSO
- **SSO URL**: https://sso.bps.go.id
- **SSO Realm**: pegawai-bps
- **SSO Scope**: openid profile-pegawai
- **Client ID**: 07300-dashqg-l30
- **Client URL**: https://dashboardqg.web.bps.go.id
- **Client Secret**: e1c46e44-f33a-45f0-ace1-62c445333ae7

## File-file yang Dibuat/Dimodifikasi

### File Baru
1. **sso-auth.php** - Handler utama untuk autentikasi SSO
2. **sso-callback.php** - Callback handler untuk response dari SSO
3. **auth-check.php** - Utility functions untuk mengecek status autentikasi
4. **logout.php** - Handler untuk logout (local + SSO)
5. **sso-debug.php** - File debug untuk mengecek data satuan kerja (jawaban pertanyaan Farhan)
6. **composer.json** - Konfigurasi package Composer

### File yang Dimodifikasi
1. **login.php** - Ditambahkan tombol SSO dan handling pesan error/success
2. **index.php** - Integrasi dengan sistem autentikasi SSO
3. **monitoring.php** - Integrasi dengan sistem autentikasi SSO

## Struktur Autentikasi

### Flow Login SSO
1. User mengklik tombol "Masuk dengan SSO BPS" di halaman login
2. Redirect ke `sso-auth.php`
3. `sso-auth.php` melakukan redirect ke SSO BPS
4. User login di SSO BPS
5. SSO BPS redirect ke `sso-callback.php`
6. `sso-callback.php` memvalidasi dan menyimpan data user ke session
7. Redirect ke dashboard (`index.php`)

### Session Management
Data user yang disimpan dalam session:
```php
$_SESSION['user_authenticated'] = true;
$_SESSION['user_data'] = [
    'nama' => $user->getName(),
    'email' => $user->getEmail(),
    'username' => $user->getUsername(),
    'nip' => $user->getNip(),
    'nip_baru' => $user->getNipBaru(),
    'kode_organisasi' => $user->getKodeOrganisasi(),
    'kode_provinsi' => $user->getKodeProvinsi(),
    'kode_kabupaten' => $user->getKodeKabupaten(),
    'provinsi' => $user->getProvinsi(),
    'kabupaten' => $user->getKabupaten(),
    'golongan' => $user->getGolongan(),
    'jabatan' => $user->getJabatan(),
    'foto' => $user->getUrlFoto(),
    'eselon' => $user->getEselon(),
];
$_SESSION['access_token'] = $token->getToken();
$_SESSION['logout_url'] = $provider->getLogoutUrl();
```

### Authentication Functions
Fungsi-fungsi utama di `auth-check.php`:
- `isUserAuthenticated()` - Mengecek apakah user sudah login
- `getUserData()` - Mendapatkan data user dari session
- `requireAuth()` - Redirect ke login jika belum autentikasi
- `getLogoutUrl()` - Mendapatkan URL logout SSO
- `getUserInitials()` - Mendapatkan inisial nama untuk avatar

### Flow Logout
1. User mengklik tombol "Logout"
2. Redirect ke `logout.php`
3. `logout.php` menghapus session lokal
4. Redirect ke SSO logout URL
5. SSO melakukan logout dan redirect kembali ke aplikasi

## Library yang Digunakan
- **irsadarief/jkd-sso** - Package Composer resmi SSO BPS (sesuai saran Fachrunisa)
- **api-pegawai-master/** - Library API Pegawai BPS (tersedia untuk integrasi lebih lanjut)

## Instalasi Package SSO (Sesuai Instruksi Fachrunisa)

### 1. Install Composer
Pastikan Composer sudah terinstall di sistem Anda:
- Download dari: https://getcomposer.org/download/
- Untuk cPanel, biasanya sudah tersedia secara otomatis

### 2. Install Package SSO BPS
Jalankan perintah ini di terminal (untuk development lokal):
```bash
composer require irsadarief/jkd-sso
```

### 3. Untuk Deploy ke cPanel
- Upload file `composer.json` 
- cPanel biasanya akan otomatis mengikuti dependency yang ada
- Pastikan folder `vendor/` ter-upload setelah install

### 4. Verifikasi Instalasi
Setelah install, struktur folder akan seperti ini:
```
qg/
├── vendor/
│   ├── autoload.php
│   └── irsadarief/
├── composer.json
├── sso-auth.php
├── sso-callback.php
└── ...
```

## Keamanan
- CSRF protection menggunakan OAuth2 state parameter
- Session-based authentication
- Validasi response dari SSO server
- Error handling untuk berbagai skenario gagal login

## Kompatibilitas
- Tetap mendukung login lokal (jika diperlukan)
- Backward compatible dengan sistem yang sudah ada
- Responsive design untuk semua device

## Testing
1. Akses https://dashboardqg.web.bps.go.id/login.php
2. Klik tombol "Masuk dengan SSO BPS"
3. Login menggunakan kredensial BPS
4. Verifikasi data user dan akses ke dashboard
5. Test logout dan verifikasi session cleanup

## Debug Satuan Kerja (Sesuai Pertanyaan Farhan)
Untuk mengecek "apakah akun provinsi atau kabkot" dan melihat data satuan kerja lengkap:

1. **Login melalui SSO** terlebih dahulu
2. **Akses file debug**: https://dashboardqg.web.bps.go.id/sso-debug.php
3. **Lihat analisis level akses**:
   - ✅ **AKUN PUSAT** - Kode provinsi: 00, Kode kabupaten: 00
   - ✅ **AKUN PROVINSI** - Kode provinsi: tidak 00, Kode kabupaten: 00  
   - ✅ **AKUN KABUPATEN/KOTA** - Kode provinsi: tidak 00, Kode kabupaten: tidak 00

### Data Satuan Kerja yang Tersedia:
- **Kode Organisasi** - Kode lengkap satuan kerja
- **Kode Provinsi** - 2 digit kode provinsi (00 = pusat)
- **Kode Kabupaten** - 2 digit kode kabupaten (00 = level provinsi)
- **Nama Provinsi** - Nama lengkap provinsi
- **Nama Kabupaten** - Nama lengkap kabupaten/kota
- **Jabatan, Golongan, Eselon** - Info kepegawaian lengkap

### Contoh Interpretasi:
```
Kode Provinsi: 32, Kode Kabupaten: 00 → Akun Provinsi Jawa Barat
Kode Provinsi: 32, Kode Kabupaten: 04 → Akun Kabupaten Bandung, Jawa Barat  
Kode Provinsi: 00, Kode Kabupaten: 00 → Akun Pusat BPS
```

## Error Handling
Sistem menangani berbagai error conditions:
- Invalid state (CSRF protection)
- No authorization code
- SSO authentication failure
- Network errors
- Invalid user data

## Notes
- File `LoginSSO.php` adalah contoh dari aplikasi lain (SKS) dan tidak digunakan dalam implementasi ini
- Implementasi ini dirancang untuk aplikasi PHP native (bukan framework CodeIgniter)
- URL callback harus didaftarkan di konfigurasi SSO BPS 