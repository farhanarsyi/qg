# Perbaikan Masalah Wilayah dengan Nama Null

## Masalah
Pada halaman monitoring, ketika project 110 dipilih, response API untuk wilayah cakupan memiliki beberapa wilayah dengan `name: null`:

```json
{
  "status": true,
  "data": [
    {
      "id_project": 110,
      "prov": "11",
      "kab": "00", 
      "name": "ACEH",
      "user_created": "ramelia",
      "date_created": "2025-04-22 15:26:16",
      "user_modified": "ramelia", 
      "date_modified": "2025-04-22 15:26:16"
    },
    {
      "id_project": 110,
      "prov": "12",
      "kab": "00",
      "name": null,  // ← Masalah di sini
      "user_created": "ramelia",
      "date_created": "2025-04-22 15:28:46",
      "user_modified": "ramelia",
      "date_modified": "2025-04-22 15:28:46"
    },
    {
      "id_project": 110,
      "prov": "13", 
      "kab": "00",
      "name": null,  // ← Masalah di sini
      "user_created": "ramelia",
      "date_created": "2025-04-22 15:30:07",
      "user_modified": "ramelia",
      "date_modified": "2025-04-22 15:30:07"
    }
  ]
}
```

Ketika user memilih wilayah dengan nama null, muncul error:
```
Terjadi Kesalahan
Cannot read properties of null (reading 'split')
```

## Solusi

### 1. Modifikasi API (api.php)

#### A. Menambahkan fungsi getNamaDaerah()
```php
// Function to get nama daerah from daftar_daerah.csv
function getNamaDaerah($kode) {
    static $daerah_map = null;
    
    // Load CSV file only once
    if ($daerah_map === null) {
        $daerah_map = [];
        $csv_file = 'daftar_daerah.csv';
        
        if (file_exists($csv_file)) {
            $handle = fopen($csv_file, 'r');
            if ($handle) {
                // Skip header
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 2) {
                        $kode_daerah = $data[0];
                        $nama_daerah = $data[1];
                        $daerah_map[$kode_daerah] = $nama_daerah;
                    }
                }
                fclose($handle);
            }
        }
    }
    
    return isset($daerah_map[$kode]) ? $daerah_map[$kode] : "Wilayah " . $kode;
}
```

#### B. Memodifikasi case "fetchCoverages"
```php
case "fetchCoverages":
    $id_project = $_POST['id_project'];
    
    $query = "SELECT * FROM [project_coverages] WHERE [id_project] = ? ORDER BY [prov], [kab]";
    
    $conn = getConnection();
    if ($conn === null) {
        echo json_encode(["status" => false, "message" => "Connection failed"]);
        break;
    }
    
    $stmt = sqlsrv_query($conn, $query, [$id_project]);
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
        sqlsrv_close($conn);
        echo json_encode(["status" => false, "message" => $errorMsg]);
        break;
    }
    
    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Jika name null, cari dari daftar_daerah.csv
        if ($row['name'] === null) {
            $kode = $row['prov'] . $row['kab'];
            $nama_daerah = getNamaDaerah($kode);
            $row['name'] = $nama_daerah;
        }
        $data[] = $row;
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    echo json_encode(["status" => true, "data" => $data]);
    break;
```

### 2. Modifikasi Frontend (monitoring.php)

#### A. Memperbaiki penanganan nama wilayah null di JavaScript
```javascript
// Sebelum (error):
const capitalizedName = region.name.split(' ').map(word => 
  word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
).join(' ');

// Sesudah (aman):
let capitalizedName = "Wilayah Tidak Diketahui";
if (region.name && region.name.trim() !== '') {
  capitalizedName = region.name.split(' ').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
  ).join(' ');
}
```

#### B. Memperbaiki penanganan split pada nama kabupaten
```javascript
// Sebelum (error):
if (kabName.includes(' - ')) {
  provinceName = kabName.split(' - ')[0];
}

// Sesudah (aman):
if (kabName && kabName.trim() !== '' && kabName.includes(' - ')) {
  provinceName = kabName.split(' - ')[0];
}
```

## Hasil

1. **API Response**: Sekarang ketika ada wilayah dengan `name: null`, API akan otomatis mencari nama wilayah dari `daftar_daerah.csv` berdasarkan kode provinsi dan kabupaten.

2. **Frontend**: JavaScript sekarang menangani kasus ketika nama wilayah null atau kosong, sehingga tidak terjadi error "Cannot read properties of null (reading 'split')".

3. **User Experience**: User dapat memilih wilayah tanpa error, dan nama wilayah akan ditampilkan dengan benar.

## Testing

Fungsi telah diuji dengan data test:
- Kode 1100 → ACEH ✓
- Kode 1200 → SUMATERA UTARA ✓  
- Kode 1300 → SUMATERA BARAT ✓
- Kode 9999 → "Wilayah 9999" ✓ (fallback untuk kode yang tidak ada)

## File yang Dimodifikasi

1. `api.php` - Menambahkan fungsi getNamaDaerah() dan memodifikasi fetchCoverages
2. `monitoring.php` - Memperbaiki penanganan nama wilayah null di JavaScript
3. `daftar_daerah.csv` - Referensi untuk mapping kode wilayah ke nama wilayah 