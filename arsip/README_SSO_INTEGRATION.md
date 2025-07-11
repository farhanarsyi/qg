# 🔐 Implementasi SSO BPS untuk Quality Gates Dashboard

## 📋 Overview

Implementasi Single Sign-On (SSO) BPS dengan filtering wilayah otomatis berdasarkan data pegawai. Sistem ini mengintegrasikan autentikasi SSO BPS dengan dashboard dan monitoring Quality Gates, serta menerapkan pembatasan akses data berdasarkan wilayah kerja pegawai.

## 🎯 Fitur Utama

### ✅ SSO Integration
- **Library JKD SSO**: Menggunakan `irsadarief/jkd-sso` 
- **Data Lengkap**: Nama, NIP, Jabatan, Kode Organisasi, Provinsi, Kabupaten
- **Auto Login**: Tidak perlu login terpisah untuk dashboard/monitoring

### ✅ Regional Filtering
- **Pusat**: Akses seluruh Indonesia
- **Provinsi**: Akses terbatas pada provinsi tugas
- **Kabupaten**: Akses terbatas pada kabupaten tugas

### ✅ UI/UX Integration
- **Navbar Terpadu**: Info user dan cakupan wilayah
- **Auto Lock Fields**: Filter wilayah otomatis terkunci
- **Visual Indicators**: Badge dan info box wilayah kerja

## 📁 Struktur File

```
qg/
├── sso_integration.php      # Core integrasi SSO & filtering
├── sso_config.php          # Konfigurasi dasar SSO
├── sso_login.php           # Halaman login SSO
├── sso_callback.php        # Callback handler
├── sso_logout.php          # Logout handler
├── main.php                # Entry point utama
├── index.php               # Dashboard (terintegrasi SSO)
├── monitoring.php          # Monitoring (terintegrasi SSO)
├── profile.php             # Profile pegawai
├── test_sso_library.php    # Test library JKD SSO
└── debug_attributes.php    # Debug data SSO
```

## 🛠️ Core Functions

### `sso_integration.php`

#### 📊 Filtering Functions
```php
getSSOWilayahFilter()        // Get filter wilayah user
getWilayahSQLFilter()        // Generate SQL WHERE clause
getWilayahJSOptions()        // Generate JS filter options
```

#### 🎨 UI Functions
```php
renderSSONavbar($page)       // Navbar dengan user info
renderWilayahInfoBox()       // Info box cakupan wilayah
injectWilayahJS()           // Inject JS filter
```

#### 🔒 Security Functions
```php
requireSSOLogin($redirect)   // Ensure user login
```

## 🌐 Regional Access Logic

### 📍 Kode Organisasi Structure
```
Format: PPKKUUUUUUU (13 digit)
- PP: Kode Provinsi (2 digit)
- KK: Kode Kabupaten (2 digit)  
- UUUUUUU: Kode Unit (7 digit)
```

### 🏢 Unit Kerja Determination
```php
function determineUnitKerja($kode_organisasi) {
    // Ekstrak kode provinsi dan kabupaten
    $kode_provinsi = substr($kode_organisasi, 0, 2);
    $kode_kabupaten = substr($kode_organisasi, 2, 2);
    $kode_unit = substr($kode_organisasi, 7, 5);
    
    // Logika penentuan level
    if ($kode_provinsi === '00' || $kode_unit === '10000') {
        return 'pusat';        // Akses seluruh Indonesia
    } elseif ($kode_kabupaten === '00' || $kode_kabupaten === '71') {
        return 'provinsi';     // Akses 1 provinsi
    } else {
        return 'kabupaten';    // Akses 1 kabupaten
    }
}
```

## 🔄 Flow Integration

### 1️⃣ Login Flow
```
User → main.php → Check Login:
├── Not Logged In → sso_login.php → SSO BPS → sso_callback.php → Dashboard
└── Logged In → Dashboard/Monitoring (based on ?page parameter)
```

### 2️⃣ Navigation Flow
```
Dashboard ←→ Monitoring
    ↓
  Profile (optional)
    ↓
  Logout → SSO Logout → Login Page
```

### 3️⃣ Data Filtering Flow
```
User Login → Determine Unit Kerja → Generate Filters:
├── Pusat: WHERE 1=1 (All Indonesia)
├── Provinsi: WHERE kode_provinsi='XX'
└── Kabupaten: WHERE kode_provinsi='XX' AND kode_kabupaten='YY'
```

## 🚀 Quick Start

### 1. Prerequisites
```bash
# Install dependencies
composer require irsadarief/jkd-sso
```

### 2. Configuration
Edit `sso_config.php`:
```php
define('SSO_CLIENT_ID', 'your-client-id');
define('SSO_CLIENT_SECRET', 'your-client-secret');
define('SSO_REDIRECT_URI', 'http://yourdomain.com/qg/sso_callback.php');
```

