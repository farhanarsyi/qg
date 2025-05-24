# Update Sistem Autentikasi dan Filter Coverage

## Ringkasan Perubahan

Implementasi sistem autentikasi lengkap dan filter coverage berdasarkan role user untuk dashboard dan monitoring Quality Gates.

## 1. Sistem Autentikasi Universal

### Dashboard (index.php)
- ✅ **Login Required**: Semua akses harus login terlebih dahulu
- ✅ **Auto Redirect**: User yang tidak login otomatis diarahkan ke `login.php`
- ✅ **Session Management**: Menggunakan localStorage untuk menyimpan data user
- ✅ **Logout Function**: User dapat logout dengan aman

### Monitoring (monitoring.php)
- ✅ **Login Required**: Sistem autentikasi yang sama dengan dashboard
- ✅ **Navbar Konsisten**: UI navbar yang seragam dengan dashboard
- ✅ **User Info Display**: Menampilkan informasi user yang sedang login
- ✅ **Logout Function**: Tombol logout dengan konfirmasi

## 2. Filter Coverage Berdasarkan Role User

### Level Akses User:

#### **Pusat (prov="00", kab="00")**
- **Dashboard**: Dapat melihat semua project di seluruh Indonesia
- **Monitoring**: Dapat melihat semua project dan coverage wilayah
- **Coverage**: Nasional, Provinsi, Kabupaten/Kota

#### **Provinsi (prov≠"00", kab="00")**
- **Dashboard**: Hanya project di provinsi tersebut dan kabupaten di dalamnya
- **Monitoring**: Hanya project dan coverage wilayah dalam provinsinya
- **Coverage**: Provinsi dan Kabupaten/Kota dalam provinsi tersebut

#### **Kabupaten/Kota (prov≠"00", kab≠"00")**
- **Dashboard**: Hanya project untuk kabupaten/kota spesifik tersebut
- **Monitoring**: Hanya project dan coverage untuk kabupaten/kota tersebut
- **Coverage**: Kabupaten/Kota spesifik saja

## 3. Implementasi Technical

### API Updates
- **`fetchAvailableProjects`**: Menerapkan filter coverage berdasarkan user role
- **`fetchDashboardData`**: Filter project scope sesuai level akses user
- Konsistensi filter di semua endpoint yang relevan

### Frontend Updates

#### Dashboard (index.php)
```javascript
// Filter otomatis berdasarkan user coverage
const loadFilterOptions = async () => {
  const projectsResponse = await makeAjaxRequest(API_URL, {
    action: "fetchAvailableProjects",
    user_prov: currentUser.prov,
    user_kab: currentUser.kab,
    year: "2025"  // Hardcode sesuai requirement
  });
  // ...
};
```

#### Monitoring (monitoring.php) 
```javascript
// Filter coverage wilayah sesuai role user
const loadRegions = async () => {
  // Filter berdasarkan role user
  if (currentUser.prov === "00" && currentUser.kab === "00") {
    // Pusat - bisa lihat semua
    filteredData = response.data || [];
  } else if (currentUser.prov !== "00" && currentUser.kab === "00") {
    // Provinsi - hanya provinsi dan kabupaten dalam provinsinya
    filteredData = (response.data || []).filter(cov => 
      cov.prov === currentUser.prov || (cov.prov === currentUser.prov && cov.kab !== "00")
    );
  } else {
    // Kabupaten - hanya kabupaten spesifik
    filteredData = (response.data || []).filter(cov => 
      cov.prov === currentUser.prov && cov.kab === currentUser.kab
    );
  }
  // ...
};
```

## 4. Security Features

### Authentication
- **Login Validation**: Cek keberadaan dan validitas data user di localStorage
- **Auto Logout**: Redirect otomatis jika session tidak valid
- **Logout Confirmation**: Konfirmasi sebelum logout untuk mencegah logout tidak sengaja

### Authorization
- **Role-based Access**: Filter data berdasarkan level akses user
- **Coverage Restriction**: User hanya bisa melihat data sesuai coverage wilayahnya
- **Consistent Filtering**: Filter diterapkan di semua endpoint yang relevan

## 5. User Experience

### Navigation
- **Unified Navbar**: Navbar konsisten di dashboard dan monitoring
- **User Avatar**: Display avatar dan info user
- **Easy Navigation**: Link antar halaman dashboard dan monitoring

### Tahun 2025 Focus
- **Auto Selection**: Tahun 2025 otomatis dipilih di semua filter
- **Simplified Interface**: Interface lebih bersih fokus pada tahun 2025
- **Consistent Year**: Semua query menggunakan tahun 2025

## 6. Testing Scenarios

### Test Login Required
1. Akses `index.php` tanpa login → Redirect ke `login.php`
2. Akses `monitoring.php` tanpa login → Redirect ke `login.php`
3. Login dengan user valid → Bisa akses kedua halaman

### Test Coverage Filter

#### User Pusat (00, 00)
1. Login sebagai user pusat
2. Dashboard: Melihat semua project Indonesia
3. Monitoring: Opsi coverage "Pusat - Nasional" + semua provinsi

#### User Provinsi (contoh: 32, 00)
1. Login sebagai user provinsi Jawa Barat
2. Dashboard: Hanya project untuk Jawa Barat dan kabupaten di dalamnya
3. Monitoring: Hanya coverage Provinsi Jawa Barat + kabupaten di Jawa Barat

#### User Kabupaten (contoh: 32, 04)
1. Login sebagai user Kabupaten Bandung
2. Dashboard: Hanya project untuk Kabupaten Bandung
3. Monitoring: Hanya coverage Kabupaten Bandung

## 7. Benefits

### Security
- ✅ **Protected Access**: Semua halaman utama memerlukan autentikasi
- ✅ **Role-based Security**: Data filtering berdasarkan role user
- ✅ **Consistent Authorization**: Filter diterapkan konsisten di semua endpoint

### User Experience  
- ✅ **Seamless Navigation**: Perpindahan antar halaman yang mudah
- ✅ **Relevant Data**: User hanya melihat data yang relevan dengan tugasnya
- ✅ **Clean Interface**: Interface yang bersih dan fokus pada tahun 2025

### Performance
- ✅ **Optimized Queries**: Query hanya mengambil data sesuai coverage user
- ✅ **Reduced Data**: Mengurangi data yang tidak perlu di frontend
- ✅ **Faster Load**: Loading lebih cepat karena data lebih sedikit

## 8. Future Considerations

- Session timeout untuk keamanan tambahan
- Audit log untuk tracking akses user
- Advanced role management jika diperlukan
- Cache optimization untuk performa 