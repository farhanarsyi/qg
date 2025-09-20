# Implementasi SSO BPS untuk Dashboard Quality Gates

## Deskripsi
Implementasi Single Sign-On (SSO) BPS untuk aplikasi Dashboard Quality Gates menggunakan protokol OpenID Connect yang berbasis OAuth2.

## Konfigurasi SSO BPS
- **SSO URL**: https://sso.bps.go.id
- **SSO Realm**: pegawai-bps
- **SSO Scope**: openid profile-pegawai
- **Client ID**: 07300-dashqg-l30
- **Client Secret**: e1c46e44-f33a-45f0-ace1-62c445333ae7

## File yang Dibuat

### 1. sso_config.php
File konfigurasi utama yang berisi:
- Konfigurasi parameter SSO BPS
- Fungsi untuk mendapatkan access token
- Fungsi untuk mengambil data pegawai dari API
- Fungsi manajemen session

### 2. sso_login.php
Halaman login SSO yang:
- Menampilkan tombol "Masuk dengan SSO BPS"
- Mengarahkan user ke portal SSO BPS untuk autentikasi
- Menggunakan state parameter untuk keamanan CSRF

### 3. sso_callback.php
Handler callback dari SSO yang:
- Menerima authorization code dari SSO BPS
- Menukar code dengan access token
- Mengambil data user dari userinfo endpoint
- Mengambil data pegawai lengkap dari API Pegawai BPS
- Menyimpan data ke session dan redirect ke profile

### 4. profile.php
Halaman profil pegawai yang menampilkan:
- Informasi personal pegawai (nama, username, email, NIP)
- Informasi jabatan (jabatan, eselon, golongan, kode organisasi)
- Informasi lokasi (provinsi, kabupaten, alamat kantor)
- Menu navigasi ke Dashboard dan Monitoring

### 5. sso_logout.php
Handler logout yang:
- Menghapus session lokal
- Mengarahkan ke SSO logout endpoint
- Redirect kembali ke halaman login

### 6. demo_pegawai.php
File demo untuk testing tanpa koneksi SSO aktual yang menampilkan contoh data pegawai.

### 7. demo_unit_levels.php
File demo untuk testing berbagai level unit kerja (pusat, provinsi, kabupaten) dengan parameter:
- `?level=pusat` - Demo akses level pusat (seluruh Indonesia)
- `?level=provinsi` - Demo akses level provinsi (satu provinsi)
- `?level=kabupaten` - Demo akses level kabupaten (satu kabupaten)

## Cara Penggunaan

### Testing dengan Data Demo
1. **Demo Pegawai Standar**: Akses `demo_pegawai.php` untuk testing dengan data dummy
2. **Demo Level Unit Kerja**: Akses `demo_unit_levels.php` dengan parameter:
   - `demo_unit_levels.php?level=pusat` - Testing akses level pusat
   - `demo_unit_levels.php?level=provinsi` - Testing akses level provinsi
   - `demo_unit_levels.php?level=kabupaten` - Testing akses level kabupaten
3. Akan menampilkan halaman profil dengan data pegawai sesuai level unit kerja
4. Perhatikan bagian "Unit Kerja & Akses Wilayah" yang menunjukkan filter monitoring
5. Dari halaman profil, bisa memilih menu Dashboard atau Monitoring

### Penggunaan Dengan SSO Aktual
1. Akses `sso_login.php` 
2. Klik tombol "Masuk dengan SSO BPS"
3. Login menggunakan akun pegawai BPS di portal SSO
4. Setelah berhasil login, akan diarahkan ke halaman profil
5. Pilih menu Dashboard (`index.php`) atau Monitoring (`monitoring.php`)

## Keamanan
- Menggunakan state parameter untuk proteksi CSRF
- Session management untuk menjaga status login
- Validasi token dan response dari SSO
- Logout yang aman dengan penghapusan session

## Data Pegawai yang Ditampilkan
Sesuai dengan API Pegawai BPS, data yang ditampilkan meliputi:
- Nama lengkap
- Username
- Email  
- NIP dan NIP Baru
- Jabatan dan Eselon
- Golongan
- Kode Organisasi dan Kode Organisasi Lengkap
- Provinsi dan Kabupaten
- Alamat Kantor

## Unit Kerja dan Filter Wilayah
Aplikasi secara otomatis menentukan unit kerja pegawai berdasarkan kode organisasi:

### Level Unit Kerja:
1. **Pusat** - Akses seluruh Indonesia
   - Kode provinsi `00` atau kode unit `10000`
   - Dapat melihat data semua provinsi dan kabupaten/kota

2. **Provinsi** - Akses tingkat provinsi
   - Kode kabupaten `00` atau `71` (khusus ibu kota provinsi)
   - Dapat melihat data semua kabupaten/kota dalam provinsi tersebut

3. **Kabupaten/Kota** - Akses tingkat kabupaten/kota
   - Hanya dapat melihat data kabupaten/kota masing-masing

### Filter Monitoring & Dashboard:
- **User Pusat**: Dapat memfilter dan melihat data dari seluruh Indonesia
- **User Provinsi**: Hanya dapat melihat data dari provinsi tempat bertugas
- **User Kabupaten**: Hanya dapat melihat data dari kabupaten/kota tempat bertugas

Filter ini akan diterapkan pada halaman Monitoring dan Dashboard untuk membatasi akses data sesuai dengan unit kerja pegawai.

## Catatan Penting
- Pastikan URL redirect_uri di `sso_config.php` sesuai dengan domain aplikasi
- Untuk production, ubah URL localhost menjadi domain aktual
- SSL/HTTPS diperlukan untuk production
- Client ID dan Secret harus dijaga kerahasiaannya

## Troubleshooting
- Jika error "invalid_state", hapus cookies browser dan coba lagi
- Jika error token, pastikan Client ID dan Secret benar
- Jika tidak bisa akses API Pegawai, pastikan token masih valid 