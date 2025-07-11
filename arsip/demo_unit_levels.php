<?php
// demo_unit_levels.php - Demo berbagai level unit kerja BPS
require_once 'sso_config.php';

startSession();

// Parameter untuk memilih level demo
$level = $_GET['level'] ?? 'kabupaten';

switch ($level) {
    case 'pusat':
        $kodeOrganisasi = '0000000010000'; // Kode untuk pusat
        $demo_data = array(
            "nama" => 'Dr. Margo Yuwono, M.Si.',
            "email" => 'margo.yuwono@bps.go.id',
            "username" => 'margo.yuwono',
            "nip" => '196301011985031001',
            "nipbaru" => '198503010163010101',
            "kodeorganisasi_full" => $kodeOrganisasi,
            "kodeorganisasi" => substr($kodeOrganisasi, 7, 5),
            "kodeprovinsi" => '00',
            "kodekabupaten" => '00',
            "alamatkantor" => 'Jakarta',
            "provinsi" => 'DKI Jakarta',
            "kabupaten" => 'Jakarta Pusat',
            "golongan" => 'IV/e',
            "jabatan" => 'Kepala Badan Pusat Statistik',
            "foto" => '',
            "eselon" => 'I.a',
        );
        break;
        
    case 'provinsi':
        $kodeOrganisasi = '3200000070000'; // Kode untuk Provinsi Jawa Barat
        $demo_data = array(
            "nama" => 'Drs. Ahmad Sodikin, M.Si.',
            "email" => 'ahmad.sodikin@bps.go.id',
            "username" => 'ahmad.sodikin',
            "nip" => '196505121990031002',
            "nipbaru" => '199003020165051201',
            "kodeorganisasi_full" => $kodeOrganisasi,
            "kodeorganisasi" => substr($kodeOrganisasi, 7, 5),
            "kodeprovinsi" => '32',
            "kodekabupaten" => '00',
            "alamatkantor" => 'Jawa Barat',
            "provinsi" => 'Jawa Barat',
            "kabupaten" => 'Bandung',
            "golongan" => 'IV/d',
            "jabatan" => 'Kepala BPS Provinsi Jawa Barat',
            "foto" => '',
            "eselon" => 'II.a',
        );
        break;
        
    case 'kabupaten':
    default:
        $kodeOrganisasi = '3273000080000'; // Kode untuk Kabupaten Bandung
        $demo_data = array(
            "nama" => 'Dra. Siti Nurhaliza, M.M.',
            "email" => 'siti.nurhaliza@bps.go.id',
            "username" => 'siti.nurhaliza',
            "nip" => '197208151995032001',
            "nipbaru" => '199503201972081501',
            "kodeorganisasi_full" => $kodeOrganisasi,
            "kodeorganisasi" => substr($kodeOrganisasi, 7, 5),
            "kodeprovinsi" => '32',
            "kodekabupaten" => '73',
            "alamatkantor" => 'Jawa Barat',
            "provinsi" => 'Jawa Barat',
            "kabupaten" => 'Kabupaten Bandung',
            "golongan" => 'IV/c',
            "jabatan" => 'Kepala BPS Kabupaten Bandung',
            "foto" => '',
            "eselon" => 'III.a',
        );
        break;
}

// Data user info dummy
$demo_user_info = array(
    'preferred_username' => $demo_data['username'],
    'name' => $demo_data['nama'],
    'email' => $demo_data['email'],
    'sub' => 'demo-user-id'
);

// Data pegawai dummy
$demo_pegawai_data = array(
    array(
        'username' => $demo_data['username'],
        'email' => $demo_data['email'],
        'attributes' => array(
            // Format dengan attribute- prefix (format BPS yang benar)
            'attribute-nama' => array($demo_data['nama']),
            'attribute-nip-lama' => array($demo_data['nip']),
            'attribute-nip' => array($demo_data['nipbaru']),
            'attribute-organisasi' => array($demo_data['kodeorganisasi_full']),
            'attribute-provinsi' => array($demo_data['provinsi']),
            'attribute-kabupaten' => array($demo_data['kabupaten']),
            'attribute-golongan' => array($demo_data['golongan']),
            'attribute-jabatan' => array($demo_data['jabatan']),
            'attribute-eselon' => array($demo_data['eselon']),
            'attribute-foto' => array($demo_data['foto']),
            // Fallback format (tanpa attribute- prefix)
            'nama' => array($demo_data['nama']),
            'email' => array($demo_data['email']),
            'nip' => array($demo_data['nip']),
            'nipBaru' => array($demo_data['nipbaru']),
            'kodeOrganisasi' => array($demo_data['kodeorganisasi_full']),
            'kodeProvinsi' => array($demo_data['kodeprovinsi']),
            'kodeKabupaten' => array($demo_data['kodekabupaten']),
            'alamatKantor' => array($demo_data['alamatkantor']),
            'provinsi' => array($demo_data['provinsi']),
            'kabupaten' => array($demo_data['kabupaten']),
            'golongan' => array($demo_data['golongan']),
            'jabatan' => array($demo_data['jabatan']),
            'eselon' => array($demo_data['eselon']),
            'urlFoto' => array($demo_data['foto'])
        )
    )
);

// Tentukan unit kerja
$unit_kerja = determineUnitKerja($demo_data['kodeorganisasi_full']);
$demo_data['unit_kerja'] = $unit_kerja;

// Simpan ke session untuk demo
$_SESSION['user_data'] = array_merge($demo_data, array(
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