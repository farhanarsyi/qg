<?php
// demo_pegawai.php - Demo data pegawai BPS (untuk testing tanpa SSO aktual)
require_once 'sso_config.php';

startSession();

// Data dummy pegawai untuk demonstrasi (sesuai dengan format API Pegawai BPS)
$demo_pegawai_data = array(
    array(
        'username' => 'john.doe',
        'email' => 'john.doe@bps.go.id',
        'attributes' => array(
            'attribute-nama' => array('Dr. John Doe, S.St., M.Si.'),
            'attribute-nip-lama' => array('196501011990031001'),
            'attribute-nip' => array('199003010165010101'),
            'attribute-organisasi' => array('3100710010000'), // Contoh: DKI Jakarta Pusat
            'attribute-provinsi' => array('DKI Jakarta'),
            'attribute-kabupaten' => array('Jakarta Pusat'),
            'attribute-golongan' => array('IV/c'),
            'attribute-jabatan' => array('Kepala Badan Pusat Statistik'),
            'attribute-eselon' => array('I.a'),
            'attribute-foto' => array('https://example.com/foto.jpg'),
            // Fallback format (tanpa attribute- prefix)
            'nama' => array('Dr. John Doe, S.St., M.Si.'),
            'email' => array('john.doe@bps.go.id'),
            'nip' => array('196501011990031001'),
            'nipBaru' => array('199003010165010101'),
            'kodeOrganisasi' => array('3100710010000'),
            'kodeProvinsi' => array('31'),
            'kodeKabupaten' => array('71'),
            'alamatKantor' => array('Jl. Dr. Sutomo No. 6-8 Jakarta 10710'),
            'provinsi' => array('DKI Jakarta'),
            'kabupaten' => array('Jakarta Pusat'),
            'golongan' => array('IV/c'),
            'jabatan' => array('Kepala Badan Pusat Statistik'),
            'eselon' => array('I.a'),
            'urlFoto' => array('https://example.com/foto.jpg')
        )
    )
);

// Data user info dummy
$demo_user_info = array(
    'preferred_username' => 'john.doe',
    'name' => 'Dr. John Doe, S.St., M.Si.',
    'email' => 'john.doe@bps.go.id',
    'sub' => 'demo-user-id'
);

// Siapkan data pegawai dengan unit kerja sesuai format yang baru
$kodeOrganisasi = '3100710010000'; // Contoh kode organisasi lengkap
$demo_prepared_data = array(
    "nama" => 'Dr. John Doe, S.St., M.Si.',
    "email" => 'john.doe@bps.go.id',
    "username" => 'john.doe',
    "nip" => '196501011990031001',
    "nipbaru" => '199003010165010101',
    "kodeorganisasi_full" => $kodeOrganisasi,
    "kodeorganisasi" => substr($kodeOrganisasi, 7, 5), // 5 digit terakhir: 10000
    "kodeprovinsi" => '31',
    "kodekabupaten" => '71',
    "alamatkantor" => 'DKI Jakarta',
    "provinsi" => 'DKI Jakarta',
    "kabupaten" => 'Jakarta Pusat',
    "golongan" => 'IV/c',
    "jabatan" => 'Kepala Badan Pusat Statistik',
    "foto" => 'https://example.com/foto.jpg',
    "eselon" => 'I.a',
);

// Tentukan unit kerja untuk demo
$unit_kerja = determineUnitKerja($kodeOrganisasi);
$demo_prepared_data['unit_kerja'] = $unit_kerja;

// Simpan ke session untuk demo
$_SESSION['user_data'] = array_merge($demo_prepared_data, array(
    'access_token' => 'demo-access-token',
    'refresh_token' => 'demo-refresh-token',
    'user_info' => $demo_user_info,
    'pegawai_data' => $demo_pegawai_data,
    'login_time' => time()
));

// Redirect ke profile
header('Location: profile.php');
exit;
?> 