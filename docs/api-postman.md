# Dokumentasi API untuk Postman

Base URL:

```text
http://localhost:8000/api
```

Jika memakai Laragon virtual host, ganti menjadi:

```text
http://tunassiakanugrah.test/api
```

Header umum:

```http
Accept: application/json
Content-Type: application/json
```

Untuk endpoint yang membutuhkan login:

```http
Authorization: Bearer {{access_token}}
```

Untuk upload file/foto gunakan `multipart/form-data`, bukan JSON.

## Auth

### POST /login

Login API memakai `id` user/perusahaan.

Auth: tidak perlu token

Body JSON:

```json
{
  "id": "USER_ID",
  "password": "password"
}
```

Response sukses berisi `access_token` dan `token_type: Bearer`.

### POST /login-android

Login aplikasi mobile. Hanya role `inspektor` yang diizinkan.

Auth: tidak perlu token

Body JSON:

```json
{
  "username": "inspektor1",
  "password": "password"
}
```

### POST /register

Registrasi user baru. Jika `role` tidak dikirim, default menjadi `inspektor`.

Auth: tidak perlu token

Body JSON:

```json
{
  "username": "inspektor1",
  "email": "inspektor1@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "inspektor"
}
```

Validasi:

| Field | Rule |
| --- | --- |
| `username` | required, string, max 255, unique users |
| `email` | required, email, max 255, unique users |
| `password` | required, string, min 8 |
| `password_confirmation` | required, sama dengan password |
| `role` | nullable, string |

### POST /logout

Menghapus token saat ini.

Auth: Bearer token

Body: tidak ada

### GET /user

Mengambil data user yang sedang login.

Auth: Bearer token

Body: tidak ada

### PUT /user/update

Update akun user login.

Auth: Bearer token

Body JSON:

```json
{
  "username": "username_baru",
  "password": "password_baru"
}
```

Validasi di controller `App\Http\Controllers\Api\UserController`:

| Field | Rule |
| --- | --- |
| `username` | sometimes, required, string, unique users, hanya untuk role `inspektor` dan `supervisor_umum` |
| `password` | sometimes, required, min 4 |

Catatan penting: di `routes/api.php`, route ini saat ini bentrok. Ada route publik dan route `staff` yang mengarah ke `ApiLoginController::update`, padahal method `update` tidak ada. Route auth juga memakai `UserController::class` tetapi belum ada import `use App\Http\Controllers\Api\UserController;`. Sebaiknya diperbaiki sebelum dites di Postman.

## Barang / Inventory

### POST /barang

Tambah barang.

Auth: Bearer token

Body JSON:

```json
{
  "nama_barang": "APAR ABC",
  "jumlah_barang": 5,
  "tipe_barang": "APAR",
  "satuan": "unit",
  "kondisi": "baik",
  "berat_barang": 3,
  "merek_barang": "Yamato",
  "ukuran_barang": "3 Kg"
}
```

Validasi:

| Field | Rule |
| --- | --- |
| `nama_barang` | required, string, max 255 |
| `jumlah_barang` | required, integer, min 0 |
| `tipe_barang` | required, string |
| `satuan` | required, string |
| `kondisi` | required, string |
| `berat_barang` | nullable, numeric |
| `merek_barang` | nullable, string |
| `ukuran_barang` | nullable, string |

### GET /barangs

List inventory berbasis QR untuk APAR/HYDRANT.

Auth: Bearer token

Role: controller hanya mengizinkan `inspektor`.

Body: tidak ada

### GET /barang/ringkasan

Ringkasan jumlah barang, kondisi baik, perlu cek, APAR, dan HYDRANT.

Auth: Bearer token

Body: tidak ada

### GET /barang/detail/{qr}

Detail barang berdasarkan `nomor_identifikasi` QR.

Auth: Bearer token

Path parameter:

| Parameter | Contoh |
| --- | --- |
| `qr` | `APAR-001` |

Body: tidak ada

### GET /barang/{qrCodeData}

Scan QR dan validasi barang APAR/HYDRANT. Jika isi QR berupa teks panjang/multiline, URL encode nilainya saat dimasukkan ke path.

Auth: Bearer token

Path parameter:

| Parameter | Contoh |
| --- | --- |
| `qrCodeData` | `APAR-001` |

Body: tidak ada

## Laporan APK / Inspeksi

### POST /laporan-apk

Membuat laporan inspeksi APAR atau HYDRANT.

Auth: Bearer token

Content-Type: `multipart/form-data`

Field dasar:

