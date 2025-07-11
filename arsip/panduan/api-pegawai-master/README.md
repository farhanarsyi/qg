# API (Application Programming Interface) Pegawai BPS v2.0

## Penggunaan

Untuk menggunakan API Pegawai BPS ini anda membutuhkan parameter `Client-Id` dan `Client-Secret` SSO (Single Sign-On) BPS. Parameter ini didapatkan dari Subdirektorat JKD. Secara umum penggunaan API Pegawai v2 ini sangat mirip dengan versi sebelumnya, perbedaannya hanya pada URL API dan Query dengan tetap membutuhkan token bearer.

Apabila anda telah menggunakan SSO BPS untuk PHP, maka anda dapat menggunakan token yang telah didapatkan dan langsung menuju ke [tahap2](https://git.bps.go.id/jkd-repo/api-pegawai#tahap-2-mendapatkan-data-pegawai-dengan-username-tertentu) pada panduan ini. Instalasi dan contoh penggunaan SSO BPS untuk PHP dapat dilihat pada project [disini](https://git.bps.go.id/jkd-repo/sso-php).

## Contoh Kode Menggunakan PHP

### Inisiasi
```php
$url_base       = 'https://sso.bps.go.id/auth/';
$url_token      = $url_base.'realms/pegawai-bps/protocol/openid-connect/token';
$url_api        = $url_base.'realms/pegawai-bps/api-pegawai';
$client_id      = '{client-id}'; 
$client_secret  = '{client-secret}'; 
```

### Query Pencarian
```php
//Mencari pengguna berdasarkan Username
$query_search   = '/username/{username}';

//Mencari pengguna berdasarkan Email
$query_search   = '/email/{email}';

//Mencari pengguna berdasarkan NIP
$query_search   = '/nip/{nip}';

//Mencari pengguna berdasarkan NIP Baru
$query_search   = '/nipbaru/{nipbaru}';

//Mencari pengguna berdasarkan Kode Unit Organisasi
$query_search   = '/unit/{kodeunitorganisasi}';
```

### Tahap 1 : Mendapatkan akses token

```php
$ch = curl_init($url_token);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($ch, CURLOPT_POSTFIELDS,"grant_type=client_credentials");
curl_setopt($ch, CURLOPT_USERPWD, $client_id . ":" . $client_secret);  
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response_token = curl_exec($ch);
if(curl_errno($ch)){
    throw new Exception(curl_error($ch));
}
curl_close ($ch);
$json_token = json_decode($response_token, true);
$access_token = $json_token['access_token'];
```

### Tahap 2 : Mendapatkan data pegawai dengan username tertentu

```php
$ch = curl_init($url_api.$query_search);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , 'Authorization: Bearer '.$access_token ));  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if(curl_errno($ch)){
    throw new Exception(curl_error($ch));
}
curl_close ($ch);
$json = json_decode($response, true);

echo "Hasil Pencarian <b>$query_search </b><hr>";
$i=1;
foreach ($json as $result){
    echo "<br>$i : Username : ".$result['username']."<ul>";
    foreach ($result['attributes'] as $key => $value){
        echo "<li><i>".$key."</i>: <br>". $value[0]."</li>";
    }
    echo "</ul>";
    $i++;
   
}
```

## 