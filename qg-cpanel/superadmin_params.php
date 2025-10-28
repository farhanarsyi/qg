<?php
// superadmin_params.php - Halaman khusus untuk superadmin memodifikasi parameter SSO

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

try {
    require_once 'sso_integration.php';
    require_once 'app_config.php';
    
    // Pastikan user sudah login SSO
    requireSSOLogin('superadmin_params.php');
    
    // Dapatkan data user
    $user_data = getUserData();
    
    // Cek apakah user adalah superadmin
    $username = $user_data['username'] ?? '';
    $is_superadmin = ($username === 'farhan.arsyi');
    
    if (!$is_superadmin) {
        // Redirect ke halaman utama jika bukan superadmin
        header('Location: index.php');
        exit;
    }
    
    // Proses form jika ada submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        $unit_kerja = $_POST['unit_kerja'] ?? 'pusat';
        $kode_provinsi = $_POST['kode_provinsi'] ?? '00';
        $kode_kabupaten = $_POST['kode_kabupaten'] ?? '00';
        $nama_provinsi = $_POST['nama_provinsi'] ?? '';
        $nama_kabupaten = $_POST['nama_kabupaten'] ?? '';
        
        // Simpan parameter yang dimodifikasi ke session
        $_SESSION['modified_sso_params'] = [
            'unit_kerja' => $unit_kerja,
            'kodeprovinsi' => $kode_provinsi,
            'kodekabupaten' => $kode_kabupaten,
            'provinsi' => $nama_provinsi,
            'kabupaten' => $nama_kabupaten,
            'is_modified' => true
        ];
        
        // Redirect ke halaman utama
        header('Location: index.php?refresh=true');
        exit;
    }
    
    // Ambil daftar provinsi dan kabupaten dari file JSON
    $daerah_json = file_get_contents('daftar_daerah.json');
    $daerah_data = json_decode($daerah_json, true);
    
    // Proses data daerah untuk dropdown
    $provinsi_list = [];
    $kabupaten_list = [];
    
    foreach ($daerah_data as $daerah) {
        $kode = $daerah['kode'];
        $nama = $daerah['daerah'];
        
        // Jika kode daerah 4 digit dan berakhir dengan "00", ini adalah provinsi
        if (strlen($kode) === 4 && substr($kode, -2) === "00") {
            $provinsi_list[] = [
                'kode' => substr($kode, 0, 2),
                'nama' => $nama
            ];
        } else if (strlen($kode) === 4) {
            // Ini adalah kabupaten/kota
            $kode_prov = substr($kode, 0, 2);
            $kode_kab = substr($kode, 2, 2);
            
            if (!isset($kabupaten_list[$kode_prov])) {
                $kabupaten_list[$kode_prov] = [];
            }
            
            $kabupaten_list[$kode_prov][] = [
                'kode' => $kode_kab,
                'nama' => $nama
            ];
        }
    }
    
} catch (Exception $e) {
    // Jika terjadi error, tampilkan pesan error
    echo "<!DOCTYPE html><html><head><title>Error - Superadmin</title></head><body>";
    echo "<h1>🚨 Superadmin Error</h1>";
    echo "<p>Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='index.php'>🏠 Kembali</a></p>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Superadmin - Modifikasi Parameter SSO</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root {
      --primary-color: #059669;   /* Emerald green */
      --primary-hover: #047857;   /* Darker emerald */
      --primary-light: #d1fae5;   /* Light emerald */
      --success-color: #10b981;   /* Success green */
      --warning-color: #f59e0b;   /* Amber */
      --danger-color: #ef4444;    /* Red */
      --neutral-color: #6b7280;   /* Gray */
      --light-color: #f9fafb;     /* Light gray */
      --dark-color: #111827;      /* Dark gray */
      --border-color: #e5e7eb;    /* Border gray */
      --text-secondary: #374151;  /* Secondary text */
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
      color: var(--dark-color);
      line-height: 1.6;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
    
    .container {
      max-width: 800px;
      margin: 2rem auto;
      padding: 2rem;
    }
    
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.04);
      border: none;
      margin-bottom: 1.5rem;
      background-color: #ffffff;
      overflow: visible;
      transform: translateY(0);
      transition: all 0.3s ease;
    }
    
    .card-header {
      background-color: var(--primary-light);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 1.5rem;
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
    }
    
    .form-control, .form-select {
      border-radius: 6px;
      border: 1px solid var(--border-color);
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      background-color: #fff;
      transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
      outline: none;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 6px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
      background-color: var(--primary-hover);
      border-color: var(--primary-hover);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }
    
    .superadmin-badge {
      background-color: var(--warning-color);
      color: white;
      font-size: 0.8rem;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      margin-left: 0.5rem;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Modifikasi Parameter SSO <span class="superadmin-badge">SUPERADMIN</span></h3>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i> Halaman ini memungkinkan Anda untuk memodifikasi parameter SSO yang digunakan dalam sistem. Setelah parameter dimodifikasi, sistem akan berjalan dengan parameter tersebut.
        </div>
        
        <form method="POST" action="">
          <div class="mb-3">
            <label for="unit_kerja" class="form-label">Unit Kerja</label>
            <select class="form-select" id="unit_kerja" name="unit_kerja" required>
              <option value="pusat">Pusat</option>
              <option value="provinsi">Provinsi</option>
              <option value="kabupaten">Kabupaten</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="kode_provinsi" class="form-label">Provinsi</label>
            <select class="form-select" id="kode_provinsi" name="kode_provinsi" required>
              <option value="00">-- Pilih Provinsi --</option>
              <?php foreach ($provinsi_list as $prov): ?>
                <option value="<?= htmlspecialchars($prov['kode']) ?>" data-nama="<?= htmlspecialchars($prov['nama']) ?>">
                  <?= htmlspecialchars($prov['nama']) ?> (<?= htmlspecialchars($prov['kode']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" id="nama_provinsi" name="nama_provinsi" value="">
          </div>
          
          <div class="mb-3">
            <label for="kode_kabupaten" class="form-label">Kabupaten/Kota</label>
            <select class="form-select" id="kode_kabupaten" name="kode_kabupaten" disabled>
              <option value="00">-- Pilih Kabupaten/Kota --</option>
            </select>
            <input type="hidden" id="nama_kabupaten" name="nama_kabupaten" value="">
          </div>
          
          <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan & Lanjutkan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <script>
    // Data kabupaten untuk dropdown
    const kabupatenData = <?= json_encode($kabupaten_list) ?>;
    
    // Fungsi untuk mengisi dropdown kabupaten berdasarkan provinsi yang dipilih
    $(document).ready(function() {
      // Ketika unit kerja berubah
      $('#unit_kerja').change(function() {
        const unitKerja = $(this).val();
        
        if (unitKerja === 'pusat') {
          $('#kode_provinsi').val('00');
          $('#kode_kabupaten').val('00');
          $('#kode_provinsi').prop('disabled', true);
          $('#kode_kabupaten').prop('disabled', true);
          $('#nama_provinsi').val('');
          $('#nama_kabupaten').val('');
        } else if (unitKerja === 'provinsi') {
          $('#kode_provinsi').prop('disabled', false);
          $('#kode_kabupaten').val('00');
          $('#kode_kabupaten').prop('disabled', true);
          $('#nama_kabupaten').val('');
        } else {
          $('#kode_provinsi').prop('disabled', false);
          $('#kode_kabupaten').prop('disabled', false);
        }
      });
      
      // Ketika provinsi berubah
      $('#kode_provinsi').change(function() {
        const kodeProvinsi = $(this).val();
        const namaProvinsi = $(this).find('option:selected').data('nama') || '';
        $('#nama_provinsi').val(namaProvinsi);
        
        // Reset kabupaten
        $('#kode_kabupaten').empty();
        $('#kode_kabupaten').append('<option value="00">-- Pilih Kabupaten/Kota --</option>');
        $('#nama_kabupaten').val('');
        
        if (kodeProvinsi === '00') {
          $('#kode_kabupaten').prop('disabled', true);
          return;
        }
        
        // Isi dropdown kabupaten
        const kabupatenList = kabupatenData[kodeProvinsi] || [];
        if (kabupatenList.length > 0) {
          $('#kode_kabupaten').prop('disabled', false);
          
          // Tambahkan opsi kabupaten
          kabupatenList.forEach(function(kab) {
            $('#kode_kabupaten').append(
              `<option value="${kab.kode}" data-nama="${kab.nama}">${kab.nama} (${kab.kode})</option>`
            );
          });
        } else {
          $('#kode_kabupaten').prop('disabled', true);
        }
      });
      
      // Ketika kabupaten berubah
      $('#kode_kabupaten').change(function() {
        const namaKabupaten = $(this).find('option:selected').data('nama') || '';
        $('#nama_kabupaten').val(namaKabupaten);
      });
    });
  </script>
</body>
</html>