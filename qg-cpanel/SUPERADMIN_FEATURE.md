# Fitur Superadmin - Quality Gates Dashboard

## Overview
Fitur superadmin memungkinkan user dengan username `farhan.arsyi` untuk mengimitasi akses dan privilege seperti user daerah tertentu. Superadmin dapat bertindak sebagai pusat, provinsi, atau kabupaten dengan akses data yang persis sama seperti user daerah tersebut.

## Fitur Utama

### 1. Menu Superadmin di Dropdown Profile
- Hanya muncul untuk username `farhan.arsyi`
- Menu "Switch Role" untuk mengubah peran
- Menu "Reset to Original" untuk kembali ke role asli
- Icon crown (👑) sebagai indikator superadmin

### 2. Role Switching Interface
- Modal dialog untuk memilih role (Pusat, Provinsi, Kabupaten)
- Dropdown provinsi yang terintegrasi dengan data daerah
- Dropdown kabupaten yang dinamis berdasarkan provinsi terpilih
- Validasi input sebelum switch role

### 3. Imitation Mode
- Superadmin dapat mengimitasi akses wilayah tertentu
- Data yang ditampilkan persis sama dengan user daerah
- Filter wilayah otomatis disesuaikan dengan role yang dipilih
- Batasan akses data sesuai dengan role yang diimitasi

### 4. Visual Indicators
- Badge "Superadmin Mode" pada info box wilayah
- Border kuning pada info box saat dalam mode imitation
- Pesan khusus yang menjelaskan mode superadmin

## Cara Penggunaan

### 1. Login sebagai Superadmin
```php
// Username: farhan.arsyi
// Password: sesuai dengan sistem SSO
```

### 2. Switch Role
1. Klik dropdown profile (icon user)
2. Pilih "Switch Role"
3. Pilih role yang diinginkan:
   - **Pusat**: Akses seluruh Indonesia
   - **Provinsi**: Pilih provinsi, akses semua kabupaten di provinsi tersebut
   - **Kabupaten**: Pilih provinsi dan kabupaten spesifik
4. Klik "Switch Role"

### 3. Reset Role
1. Klik dropdown profile
2. Pilih "Reset to Original"
3. Konfirmasi reset

## Implementasi Teknis

### 1. Session Management
```php
// Session imitation data
$_SESSION['superadmin_imitation'] = [
    'unit_kerja' => 'provinsi',
    'kode_provinsi' => '31',
    'kode_kabupaten' => '00',
    'nama_provinsi' => 'DKI Jakarta',
    'nama_kabupaten' => '',
    'switched_at' => '2024-01-15 10:30:00'
];
```

### 2. Filter Wilayah
```php
// Fungsi getSSOWilayahFilter() sudah dimodifikasi untuk mendukung superadmin
function getSSOWilayahFilter() {
    // Cek apakah superadmin sedang dalam mode imitation
    if (isSuperAdmin() && isset($_SESSION['superadmin_imitation'])) {
        // Gunakan data imitation
        return $imitation_filter;
    }
    // Gunakan data normal
    return $normal_filter;
}
```

### 3. API Endpoints
- `POST api.php?action=switch_superadmin_role`: Switch role
- `POST api.php?action=reset_superadmin_role`: Reset role

### 4. Frontend Integration
- JavaScript functions untuk handle modal dan AJAX calls
- Dynamic loading data daerah untuk dropdown
- Real-time UI updates setelah role switch

## Keamanan

### 1. Access Control
- Hanya username `farhan.arsyi` yang dapat mengakses fitur superadmin
- Validasi di server-side untuk semua operasi superadmin
- Session-based authentication

### 2. Data Isolation
- Superadmin hanya dapat mengakses data sesuai dengan role yang dipilih
- Tidak ada akses ke data yang tidak seharusnya terlihat
- Filter SQL otomatis diterapkan

## Testing

### 1. Test File
Gunakan `test_superadmin.php` untuk testing:
```bash
# Akses: http://your-domain/test_superadmin.php
```

### 2. Manual Testing
1. Login sebagai farhan.arsyi
2. Cek menu superadmin muncul di dropdown
3. Test switch role ke berbagai provinsi/kabupaten
4. Verifikasi data yang ditampilkan sesuai dengan role
5. Test reset role

## Troubleshooting

### 1. Menu Superadmin Tidak Muncul
- Pastikan username adalah `farhan.arsyi`
- Cek session SSO sudah aktif
- Refresh halaman

### 2. Switch Role Gagal
- Cek koneksi ke API
- Pastikan data daerah tersedia
- Cek error log

### 3. Data Tidak Sesuai
- Pastikan session imitation sudah tersimpan
- Cek filter wilayah di database
- Verifikasi kode provinsi/kabupaten

## File yang Dimodifikasi

1. `sso_integration.php` - Core superadmin functions
2. `api.php` - API endpoints untuk role switching
3. `monitoring.php` - Integration dengan monitoring page
4. `index.php` - Integration dengan dashboard page
5. `test_superadmin.php` - Test file
6. `SUPERADMIN_FEATURE.md` - Dokumentasi ini

## Catatan Penting

- Fitur ini hanya untuk user `farhan.arsyi`
- Semua operasi superadmin dicatat dalam session
- Data yang ditampilkan persis sama dengan user daerah
- Tidak ada akses khusus atau bypass keamanan
- Mode imitation dapat direset kapan saja