| Field | Rule |
| --- | --- |
| `qr_code_data` | required, string |
| `tanggal_inspeksi` | required, date |
| `lokasi_alat` | required, string, max 255 |
| `foto` | nullable, file image, max 5120 KB |
| `kondisi_fisik` | required, salah satu: `Good`, `Korosif`, `Bad` |
| `catatan_tindakan` | nullable, string, max 500 |

Tambahan jika barang terdeteksi APAR:

| Field | Rule |
| --- | --- |
| `tindakan` | required, salah satu: `Good`, `Isi Ulang`, `Ganti` |
| `selang` | required, salah satu: `Good`, `Bad`, `Crack` |
| `pressure_gauge` | required, salah satu: `Good`, `Bad` |
| `safety_pin` | required, salah satu: `Good`, `Crack` |
| `catatan_tindakan` | wajib jika `tindakan` = `Ganti` |

Contoh APAR form-data:

```text
qr_code_data: APAR-001
tanggal_inspeksi: 2026-07-31
lokasi_alat: Gedung A Lantai 1
kondisi_fisik: Good
tindakan: Good
selang: Good
pressure_gauge: Good
safety_pin: Good
foto: pilih file gambar
```

Tambahan jika barang terdeteksi HYDRANT:

| Field | Rule |
| --- | --- |
| `tindakan` | required, salah satu: `Good`, `Broken`, `Repair` |
| `tekanan_air` | sometimes, nullable, salah satu: `Good`, `Low`, `Bad` |
| `katup` | sometimes, nullable, salah satu: `Good`, `Bad`, `Stuck` |
| `selang_hydrant` | sometimes, nullable, salah satu: `Good`, `Bad`, `Crack` |
| `catatan_tindakan` | wajib jika `tindakan` = `Repair` |

Contoh HYDRANT form-data:

```text
qr_code_data: HYD-001
tanggal_inspeksi: 2026-07-31
lokasi_alat: Area Parkir
kondisi_fisik: Good
tindakan: Good
tekanan_air: Good
katup: Good
selang_hydrant: Good
foto: pilih file gambar
```

### POST /laporan-apk/terakhir/{qrCode}

Mengambil inspeksi terakhir berdasarkan QR. Method yang terdaftar adalah POST walaupun hanya mengambil data.

Auth: Bearer token

Path parameter:

| Parameter | Contoh |
| --- | --- |
| `qrCode` | `APAR-001` |

Body: tidak ada

## Notifikasi

### GET /notifikasi

List notifikasi.

Auth: Bearer token

Role: controller hanya mengizinkan `inspektor`.

Body: tidak ada

### POST /notifikasi/generate

Generate notifikasi berdasarkan riwayat inspeksi barang.

Auth: Bearer token

Body: tidak ada

## Pengajuan Barang Masuk

### POST /pengajuan-barangs

Membuat pengajuan barang masuk. Field request bersifat dinamis mengikuti konfigurasi `master_data` berdasarkan `tipe_barang_kategori`.

Auth: Bearer token

Langkah yang disarankan di Postman:

1. Panggil `GET /form-configs/{form_type}` untuk melihat field yang dibutuhkan.
2. Kirim body ke `POST /pengajuan-barangs` dengan semua field required.

Field wajib minimal:

| Field | Rule |
| --- | --- |
| `tipe_barang_kategori` | required, string |
| `nama_barang` | wajib secara logic controller |

Contoh body untuk kategori `APAR` sesuai seeder:

```json
{
  "tipe_barang_kategori": "APAR",
  "nama_barang": "APAR ABC",
  "tipe_barang": "APAR",
  "jumlah_barang": 2,
  "jenis_barang": "Barang Jadi",
  "kondisi": "baik",
  "media": "Powder",
  "berat": "3 Kg",
  "satuan": "unit"
}
```

Contoh body untuk kategori `Sparepart` sesuai seeder:

```json
{
  "tipe_barang_kategori": "Sparepart",
  "nama_barang": "Selang Hydrant",
  "tipe_barang": "Sparepart",
  "jumlah_barang": "5",
  "ukuran_barang": "1.5 inch",
  "satuan": "unit",
  "kondisi": "baik"
}
```

Catatan: field `kondisi` akan disimpan ke kolom `kondisi_barang`.

## Barang Keluar

### POST /transaksi/barang-keluar

Membuat pengajuan barang keluar.

Auth: saat ini tidak memakai middleware auth di route, tetapi secara bisnis sebaiknya tetap diberi auth.

Body JSON:

```json
{
  "items": [
    {
      "id_barang": "APASPA-0726-001",
      "jumlah_barang": 1
    }
  ],
  "tujuan": "Area Produksi",
  "keterangan": "Penggantian unit"
}
```