### 3. Access Points
- **Main Entry**: `main.php`
- **Direct Dashboard**: `index.php`
- **Direct Monitoring**: `monitoring.php`
- **Profile**: `profile.php`

## 🧪 Testing & Debug

### Test Library
```
test_sso_library.php
```
- ✅ Library initialization
- ✅ URL generation  
- ✅ Method availability
- ✅ Dependencies check

### Debug Data
```
debug_attributes.php
```
- 📊 Current user data
- 📋 Available SSO methods
- 🔍 Raw SSO response

### Debug Wilayah
```
index.php?debug=1
monitoring.php?debug=1
```
- 🗺️ Current wilayah filter
- 📝 Generated SQL filter
- ⚙️ Filter configuration

## 🎨 UI Components

### Navbar Integration
```php
<?php renderSSONavbar('dashboard'); ?>
```
- 👤 User info (nama, jabatan)
- 🗺️ Cakupan wilayah
- 🔄 Navigation tabs
- ⚙️ Profile dropdown

### Wilayah Info Box
```php
<?php renderWilayahInfoBox(); ?>
```
- 📍 Badge level unit kerja
- 📋 Deskripsi cakupan
- ℹ️ Info pembatasan data

### Auto-Lock Fields
```javascript
window.updateFilterUI();
```
- 🔒 Lock provinsi/kabupaten dropdown
- 💡 Visual indicators
- 📱 Responsive behavior

## 📊 SQL Integration Examples

### Dashboard Queries
```php
$sql = "SELECT * FROM projects WHERE " . getWilayahSQLFilter('p');
```

### Monitoring Queries  
```php
$where_clause = getWilayahSQLFilter('coverage');
$sql = "SELECT * FROM project_coverages c WHERE $where_clause";
```

### JavaScript AJAX
```javascript
const sqlFilter = window.getSQLFilter('table_alias');
```

## 🔐 Security Features

### CSRF Protection
- ✅ State parameter validation
- ✅ Session-based verification

### Input Validation
- ✅ Redirect URL whitelist
- ✅ SQL injection prevention

### Access Control
- ✅ Unit kerja based restrictions
- ✅ Automatic field locking

## 🏗️ Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   SSO BPS       │    │  sso_integration │    │   Dashboard     │
│                 │────│                  │────│                 │
│ - Authentication│    │ - User Data      │    │ - Filtered Data │
│ - User Info     │    │ - Wilayah Logic  │    │ - Regional UI   │
└─────────────────┘    │ - SQL Filters    │    └─────────────────┘
                       └──────────────────┘             
                                │                        
                       ┌──────────────────┐             
                       │   Monitoring     │             
                       │                  │             
                       │ - Regional Data  │             
                       │ - Access Control │             
                       └──────────────────┘             
```

## 📈 Performance

### Optimizations
- ⚡ Single SQL filter generation
- 🎯 Client-side field locking
- 📦 Minimal session data
- 🔄 Efficient redirects

### Monitoring
- 📊 Load time tracking
- 🚀 Performance alerts
- 📝 Debug logging

## 🔧 Troubleshooting

### Common Issues

**1. Library not found**
```bash
composer install
```

**2. Redirect loops**
```php
// Check sso_config.php constants
// Verify redirect URL whitelist
```

**3. Empty user data**
```php
// Check debug_attributes.php
// Verify SSO method calls
```

**4. Wrong wilayah filter**
```php
// Check kode_organisasi parsing
// Debug with ?debug=1 parameter
```

## 📋 TODO / Roadmap

- [ ] **Caching**: Implement user data caching
- [ ] **Audit Log**: Track user access and filters
- [ ] **Performance**: Database query optimization
- [ ] **Mobile**: Responsive UI improvements
- [ ] **Testing**: Unit tests for filter logic

## 👥 Support

### Quick Links
- 🐛 **Issues**: Check debug files first
- 📚 **Documentation**: This file + inline comments
- 🧪 **Testing**: Use test files provided
- 💡 **Examples**: Demo files with sample data

### Contact
- **Developer**: Quality Gates Team
- **Library**: [irsadarief/jkd-sso](https://github.com/irsadarief/jkd-sso)
- **BPS SSO**: Subdirektorat JKD

---

## 🎉 Summary

Implementasi SSO BPS untuk Quality Gates Dashboard telah berhasil dengan fitur:

✅ **Complete SSO Integration** - Login sekali untuk semua akses
✅ **Automatic Regional Filtering** - Data otomatis sesuai wilayah kerja  
✅ **Seamless UI/UX** - Integrasi yang mulus tanpa perubahan workflow
✅ **Security & Performance** - Aman dan optimal untuk production

**Ready for Production!** 🚀 