<?php

use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JamKerjaController;
use App\Http\Controllers\Api\KategoriketidakhadiranController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PengajuanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    //pegawai
    Route::get('/employee', [PegawaiController::class, 'getAllEmployee']);

    //jam kerja
    Route::get('/working-hour', [JamKerjaController::class, 'getShift']);

    //kategori ketidakhadiran
    Route::get('/category', [KategoriketidakhadiranController::class, 'getCategory']);

    //absensi
    Route::prefix('attendance')->group(function () {
        //Role HRD
        Route::get('/', [AbsensiController::class, 'getAllAttendance']);
        Route::get('this-year', [AbsensiController::class, 'getAttendanceYear']);//absensi karyawan dalam 1 tahun

        Route::get('check/{id}', [AbsensiController::class, 'checkAttendance']);
        Route::post('chekin/{id}', [AbsensiController::class, 'storeCheckIn']);

        //Role pegawai
        Route::get('this-month/{id}', [AbsensiController::class, 'getAllAttendanceEmployee']);
    });

    Route::prefix('submission')->group(function () {
        //Role HRD
        Route::get('/', [PengajuanController::class, 'getAllSubmission']);
        Route::post('approve/{id}', [PengajuanController::class, 'approve']);

        //role pegawai untuk pengajuan per user
        Route::get('employees', [PengajuanController::class, 'getEmployeeSubmission']);
        Route::post('/', [PengajuanController::class, 'storeSubmission']);
    });

});
