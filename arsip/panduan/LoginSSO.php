<?php

namespace App\Controllers;

use App\Libraries\PegawaiSimpeg;
use App\Libraries\PegawaiSSO;
use App\Libraries\Sesi;
use App\Models\ArsipSimpegModel;
use App\Models\NominasiDetailModel;
use App\Models\NominasiSyaratModel;
use App\Models\PenggunaModel;
use App\Models\PeriodeModel;
use App\Models\SksNominasiModel;
use Exception as GlobalException;
use JKD\SSO\Client\Provider\Keycloak;

class LoginSSO extends BaseController
{
    public $uri_untuk_login_sso = 'login_sso_bps';

    public function index()
    {
        $session = \Config\Services::session();

        $provider = new Keycloak(
            [
                'authServerUrl'         => 'https://sso.bps.go.id',
                'realm'                 => 'pegawai-bps',
                'clientId'              => '03340-sks-er5',
                'clientSecret'          => 'e59ec3aa-dcdc-489c-85e8-6d5efd15ead7',
                'redirectUri'           => base_url() . '/login_sso_bps'
            ]
        );

        if (!isset($_GET['code'])) {

            // Untuk mendapatkan authorization code
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);
            exit;

            // Mengecek state yang disimpan saat ini untuk memitigasi serangan CSRF
        } elseif (empty($_GET['state']) || !isset($_SESSION['oauth2state']) || (isset($_SESSION['oauth2state']) && ($_GET['state'] !== $_SESSION['oauth2state']))) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {

            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                $bearer = $provider->getBearer($token);
                $apiToken = substr($bearer['Authorization'], 7);
            } catch (GlobalException $e) {
                exit('Gagal mendapatkan akses token : ' . $e->getMessage());
            }

