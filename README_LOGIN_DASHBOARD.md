# Sistem Login dan Dashboard Quality Gates

## Overview
Sistem ini menambahkan fitur login dan dashboard per wilayah untuk aplikasi Quality Gates yang sudah ada. Sistem menggunakan role-based access control (RBAC) berdasarkan wilayah pengguna.

## Fitur Baru

### 1. Halaman Login (`login.php`)
- Login dengan username dari tabel `user`
- Password tetap untuk semua akun: `password123`
- Role otomatis berdasarkan kolom `prov` dan `kab` di tabel `user`
- Simpan session menggunakan localStorage
- Redirect otomatis ke dashboard setelah login berhasil

### 2. Dashboard Per Wilayah (`dashboard.php`)
- Tampilan berbeda berdasarkan role user:
  - **Pusat**: Akses ke seluruh data (nasional, provinsi, kabupaten/kota)
  - **Provinsi**: Akses ke data provinsi dan kabupaten/kota dalam provinsinya
  - **Kabupaten/Kota**: Akses hanya ke data kabupaten/kota spesifik
- Tabel project-gate dengan informasi:
  - Nama kegiatan
  - Gate (GATE1, GATE2, dst.)
  - Wilayah
  - Tahun
  - Tanggal mulai (dari `prev_insert_start`)
  - Tanggal selesai (dari `cor_upload_end`)
  - Status: Akan datang, Sedang berlangsung (hijau berkedip), Selesai

### 3. Filter untuk Pusat dan Provinsi
- Filter berdasarkan tahun
- Filter berdasarkan kegiatan
- Filter berdasarkan wilayah
- Filter dinamis yang saling berkaitan

### 4. Navigasi
- Link dari halaman monitoring ke login/dashboard
- Link dari dashboard kembali ke monitoring
- Tombol logout dengan konfirmasi

## Endpoint API Baru

### `login`
- **Input**: `username`, `password`
- **Output**: Data user dengan role
- **Role ditentukan berdasarkan**:
  - `prov=00, kab=00` → Pusat
  - `prov!=00, kab=00` → Provinsi
  - `prov!=00, kab!=00` → Kabupaten/Kota

### `fetchDashboardData`
- **Input**: `user_prov`, `user_kab`, filter opsional
- **Output**: Data project-gate sesuai dengan role user
- **Filter otomatis berdasarkan role**

### `fetchAvailableYears`
- **Input**: `user_prov`, `user_kab`
- **Output**: List tahun yang tersedia sesuai role

### `fetchAvailableProjects`
- **Input**: `user_prov`, `user_kab`, `year` (opsional)
- **Output**: List project yang tersedia sesuai role

### `fetchAvailableRegions`
- **Input**: `user_prov`, `user_kab`, `project_id` (opsional)
- **Output**: List wilayah yang tersedia sesuai role

## Struktur Role-Based Access Control

### 1. Pusat (prov=00, kab=00)
```sql
-- Bisa akses semua data tanpa filter
SELECT ... FROM projects p 
INNER JOIN project_coverages pc ON p.id = pc.id_project
```

### 2. Provinsi (prov!=00, kab=00)
```sql
-- Hanya provinsi dan kabupaten dalam provinsinya
WHERE (pc.prov = user_prov OR (pc.prov = user_prov AND pc.kab != '00'))
```

### 3. Kabupaten/Kota (prov!=00, kab!=00)
```sql
-- Hanya kabupaten spesifik
WHERE pc.prov = user_prov AND pc.kab = user_kab
```

## Status Tanggal dengan Visual

### 1. Akan Datang (date-upcoming)
- Background: Orange transparan
- Text: Orange
- Kondisi: Hari ini < tanggal mulai

### 2. Sedang Berlangsung (date-active)
- Background: Hijau gradient
- Text: Putih
- Animasi: Pulse berkedip-kedip
- Kondisi: Tanggal mulai ≤ hari ini ≤ tanggal selesai

### 3. Selesai (date-completed)
- Background: Abu-abu transparan
- Text: Abu-abu
- Kondisi: Hari ini > tanggal selesai

## Security dan Session

### LocalStorage
- Data user disimpan di `localStorage` dengan key `qg_user`
- Auto-redirect ke login jika data tidak valid
- Auto-redirect ke dashboard jika sudah login

### Password
- Sementara menggunakan password tetap `password123` untuk demo
- Dapat diubah di endpoint `login` pada `api.php`

## File yang Ditambahkan/Dimodifikasi

### File Baru:
1. `login.php` - Halaman login
2. `dashboard.php` - Dashboard per wilayah
3. `README_LOGIN_DASHBOARD.md` - Dokumentasi ini

### File yang Dimodifikasi:
1. `api.php` - Ditambahkan endpoint login dan dashboard
2. `monitoring.php` - Ditambahkan navigasi ke login/dashboard

## Cara Penggunaan

1. **Akses halaman monitoring**: `monitoring.php`
2. **Klik "Login Dashboard"** untuk masuk ke halaman login
3. **Login dengan username** dari tabel `user` dan password `password123`
4. **Dashboard otomatis menyesuaikan** dengan role user
5. **Gunakan filter** (jika tersedia) untuk menyaring data
6. **Klik "Monitoring"** untuk kembali ke halaman monitoring

## Testing

Server development dapat dijalankan dengan:
```bash
cd /path/to/qg
php -S localhost:8000
```

Kemudian akses:
- Monitoring: `http://localhost:8000/monitoring.php`
- Login: `http://localhost:8000/login.php`
- Dashboard: `http://localhost:8000/dashboard.php` 