# Sistem Login dan Dashboard Quality Gates

## Overview
Sistem ini menambahkan fitur login dan dashboard per wilayah untuk aplikasi Quality Gates yang sudah ada. Sistem menggunakan role-based access control (RBAC) berdasarkan wilayah pengguna dengan fokus pada perspektif wilayah.

## Fitur Baru

### 1. Halaman Login (`login.php`)
- Login dengan username dari tabel `user`
- Password tetap untuk semua akun: `password123`
- Role otomatis berdasarkan kolom `prov` dan `kab` di tabel `user`
- Simpan session menggunakan localStorage
- Redirect otomatis ke dashboard setelah login berhasil

### 2. Dashboard Per Wilayah (`index.php`)
- **Dashboard berfokus pada perspektif wilayah** dengan nama wilayah ditampilkan di header
- Tampilan berbeda berdasarkan role user:
  - **Pusat**: Akses ke seluruh data, **WAJIB** mengisi filter tahun dan wilayah sebelum melihat data
  - **Provinsi**: Akses ke data provinsi dan kabupaten/kota dalam provinsinya
  - **Kabupaten/Kota**: Akses hanya ke data kabupaten/kota spesifik, **dengan filter kegiatan**

### 3. Tabel Project-Gate dengan Merge
- **Kegiatan yang sama di-merge dalam satu row** dengan rowspan untuk nama kegiatan dan tahun
- Kolom tabel: Kegiatan, Tahun, Gate, Tanggal Mulai, Tanggal Selesai, Status
- Tanggal mulai diambil dari `prev_insert_start`
- Tanggal selesai diambil dari `cor_upload_end`
- Status visual dengan animasi berkedip untuk tanggal aktif

### 4. Filter dengan Urutan Baru
- **Urutan filter: Tahun → Wilayah → Kegiatan** (sesuai perspektif wilayah)
- **Filter dinamis** yang saling berkaitan
- **Semua level user** sekarang memiliki akses ke filter (termasuk kabupaten untuk kegiatan)
- **Pusat**: Wajib isi filter tahun dan wilayah untuk performa
- **Provinsi**: Filter opsional
- **Kabupaten**: Filter kegiatan berdasarkan tahun

### 5. Optimisasi Loading
- **Pusat**: Tidak ada auto-load data untuk mencegah loading berlebihan
- **Provinsi & Kabupaten**: Auto-load data awal tanpa filter
- Loading state yang lebih responsif

## Struktur Role-Based Access Control

### 1. Pusat (prov=00, kab=00)
- **Wajib filter**: Tahun dan Wilayah minimal harus dipilih
- Akses ke semua data nasional
- Filter: Tahun → Wilayah → Kegiatan (opsional)

### 2. Provinsi (prov!=00, kab=00)
- Auto-load data provinsi dan kabupaten dalam provinsinya
- Filter opsional: Tahun → Wilayah → Kegiatan
- Nama provinsi ditampilkan di header

### 3. Kabupaten/Kota (prov!=00, kab!=00)
- Auto-load data kabupaten spesifik
- Filter kegiatan berdasarkan tahun
- Nama kabupaten/kota ditampilkan di header
- Filter wilayah disembunyikan (karena sudah fix)

## Status Tanggal dengan Visual

### 1. Akan Datang (date-upcoming)
- Background: Orange transparan
- Text: Orange
- Kondisi: Hari ini < tanggal mulai

### 2. Sedang Berlangsung (date-active)
- Background: Hijau gradient
- Text: Putih
- **Animasi: Pulse berkedip-kedip dengan shadow effect**
- Kondisi: Tanggal mulai ≤ hari ini ≤ tanggal selesai

### 3. Selesai (date-completed)
- Background: Abu-abu transparan
- Text: Abu-abu
- Kondisi: Hari ini > tanggal selesai

## Fitur Interface Baru

### Header Dinamis
- Menampilkan nama wilayah yang sedang dipilih
- Format: "Dashboard Quality Gates - **Nama Wilayah**"

### Merge Kegiatan
- Kegiatan yang sama (nama dan tahun) digabung dalam satu baris
- Gate ditampilkan terpisah di baris berbeda
- Menggunakan rowspan untuk efisiensi tampilan

### Filter yang Lebih Smart
- **Cascading filter**: Tahun mempengaruhi wilayah, wilayah mempengaruhi kegiatan
- **Validation untuk pusat**: Tidak bisa load data tanpa filter minimal
- **Auto-clear dependent filter** saat parent filter berubah

## API Endpoints (Updated)

### `fetchAvailableProjects` (Enhanced)
- **Input baru**: `region` parameter untuk filtering lebih akurat
- Filter otomatis berdasarkan role dan region yang dipilih

### Lainnya tetap sama:
- `login`, `fetchDashboardData`, `fetchAvailableYears`, `fetchAvailableRegions`, `fetchUsers`

## Revisi yang Telah Dilakukan

1. ✅ **Filter kegiatan untuk kabupaten**: Sekarang level kabupaten memiliki filter kegiatan berdasarkan tahun
2. ✅ **Validasi filter untuk pusat**: Loading hanya jika filter tahun dan wilayah diisi
3. ✅ **Merge kegiatan yang sama**: Tabel menggunakan rowspan untuk menggabungkan kegiatan yang sama
4. ✅ **Urutan filter baru**: Tahun → Wilayah → Kegiatan (fokus perspektif wilayah)
5. ✅ **Nama wilayah di header**: Dinamis berdasarkan pilihan atau role user
6. ✅ **Hapus stats card**: Simplified interface

## Cara Penggunaan

### Untuk Level Pusat:
1. Login dengan akun pusat (prov=00, kab=00)
2. **Wajib pilih** tahun dan wilayah terlebih dahulu
3. Klik "Tampilkan Data" untuk melihat hasil
4. Filter kegiatan opsional untuk menyaring lebih lanjut

### Untuk Level Provinsi:
1. Login dengan akun provinsi (prov≠00, kab=00)
2. Data otomatis dimuat untuk provinsi dan kabupaten dalam provinsinya
3. Gunakan filter untuk menyaring data (opsional)

### Untuk Level Kabupaten:
1. Login dengan akun kabupaten (prov≠00, kab≠00)
2. Data otomatis dimuat untuk kabupaten spesifik
3. Gunakan filter tahun untuk memuat kegiatan yang tersedia
4. Pilih kegiatan spesifik jika diperlukan

## File yang Dimodifikasi

### Major Changes:
1. `index.php` (dashboard) - **Complete revamp** interface dan logika
2. `api.php` - Enhanced `fetchAvailableProjects` dengan region parameter

### Previous Files:
1. `login.php` - Tetap sama
2. `monitoring.php` - Tetap sama  
3. `test_users.php` - Tetap sama

## Testing

Server development:
```bash
cd "C:\xampp\htdocs\qg"
php -S localhost:8000
```

Akses:
- Login: `http://localhost:8000/login.php`
- Dashboard: `http://localhost:8000/index.php` atau `http://localhost:8000/`
- Test Users: `http://localhost:8000/test_users.php` 