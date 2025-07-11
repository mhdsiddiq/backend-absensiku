# Dokumentasi API Absensiku

Dokumentasi ini menjelaskan setiap endpoint yang tersedia di API Absensiku.

**Base URL**: `http://localhost:8000/api`

## Autentikasi

Endpoint yang berhubungan dengan autentikasi pengguna.

---

### 1. Login Pengguna

Login untuk mendapatkan token autentikasi (Bearer Token).

- **Method**: `POST`
- **Endpoint**: `localhost:8000/api/login`
- **Headers**:
  - `Accept: application/json`
- **Request Body** (form-data):
  - `id_pegawai` (string, required): ID unik dari pegawai.
  - `password` (string, required): Password pengguna.
- **Success Response** (200 OK):
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "user": {
        "id": 1,
        "id_pegawai": 1,
        "role_id": 1,
        "is_active": true,
        // ...
        "role": {
          "id": 1,
          "nama_role": "HRD"
        },
        "pegawai": {
          "id": 1,
          "nama": "Admin HRD",
          // ...
        }
      },
      "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
      "role": "HRD"
    }
  }
  ```
- **Error Response** (401 Unauthorized):
  ```json
  {
    "success": false,
    "message": "Invalid credentials"
  }
  ```
- **Error Response** (400 Bad Request - Validasi Gagal):
  ```json
  {
    "success": false,
    "message": "Validation Error",
    "errors": {
      "id_pegawai": [
        "The id pegawai field is required."
      ]
    }
  }
  ```

---

### 2. Logout Pengguna

Menghapus token autentikasi pengguna yang sedang login.

- **Method**: `POST`
- **Endpoint**: `localhost:8000/api/logout`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "success": true,
    "message": "Logout successful"
  }
  ```

---

### 3. Profil Pengguna

Mendapatkan data profil pengguna yang sedang login.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/profile`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "success": true,
    "data": {
        "id": 1,
        "id_pegawai": 1,
        // ...
        "role": {
            "id": 1,
            "nama_role": "HRD"
        },
        "pegawai": {
            "id": 1,
            "nama": "Admin HRD",
            // ...
        }
    }
  }
  ```

---

## Master Data (Membutuhkan Autentikasi)

Endpoint untuk mendapatkan data master.

---

### 1. Mendapatkan Semua Data Pegawai

Mengambil daftar semua pegawai (hanya kolom tertentu).

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/employee`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Employees data has been successfully retrieved",
    "data": [
      {
        "id": 1,
        "nip": "123456789",
        "nama": "Budi Santoso",
        "nama_jabatan": "Developer"
      }
    ]
  }
  ```
- **Error Response** (404 Not Found):
  ```json
  {
    "status": "error",
    "message": "Data Not Found"
  }
  ```

### 2. Mendapatkan Jam Kerja

Mengambil data jam kerja yang berlaku.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/working-hour`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Working hours data has been successfully retrieved",
    "data": {
      "id": 1,
      "jam_masuk": "08:00:00",
      "jam_keluar": "17:00:00",
      "latitude": "-6.200000",
      "longitude": "106.816666"
    }
  }
  ```

### 3. Mendapatkan Kategori Ketidakhadiran

Mengambil daftar semua kategori alasan ketidakhadiran.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/category`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Absence Category data has been successfully retrieved",
    "data": [
      {
        "id": 1,
        "nama_kategori": "Sakit",
        "created_at": null,
        "updated_at": null
      },
      {
        "id": 2,
        "nama_kategori": "Izin",
        "created_at": null,
        "updated_at": null
      }
    ]
  }
  ```

---

## Absensi (Membutuhkan Autentikasi)

Endpoint untuk mengelola data absensi.

---

### 1. Check-in Pegawai

Melakukan absensi masuk (check-in) untuk seorang pegawai.

