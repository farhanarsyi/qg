# Konfigurasi Aplikasi QG Cpanel

## File Konfigurasi
Semua konfigurasi aplikasi berada di file `app_config.php`.

## Variabel Konfigurasi

### 1. Konfigurasi Card Display

#### `SHOW_MONITORING_CARDS`
- **Lokasi**: `app_config.php` line 8
- **Tipe**: boolean
- **Default**: `true`
- **Deskripsi**: Mengontrol apakah card monitoring ditampilkan atau tidak
- **Nilai**:
  - `true`: Card monitoring ditampilkan (default)
  - `false`: Card monitoring disembunyikan dan tidak dihitung

#### `SHOW_DASHBOARD_CARDS`
- **Lokasi**: `app_config.php` line 11
- **Tipe**: boolean
- **Default**: `true`
- **Deskripsi**: Mengontrol apakah card dashboard ditampilkan atau tidak
- **Nilai**:
  - `true`: Card dashboard ditampilkan (default)
  - `false`: Card dashboard disembunyikan dan tidak dihitung

### 2. Konfigurasi Debug Mode

#### `DEBUG_MODE`
- **Lokasi**: `app_config.php` line 16
- **Tipe**: boolean
- **Default**: `false`
- **Deskripsi**: Mengontrol apakah debug logging ditampilkan di console browser
- **Nilai**:
  - `true`: Debug logging aktif (menampilkan log di console)
  - `false`: Debug logging nonaktif (tidak menampilkan log di console)

### 3. Konfigurasi Filter Status Lanjutan

#### `ENABLE_ADVANCED_STATUS_FILTER`
- **Lokasi**: `app_config.php` line 21
- **Tipe**: boolean
- **Default**: `true`
- **Deskripsi**: Mengontrol apakah filter status lanjutan aktif
- **Nilai**:
  - `true`: Filter status lanjutan aktif (Semua, Sudah, Belum)
  - `false`: Filter status legacy (Sudah, Belum, Tidak Perlu)

## Cara Mengubah Konfigurasi

### 1. Mengubah Tampilan Card
```php
// Untuk menyembunyikan card monitoring
define('SHOW_MONITORING_CARDS', false);

// Untuk menyembunyikan card dashboard
define('SHOW_DASHBOARD_CARDS', false);
```

### 2. Mengaktifkan Debug Mode
```php
// Untuk mengaktifkan debug logging
define('DEBUG_MODE', true);
```

### 3. Mengubah Filter Status
```php
// Untuk menggunakan filter status lanjutan
define('ENABLE_ADVANCED_STATUS_FILTER', true);

// Untuk menggunakan filter status legacy
define('ENABLE_ADVANCED_STATUS_FILTER', false);
```

## Dampak Konfigurasi

### Card Monitoring (`SHOW_MONITORING_CARDS`)
- **Aktif (true)**: 
  - Card statistik ditampilkan
  - Perhitungan statistik dilakukan
  - Query database untuk statistik dijalankan
- **Nonaktif (false)**:
  - Card statistik disembunyikan
  - Perhitungan statistik tidak dilakukan
  - Query database untuk statistik tidak dijalankan
  - Performa lebih cepat

### Debug Mode (`DEBUG_MODE`)
- **Aktif (true)**:
  - Console log ditampilkan di browser
  - Informasi debug muncul di console
  - Berguna untuk development dan troubleshooting
- **Nonaktif (false)**:
  - Console log disembunyikan
  - Tidak ada informasi debug di console
  - User tidak melihat log internal

### Filter Status Lanjutan (`ENABLE_ADVANCED_STATUS_FILTER`)
- **Aktif (true)**:
  - Opsi filter: "Semua", "Sudah", "Belum"
  - Logika filter:
    - **Semua**: Menampilkan semua record
    - **Sudah**: Hanya record yang SEMUA statusnya sudah/tidak perlu
    - **Belum**: Record yang minimal ada 1 status belum
- **Nonaktif (false)**:
  - Opsi filter: "Sudah", "Belum", "Tidak Perlu"
  - Logika filter legacy (sesuai implementasi sebelumnya)

## Fungsi Helper

File `app_config.php` menyediakan fungsi helper:

- `shouldShowMonitoringCards()`: Cek apakah card monitoring harus ditampilkan
- `shouldShowDashboardCards()`: Cek apakah card dashboard harus ditampilkan
- `isDebugMode()`: Cek apakah debug mode aktif
- `isAdvancedStatusFilterEnabled()`: Cek apakah filter status lanjutan aktif
- `debugLog($message, $type)`: Fungsi untuk log debug (hanya muncul jika debug mode aktif)
- `getCardConfigJS()`: Mendapatkan konfigurasi dalam format JavaScript
- `getCardConfigArray()`: Mendapatkan konfigurasi dalam format PHP array

## Contoh Penggunaan

### Di PHP
```php
<?php
require_once 'app_config.php';

if (shouldShowMonitoringCards()) {
    // Lakukan perhitungan statistik
    $stats = calculateStats();
    displayStatsCards($stats);
}

if (isDebugMode()) {
    debugLog('Data loaded successfully');
}
?>
```

### Di JavaScript
```javascript
if (window.appConfig && window.appConfig.showMonitoringCards) {
    // Tampilkan card monitoring
    $("#statsCards").show();
}

if (window.appConfig && window.appConfig.debugMode) {
    console.log('Debug information');
}
```

## Rekomendasi Konfigurasi

### Development
```php
define('SHOW_MONITORING_CARDS', true);
define('SHOW_DASHBOARD_CARDS', true);
define('DEBUG_MODE', true);
define('ENABLE_ADVANCED_STATUS_FILTER', true);
```

### Production
```php
define('SHOW_MONITORING_CARDS', true);
define('SHOW_DASHBOARD_CARDS', true);
define('DEBUG_MODE', false);
define('ENABLE_ADVANCED_STATUS_FILTER', true);
```

### Performance Optimized
```php
define('SHOW_MONITORING_CARDS', false);
define('SHOW_DASHBOARD_CARDS', false);
define('DEBUG_MODE', false);
define('ENABLE_ADVANCED_STATUS_FILTER', true);
```