Validasi:

| Field | Rule |
| --- | --- |
| `items` | required, array, min 1 |
| `items.*.id_barang` | required, string, exists `barangs.id_barang` |
| `items.*.jumlah_barang` | required, integer, min 1 |
| `tujuan` | required, string, max 255 |
| `keterangan` | nullable, string |

## Supervisor Umum

### POST /supervisor-umum/scan-qr

Scan QR untuk mengecek apakah barang sudah ada.

Auth: Bearer token

Role: `supervisor_umum`

Body JSON:

```json
{
  "nomor_identifikasi": "APAR-001"
}
```

Validasi:

| Field | Rule |
| --- | --- |
| `nomor_identifikasi` | required, string |

### POST /supervisor-umum/barang

Tambah barang sekaligus hubungkan dengan QR code.

Auth: Bearer token

Role: `supervisor_umum`

Body JSON:

```json
{
  "nama_barang": "APAR CO2",
  "jumlah_barang": 1,
  "tipe_barang": "APAR",
  "satuan": "unit",
  "kondisi": "baik",
  "nomor_identifikasi": "APAR-002",
  "berat_barang": 5,
  "harga_beli": 500000,
  "harga_jual": 650000,
  "ukuran_barang": "5 Kg",
  "merek_barang": "Yamato",
  "lokasi_barang": "Gedung B",
  "qr_code_path": "qrcodes/APAR-002.png"
}
```

Validasi:

| Field | Rule |
| --- | --- |
| `nama_barang` | required, string, max 255 |
| `jumlah_barang` | required, integer, min 1 |
| `tipe_barang` | required, string |
| `satuan` | required, string |
| `kondisi` | required, string |
| `nomor_identifikasi` | required, string |
| `berat_barang` | nullable, numeric |
| `harga_beli` | nullable, numeric |
| `harga_jual` | nullable, numeric |
| `ukuran_barang` | nullable, string |
| `merek_barang` | nullable, string |
| `lokasi_barang` | nullable, string |
| `qr_code_path` | nullable, string |

## Staff Prefix

Endpoint berikut juga terdaftar dengan prefix `/staff` dan membutuhkan token + role `staff_gudang`:

| Method | Endpoint | Controller |
| --- | --- | --- |
| GET | `/staff/barang/ringkasan` | `BarangController@ringkasan` |
| GET | `/staff/barang/{qrCodeData}` | `BarangController@showByQrCode` |
| GET | `/staff/barangs` | `BarangController@index` |
| PUT | `/staff/user/update` | `ApiLoginController@update` |
| POST | `/staff/laporan-apk` | `LaporanAPKController@store` |
| GET | `/staff/notifikasi` | `NotifikasiController@index` |
| POST | `/staff/notifikasi/generate` | `NotifikasiController@generateNotifikasi` |

Catatan penting:

- `/staff/barangs` dan `/staff/notifikasi` kemungkinan gagal 403 karena controllernya hanya mengizinkan role `inspektor`.
- `/staff/user/update` kemungkinan error karena `ApiLoginController` tidak memiliki method `update`.

## Master Data / Konfigurasi Form

### GET /form-configs/{form_type}

Mengambil konfigurasi field form mobile.

Auth: tidak perlu token di route saat ini.

Path parameter:

| Parameter | Contoh |
| --- | --- |
| `form_type` | `APAR`, `Sparepart` |

Body: tidak ada

Response berisi array field:

```json
[
  {
    "field_name": "nama_barang",
    "label_display": "Nama Barang",
    "input_type": "text",
    "is_required": true,
    "field_order": 1
  }
]
```

### GET /master-data/{category_name}

Mengambil value master data berdasarkan kategori.

Auth: tidak perlu token di route saat ini.

Path parameter:

| Parameter | Contoh |
| --- | --- |
| `category_name` | `APAR`, `Sparepart`, `category_name` |

Jika `category_name = category_name`, response berisi daftar kategori.

## Test

### GET /test

Endpoint test.

Auth: tidak perlu token

Body: tidak ada

Response:

```json
{
  "message": "API Test Berhasil!"
}
```

## Catatan Untuk Collection Postman

Variables yang disarankan:

| Variable | Value |
| --- | --- |
| `base_url` | `http://localhost:8000/api` |
| `access_token` | isi dari response login |

Authorization collection:

Type: Bearer Token

Token:

```text
{{access_token}}
```

Untuk endpoint tanpa auth, pilih `No Auth` atau biarkan ikut collection auth selama server mengabaikannya.

