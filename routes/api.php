<?php

use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JamKerjaController;
use App\Http\Controllers\Api\RedisAuthController;
use App\Http\Controllers\Api\KategoriketidakhadiranController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PengajuanController;
use Illuminate\Support\Facades\DB;

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
Route::post('/redis-login', [RedisAuthController::class, 'login']);

Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckRedisToken::class])->group(function () {
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
        Route::post('checkout/{id}', [AbsensiController::class, 'storeCheckOt']);

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

        Route::get('{id}', [PengajuanController::class, 'getSubmissionById']);
    });

    // Route untuk MongoDB Statistics
    Route::prefix('statistic')->group(function () {
        Route::get('today', function () {
            $stats = \App\Models\DashboardAbsensi::today()->first();
            return response()->json($stats);
        });

        Route::get('range', function (Request $request) {
            $start = $request->input('start_date');
            $end = $request->input('end_date');

            $stats = \App\Models\DashboardAbsensi::dateRange($start, $end)->get();
            return response()->json($stats);
        });
    });

    // Route untuk testing MongoDB
    Route::get('test-mongo', function () {
        try {
            $connection = DB::connection('mongodb');
            $connection->getMongoClient()->listDatabases();
            return 'MongoDB connection working!';
        } catch (\Exception $e) {
            return 'MongoDB connection failed: '.$e->getMessage();
        }
    });

});
