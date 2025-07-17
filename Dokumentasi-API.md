# Dokumentasi API Absensiku

Dokumentasi ini menjelaskan setiap endpoint yang tersedia di API Absensiku.

**Base URL**: `http://localhost:8000/api`

## Autentikasi

Endpoint yang berhubungan dengan autentikasi pengguna.

---

### 1. Login Pengguna

Login untuk mendapatkan token autentikasi (Bearer Token).

- **Method**: `POST`
- **Endpoint**: `localhost:8000/api/login-redis`
- **Headers**:
  - `Accept: application/json`
- **Request Body** (form-data):
  - `nip` (string, required): Nomor Induk Pegawai (NIP) dari pegawai.
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

### 2. Membuat Pengajuan Ketidakhadiran

Membuat pengajuan ketidakhadiran baru untuk seorang pegawai.

- **Method**: `POST`
- **Endpoint**: `localhost:8000/api/submission`
- **Headers**:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Request Body** (JSON):
  - `id_pegawai` (integer, required): ID pegawai yang mengajukan.
  - `id_kategori` (integer, required): ID kategori ketidakhadiran (misal: sakit, izin).
  - `tanggal_mulai` (date, required): Tanggal mulai ketidakhadiran (format: YYYY-MM-DD).
  - `tanggal_selesai` (date, required): Tanggal selesai ketidakhadiran (format: YYYY-MM-DD).
  - `keterangan` (string, optional): Keterangan tambahan mengenai pengajuan.
- **Success Response** (201 Created):
  ```json
  {
    "status": "success",
    "message": "Submission of absence created successfully",
    "data": {
      "id": 1,
      "id_pegawai": 2,
      "id_kategori": 1,
      "tanggal_mulai": "2025-07-12",
      "tanggal_selesai": "2025-07-12",
      "keterangan": "Sakit demam",
      "status_pengajuan": "pending"
    }
  }
  ```
- **Error Response** (400 Bad Request - Validasi Gagal):
  ```json
  {
    "status": "error",
    "message": "Validation Error",
    "errors": {
      "id_pegawai": [
        "The id pegawai field is required."
      ]
    }
  }
  ```
- **Error Response** (500 Internal Server Error):
  ```json
  {
    "status": "error",
    "message": "An error occurred while creating submission of absence.",
    "error": "..."
  }
  ```

### 3. Menyetujui Pengajuan Ketidakhadiran (Role: HRD)

Menyetujui pengajuan ketidakhadiran seorang pegawai.

- **Method**: `POST`
- **Endpoint**: `localhost:8000/api/submission/approve/{id}`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Path Parameter**:
  - `id` (integer, required): ID pengajuan yang akan disetujui.
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Submission approved successfully"
  }
  ```
- **Error Response** (404 Not Found):
  ```json
  {
    "status": "error",
    "message": "Submission not found"
  }
  ```
- **Error Response** (500 Internal Server Error):
  ```json
  {
    "status": "error",
    "message": "An error occurred while approving the submission.",
    "error": "..."
  }
  ```

---

## Statistik (Membutuhkan Autentikasi)

Endpoint untuk mendapatkan data statistik absensi.

---

### 1. Mendapatkan Statistik Absensi Hari Ini (Role: HRD)

Mendapatkan ringkasan statistik absensi (jumlah hadir, sakit, izin, dll.) untuk semua pegawai pada hari ini.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/statistic/today`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Today's attendance statistics retrieved successfully",
    "data": {
      "total_pegawai": 100,
      "total_hadir": 80,
      "total_sakit": 10,
      "total_izin": 5,
      "total_alpha": 5,
      "persentase_kehadiran": "80%"
    }
  }
  ```
- **Error Response** (500 Internal Server Error):
  ```json
  {
    "status": "error",
    "message": "An error occurred while retrieving today's attendance statistics.",
    "error": "..."
  }
  ```

### 2. Mendapatkan Statistik Absensi Berdasarkan Rentang Tanggal (Role: HRD)

Mendapatkan ringkasan statistik absensi (jumlah hadir, sakit, izin, dll.) untuk semua pegawai berdasarkan rentang tanggal.

- **Method**: `GET`
- **Endpoint**: `localhost:8000/api/statistic/range`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer <YOUR_AUTH_TOKEN>`
- **Query Parameters**:
  - `start_date` (date, required): Tanggal mulai rentang (format: YYYY-MM-DD).
  - `end_date` (date, required): Tanggal akhir rentang (format: YYYY-MM-DD).
- **Success Response** (200 OK):
  ```json
  {
    "status": "success",
    "message": "Attendance statistics for date range retrieved successfully",
    "data": {
      "total_pegawai": 100,
      "total_hadir": 800,
      "total_sakit": 100,
      "total_izin": 50,
      "total_alpha": 50,
      "persentase_kehadiran": "80%"
    }
  }
  ```
- **Error Response** (500 Internal Server Error):
  ```json
  {
    "status": "error",
    "message": "An error occurred while retrieving attendance statistics for date range.",
    "error": "..."
  }
  ```