- **Method**: `POST`
- **Endpoint**: `localhost:8000/api/attendance/chekin/{id}`
- **Headers**:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Path Parameter**:
  - `id` (integer, required): ID pegawai yang melakukan check-in.
- **Request Body** (JSON):
  - `latitude` (numeric, required): Garis lintang lokasi pegawai.
  - `longitude` (numeric, required): Garis bujur lokasi pegawai.
- **Success Response** (201 Created - Lokasi Valid):
  ```json
  {
    "status": "success",
    "message": "Check-in successful",
    "data": {
        "id": 123,
        "tanggal": "2025-07-11",
        "jam_masuk": "08:05:00",
        // ...
        "jarak_dari_kantor": "50.12 meter",
        "validasi_lokasi": "Valid"
    }
  }
  ```
- **Error Response** (400 Bad Request - Lokasi Tidak Valid):
  ```json
  {
    "status": "error",
    "message": "Check-in failed - Invalid location",
    // ...
  }
  ```
- **Error Response** (400 Bad Request - Sudah Check-in):
  ```json
  {
    "status": "error",
    "message": "Already checked in today",
    // ...
  }
  ```

### 2. Cek Status Absensi Hari Ini

Memeriksa apakah seorang pegawai sudah melakukan absensi masuk pada hari ini.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/attendance/check/{id}`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Path Parameter**:
  - `id` (integer, required): ID pegawai.
- **Success Response** (200 OK - Sudah Check-in):
  ```json
  {
    "status": "success",
    "message": "Already checked in",
    "data": { /* ... data absensi ... */ },
    "has_checked_in": true
  }
  ```
- **Success Response** (200 OK - Belum Check-in):
  ```json
  {
    "status": "success",
    "message": "Ready for check-in",
    "data": { /* ... data absensi ... */ },
    "has_checked_in": false
  }
  ```
- **Error Response** (404 Not Found):
  ```json
  {
    "status": "error",
    "message": "No attendance data found"
  }
  ```

### 3. Mendapatkan Semua Data Absensi (Role: HRD)

Mengambil seluruh data absensi semua pegawai.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/attendance/`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "success": true,
    "message": "List of all attendance data",
    "data": [ /* ... array of attendance data ... */ ]
  }
  ```

### 4. Mendapatkan Data Absensi Tahun Ini (Role: HRD)

Mengambil seluruh data absensi semua pegawai pada tahun berjalan.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/attendance/this-year`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "success": true,
    "message": "List of attendance data for the current year",
    "data": [ /* ... array of attendance data ... */ ]
  }
  ```

### 5. Mendapatkan Data Absensi Bulan Ini (Role: Pegawai)

Mengambil data absensi seorang pegawai pada bulan berjalan.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/attendance/this-month/{id}`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Path Parameter**:
  - `id` (integer, required): ID pegawai.
- **Success Response** (200 OK):
  ```json
  {
    "success": true,
    "message": "List of attendance data for the current month",
    "data": [ /* ... array of attendance data ... */ ]
  }
  ```

---

## Pengajuan (Membutuhkan Autentikasi)

Endpoint untuk mengelola data pengajuan ketidakhadiran.

---

### 1. Mendapatkan Semua Data Pengajuan (Role: HRD)

Mengambil seluruh data pengajuan ketidakhadiran dari semua pegawai.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/submission/`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Submission of absence data has been successfully retrieved",
    "data": [
      {
        "id": 1,
        "id_pegawai": 2,
        "id_kategori": 1,
        "tanggal_mulai": "2025-07-12",
        "tanggal_selesai": "2025-07-12",
        "keterangan": "Sakit demam",
        "status_pengajuan": "pending",
        "pegawai": {
          "id": 2,
          "nama": "Udin",
          "nama_jabatan": "Sales"
        }
      }
    ]
  }
  ```
- **Error Response** (500 Internal Server Error):
  ```json
  {
    "status": "error",
    "message": "An error occurred while retrieving submission of absence data.",
    "error": "..."
  }
  ```
