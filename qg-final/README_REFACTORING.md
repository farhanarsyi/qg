# Quality Gates Dashboard - Refactored Version

## 🎯 Overview

Versi ini adalah hasil refactoring dari Quality Gates Dashboard untuk membuat kode lebih modular, maintainable, dan mengikuti best practices pengembangan PHP modern.

## 📁 Struktur Folder Baru

```
qg-final/
├── config/                 # Konfigurasi aplikasi
│   ├── bootstrap.php       # Main application bootstrap
│   ├── environment.php     # Environment detection
│   ├── production.php      # Production configuration
│   ├── local.php          # Local development configuration
│   ├── development.php     # Development configuration
│   └── shared.php         # Shared configuration
├── includes/              # Core application files
│   ├── classes/           # PHP Classes
│   │   ├── SSOManager.php # SSO management class
│   │   ├── APIManager.php # API management class
│   │   └── Logger.php     # Logging class
│   └── helpers/           # Helper functions
│       └── common.php     # Common utility functions
├── assets/               # Static assets
│   └── navbar.css       # CSS files
├── docs/                # Documentation
│   └── *.md            # All markdown files
├── vendor/              # Composer dependencies
├── logs/               # Application logs (auto-created)
└── [main application files]
```

## 🚀 Fitur Baru

### 1. **Environment-Based Configuration**
- Otomatis mendeteksi environment (production, local, development)
- Konfigurasi terpisah untuk setiap environment
- URL dan setting yang sesuai per environment

### 2. **Modular Class Structure**
- **SSOManager**: Mengelola semua operasi SSO
- **APIManager**: Mengelola semua API calls
- **Logger**: Sistem logging yang konsisten

### 3. **Helper Functions**
- Fungsi utility yang dapat digunakan di seluruh aplikasi
- Standardized error handling
- Security helpers (CSRF, validation, dll)

### 4. **Improved Security**
- CSRF protection
- Better session management
- Input validation helpers
- Secure cookie settings

### 5. **Better Error Handling**
- Centralized logging
- User-friendly error pages
- Debug mode untuk development

## 🔧 Cara Penggunaan

### Setup Awal

1. **Copy files ke server/local**
2. **Pastikan folder `logs/` writable**
3. **Update konfigurasi jika diperlukan**

### Environment Configuration

Aplikasi otomatis mendeteksi environment berdasarkan domain:
- `dashboardqg.web.bps.go.id` → Production
- `localhost` → Local
- Lainnya → Development

Untuk override manual, set constant:
```php
define('QG_ENVIRONMENT', 'production'); // sebelum load bootstrap
```

### Menggunakan Class Baru

#### SSO Manager
```php
require_once 'config/bootstrap.php';

$ssoManager = new SSOManager();

// Check login status
if (!$ssoManager->isLoggedIn()) {
    $ssoManager->requireLogin();
}

// Get user data
$userData = $ssoManager->getUserData();
$unitKerja = $ssoManager->getUserUnitKerja();
$filter = $ssoManager->getWilayahFilter();
```

#### API Manager
```php
$apiManager = new APIManager();

// Get access token
$token = $apiManager->getAccessToken();

// Get employee data
$pegawai = $apiManager->getPegawaiByUsername('username123');

// Make custom API request
$response = $apiManager->makeRequest('GET', 'https://api.example.com/data');
```

#### Logger
```php
$logger = new Logger('MODULE_NAME');

$logger->info('Operation completed', ['user_id' => 123]);
$logger->warning('Something suspicious');
$logger->error('Critical error occurred', ['exception' => $e]);
$logger->debug('Debug information'); // Only shown in debug mode
```

#### Helper Functions
```php
// Redirect with validation
redirect('dashboard.php');

// Safe array access
$value = getArrayValue($_POST, 'field_name', 'default_value');

// HTML escaping
echo e($userInput);

// CSRF protection
$token = generateCSRFToken();
$isValid = verifyCSRFToken($_POST['csrf_token']);

// JSON response
jsonResponse(['status' => 'success', 'data' => $data]);
```

## 🔄 Migration dari Versi Lama

### File yang Diganti

1. **sso_config.php** → `config/` folder
2. **sso_callback.php** → `sso_callback_new.php` (contoh)
3. **sso_login.php** → `sso_login_new.php` (contoh)

### Update File Existing

Untuk file yang sudah ada, tambahkan di awal:
```php
// Ganti require lama
require_once 'config/bootstrap.php';

// Gunakan class baru
$ssoManager = new SSOManager();
$apiManager = new APIManager();
```

### Contoh Migration

**Sebelum:**
```php
require_once 'sso_config.php';
if (!isLoggedIn()) {
    header('Location: sso_login.php');
    exit;
}
$userData = getUserData();
```

**Sesudah:**
```php
require_once 'config/bootstrap.php';
$ssoManager = new SSOManager();
$ssoManager->requireLogin();
$userData = $ssoManager->getUserData();
```

## 🔒 Security Improvements

1. **Session Security**: Secure cookie settings, session regeneration
2. **CSRF Protection**: Built-in CSRF token generation and validation
3. **Input Validation**: Helper functions untuk validasi input
4. **Error Handling**: Tidak expose sensitive information di production

## 📝 Logging

Semua aktivitas penting dicatat dalam log file:
- SSO login/logout
- API calls
- Errors dan exceptions
- Debug information (development only)

Log tersimpan di `logs/app.log` dengan format:
```
[2024-01-01 12:00:00] [INFO] [SSO] User logged in | Data: {"username":"john.doe"}
```

## 🧪 Testing

### Local Development
- URL: `http://localhost/qg/qg-final`
- Debug mode: ON
- Error reporting: FULL
- Logging: Verbose

### Production
- URL: `https://dashboardqg.web.bps.go.id`
- Debug mode: OFF
- Error reporting: Minimal
- Logging: Errors only

## 🆘 Troubleshooting

### Common Issues

1. **"Class not found" errors**
   - Pastikan `config/bootstrap.php` di-load
   - Check autoloader configuration

2. **Permission errors**
   - Pastikan folder `logs/` writable (755 atau 777)

3. **SSO errors**
   - Check environment configuration
   - Verify SSO credentials per environment

4. **Configuration errors**
   - Check environment detection in `config/environment.php`
   - Verify constants are defined correctly

### Debug Mode

Enable debug untuk troubleshooting:
```php
define('QG_ENVIRONMENT', 'development');
```

## 📈 Performance Improvements

1. **Autoloading**: Class auto-loading mengurangi memory usage
2. **Caching**: Token API di-cache untuk mengurangi HTTP requests
3. **Logging**: Async logging tidak block main process
4. **Session**: Optimized session handling

## 🎉 Benefits

1. **Maintainability**: Kode lebih terorganisir dan mudah diubah
2. **Reusability**: Class dan functions dapat digunakan ulang
3. **Testability**: Structure memudahkan unit testing
4. **Security**: Built-in security features
5. **Documentation**: Better code documentation dan type hints
6. **Environment Management**: Easy deployment ke berbagai environment

## 📞 Support

Untuk pertanyaan atau issues, contact development team atau buat issue ticket dengan detail:
- Environment (production/local/development)
- Error message lengkap
- Steps to reproduce
- Log entries terkait 