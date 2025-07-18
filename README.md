# InstaApp
Share moment gambar anda, like dan komentar moment bersama
Ini adalah API bagian dari sistem InstaApp

## Fitur
Fitur dalam InstaApp mencangkup
- Register dan Login
- Posting text gambar
- Like dan komentar
- Autentifikasi pengguna
- Hak akses terhadap post, like, dan komentar

## Requirement
Berikut adalah requirement untuk menjalankan program ini
- PHP >= 8.2
- Composer
- Relational Database

## API Endpoint
Berikut adalah API Endpoint yang tersedia dalam sistem ini:

### 1. API Register
Digunakan untuk membuat profile user baru
##### HTTP Request
```
POST {{url_project}}/api/register
```
##### Parameters

| Parameters    |Status         | Keterangan                        |
| ------------- |:-------------:| -------------                     |
| name          | required	  	| - |
| username      | required      | - |
| email         | required      | - |
| password      | required      | - |
| bio           | optional      | - |
| avatar        | optional      | - |

##### Result

| Parameters    |  Keterangan  |
| ------------- |:--------------|
|success        | Bernilai `true` jika proses berhasil dan `false` jika proses terjadi kesalahan|
|message        | Pesan error ( hanya muncul ketika status `false` ) |
|user           | Data info user yang di register |
|token          | Data token untuk auth via header bearer token request |

### 2. API Login
### 3. API Post
