# Fix: Provincial User Access to Coverage Data

## Problem
User dengan level provinsi (contoh: prov=14, kab=00) memiliki project di sistem, tetapi ketika mengakses data coverage, tidak ditemukan data untuk level provinsi tersebut. Yang ada hanya data untuk kabupaten/kota di dalam provinsi (14,01, 14,02, dst).

Error yang muncul:
```
action: fetchCoverages
id_project: 83
-> "Gagal memuat data wilayah Anda. Silakan refresh halaman."
```

## Root Cause
Data coverage project tidak memiliki entry untuk level provinsi (XX,00), tetapi memiliki entry untuk kabupaten/kota (XX,01, XX,02, dst) dalam provinsi tersebut.

## Solution
Implementasi "Virtual Province" yang secara otomatis dibuat ketika:
1. User adalah level provinsi (prov≠00, kab=00)
2. Project memiliki coverage untuk kabupaten/kota dalam provinsi tersebut
3. Tetapi tidak ada coverage untuk level provinsi itu sendiri

### Changes Made

#### 1. Modifikasi `loadRegions()` Function
```javascript
} else if (currentUser.prov !== "00" && currentUser.kab === "00") {
  // User provinsi - cek apakah ada data untuk provinsi atau hanya kabupaten
  
  if (userProvince) {
    // Jika ada data provinsi, pilih provinsi (behavior normal)
    selectedRegion = userProvince.id;
  } else {
    // Jika tidak ada data provinsi, buat virtual region provinsi
    const virtualProvince = {
      id: `${currentUser.prov}00`,
      prov: currentUser.prov,
      kab: "00",
      name: provinceName,
      isVirtual: true // Mark sebagai virtual
    };
    
    coverageData.unshift(virtualProvince);
    selectedRegion = virtualProvince.id;
  }
}
```

#### 2. Modifikasi Logic dalam `$("#loadData").on('click')`
```javascript
} else if (regionData.kab === "00") {
  // Level Provinsi
  
  if (regionData.isVirtual) {
    // Untuk virtual province, hanya tampilkan kabupaten-kabupaten saja
    const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
    regionsToProcess = kabupatenList;
  } else {
    // Untuk provinsi normal, tampilkan provinsi + kabupaten
    regionsToProcess = [provinsi, ...kabupatenList];
  }
}
```

## Behavior Changes

### Before Fix
- User provinsi (14,00) dengan project tapi tanpa coverage provinsi → ERROR
- Sistem tidak bisa mengakses data

### After Fix
- User provinsi (14,00) → Virtual province dibuat otomatis
- Tampilan tabel hanya menampilkan kolom kabupaten/kota yang tersedia
- TIDAK menampilkan kolom provinsi (karena tidak ada data coverage)

## Generalization
Fix ini berlaku untuk **semua provinsi** yang mengalami kondisi yang sama:
- User level provinsi
- Project tersedia untuk provinsi tersebut  
- Coverage data hanya ada untuk kabupaten/kota, tidak ada untuk provinsi

## Example Case
**User:** prov=14, kab=00  
**Project:** ID 83 (tersedia untuk provinsi 14)  
**Coverage Data:** Hanya 14,01, 14,02, 14,03, dst (tidak ada 14,00)  

**Result:** 
- Virtual province "Provinsi (14)" dibuat
- Tabel monitoring menampilkan kolom untuk kabupaten 14,01, 14,02, 14,03
- Tidak ada kolom provinsi karena memang tidak ada data coverage

## Testing
1. Login sebagai user provinsi yang tidak memiliki coverage provinsi
2. Pilih project yang memiliki coverage untuk kabupaten dalam provinsi
3. Sistem harus berhasil memuat data dan menampilkan tabel dengan kolom kabupaten saja

Sistem sekarang dapat menangani skenario ini dengan graceful degradation. 