            // Opsional: Setelah mendapatkan token, anda dapat melihat data profil pengguna
            try {
                $user = $provider->getResourceOwner($token);
                $url_logout = $provider->getLogoutUrl();
                session()->set('url_logout', $url_logout);

                // menyimpan session
                $data = [
                    "nama" => $user->getName(),
                    "email" => $user->getEmail(),
                    "username" => $user->getUsername(),
                    "nip" => $user->getNip(),
                    "nipbaru" => $user->getNipBaru(),
                    "kodeorganisasi" => substr($user->getKodeOrganisasi(), 7, 5),
                    "kodeprovinsi" => $user->getKodeProvinsi(),
                    "kodekabupaten" => $user->getKodeKabupaten(),
                    // "alamatkantor" => $user->getAlamatKantor(),
                    "alamatkantor" => $user->getProvinsi(),
                    "provinsi" => $user->getProvinsi(),
                    "kabupaten" => $user->getKabupaten(),
                    "golongan" => $user->getGolongan(),
                    "jabatan" => $user->getJabatan(),
                    "foto" => $user->getUrlFoto(),
                    "eselon" => $user->getEselon(),
                    "active" => 1,
                    "apiToken" => "UXFFQUUxak5MSDlkUWlXaFNMN01MZz09." . $apiToken,
                ];

                // mengambil periode aktif
                $dataPeriode = new PeriodeModel();
                $periode = $dataPeriode->getPeriodeActive();
                $periode = $periode[0];
                $data['periode'] = $periode->tahun;
                $data['periodeDetail'] = $periode;
                $libsesi = new Sesi();
                $isOpen = $libsesi->isOpen($periode->ket);
                if ($isOpen == 3) {
                    $data['alert-ujicoba'] = "Aplikasi SKS masih dalam tahap percobaan, Bapak/Ibu dapat melalukan uji coba terlebih dahulu, apabila Bapak/Ibu menemukan kesalahan dalam aplikasi silakan dilaporkan melalui http://s.bps.go.id/ErrorSKS untuk selanjutnya kami perbaiki. Terimakasih atas kerjasamanya.";
                }

                $roleuser = array();
                $roleactive = array();

                $nipPengganti = null;
                
                // mengecek nip di daftar nominasi
                $dataNom = new SksNominasiModel();
                $nom = $dataNom->getByNip($user->getNip());
                if (in_array($user->getNip(),['340060079','340053223'])) $nom = $dataNom->getByNip($nipPengganti);

                // mengecek nip di daftar pengguna
                $dataPengguna = new PenggunaModel();
                $datauser = $dataPengguna->getRoleByNip($user->getNip());
                if (in_array($user->getNip(),['340060079','340053223']) && $nipPengganti != null) {
                    if ($user->getNip() != $nipPengganti) {
                        $datauser = $dataPengguna->getRoleByNip($nipPengganti);
                
                        $libsso = new PegawaiSSO();
                        $sso = $libsso->getPegawaiByNip($nipPengganti);
                        $sso = $sso[0];
                        $ssoatt = $sso['attributes'];

                        $data["nama"] = $ssoatt['attribute-nama'][0];
                        $data["email"] = $sso['email'];
                        $data["nip"] = $ssoatt['attribute-nip-lama'][0];
                        $data["nipbaru"] = $ssoatt['attribute-nip'][0];
                        $data["kodeorganisasi"] = substr($ssoatt['attribute-organisasi'][0], 7, 5);
                        $data["kodeprovinsi"] = substr($ssoatt['attribute-organisasi'][0], 0, 2);
                        $data["kodekabupaten"] = substr($ssoatt['attribute-organisasi'][0], 2, 2);
                        // $data["alamatkantor"] = $ssoatt['attribute-alamat-kantor'][0];
                        $data["alamatkantor"] = $ssoatt['attribute-provinsi'][0];
                        $data["provinsi"] = $ssoatt['attribute-provinsi'][0];
                        $data["kabupaten"] = $ssoatt['attribute-kabupaten'][0];
                        $data["golongan"] = $ssoatt['attribute-golongan'][0];
                        $data["jabatan"] = $ssoatt['attribute-jabatan'][0];
                        $data["foto"] = $ssoatt['attribute-foto'][0];
                        $data["eselon"] = $ssoatt['attribute-eselon'][0];
                    }
                }
                // print_r($nom);
                if (count($nom) == 0) {
                    $roleuser = $datauser;
                    if (count($roleuser) > 0)
                        $roleactive = $roleuser[count($roleuser) - 1];
                    else session()->set('notfound', "Akun Bapak/Ibu tidak terdaftar sebagai pengguna SKS saat ini ");
                } else {
                    $nom = $nom[0];
                    if (in_array($user->getNip(),['340060079','340053223'])) print_r($nom);
                    if ($nom->isopen == $isOpen
                    // || count($datauser) > 0
                    ) {
                        // mengambil role nominasi
                        $rpegawai = $dataPengguna->getPegawaiRole();
                        $roleuser = count($datauser) > 0 ? array_merge($rpegawai, $datauser) : $rpegawai;
                        $roleactive = count($roleuser) > 0 ?
                        (count($rpegawai) == 0 ? $roleuser[count($roleuser) - 1] : $rpegawai[0])
                        : session()->set('notfound', "Data akun tidak terambil, silakan login kembali");
                        
                        // mengambil data nominasi
                        if (session()->get('notfound') == null) {
                            $data['nom_id'] = $nom->id;
                            $data['nom_status'] = $nom->status;
                            $data['nom_stproses'] = $nom->status_proses;
                            $data['nom_jenisusulan'] = $nom->jenisusulan;
                            $data['nom_ketstatus'] = $nom->ket_status;
                            $data['nom_ketstproses'] = $nom->ket_proses;
                            $data['nom_niplama'] = $nom->niplama;

                            // cek simpeg data arsip
                            $datasimpeg = new ArsipSimpegModel();
                            $libsimpeg = new PegawaiSimpeg();
                            $dsimpeg = $libsimpeg->getDataArsip($data['apiToken'], $nom->niplama);
                            if ($dsimpeg == null) session()->set('notfound', 'Mohon pastikan VPN aktif');
                            $arsip = array();
                            foreach ($dsimpeg as $d) {
                                $arsip[] = [
                                    'niplama' => $d->idbps,
                                    'path' => $d->path,
                                    'nmfile' => $d->nmfile,
                                    'jenis' => $d->jenis
                                ];
                            }

                            $inst = $datasimpeg->add($arsip, $nom->niplama);
                            if (count($inst) > 0) {
                                $data['arsipSimpeg'] = $inst;
                            }

                            // cek nominasi detail
                            $dataDetail = new NominasiDetailModel();
                            $detail = $dataDetail->getByNip($nom->niplama);
                            if (count($detail) == 0) {
                                $data['cek_nomdetail'] = 0;
                                $data['openModalRefresh'] = 1;
                            } else {
                                $data['cek_nomdetail'] = 1;
                                $data['nomdetail'] = $detail;
                                $data['openModalRefresh'] = 0;
                            }

                            // cek nominasi syarat
                            $dataSyarat = new NominasiSyaratModel();
                            $syarat = $dataSyarat->getByNip($nom->niplama);
                            if (count($syarat) == 0) {
                                $data['cek_nomsyarat'] = 0;
                                $data['cek_syarat'] = 8;
                                $data['openModalRefresh'] = 1;
                            } else {
                                $data['cek_nomsyarat'] = count($syarat);
                                $data['cek_syarat'] = 8;
                                $data['nomsyarat'] = $syarat;
                                $data['openModalRefresh'] = 0;
                            }
                        }
                    } else {
                        session()->set('notfound', "Silakan menghubungi PIC satker apabila Bapak/Ibu belum terdaftar");
                        // session()->set('notfound', "Akses kami tutup sementara, untuk melakukan update");
                    }
                }

                if (count($roleuser) > 0) {
                    $data['roles'] = $roleuser;
                    $data['roleActive'] = $roleactive->id;
                    foreach ($roleuser as $a) {
                        if ($a-> id == 1) {
                            $data['roleAdmin'] = 1;
                        }
                    }
                    $data['nmroleActive'] = $roleactive->nama;
                } else {
                    session()->set('notfound', "Bapak/Ibu tidak terdaftar, silakan menghubungi PIC satker apabila Bapak/Ibu belum terdaftar");
                }
                
                
                if (session()->get('notfound') != null) {
                    throw new \Exception(session()->get('notfound'));
                }

                session()->set($data);
                // if (!in_array(session()->get('nip'),['340060079','340053223'])) session()->set('notfound', "Akses kami tutup sementara, untuk melakukan update");

                return redirect()->to('/');
            } catch (\Exception $e) {

                if ($user->getNip() == "340060079") { exit('Gagal Mendapatkan Data Penggunas: ' . $e->getMessage());}
                // return redirect()->to('ups');
            }
        }
    }
}
