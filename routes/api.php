<?php

use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JamKerjaController;

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

    //jam kerja
    Route::get('/jam-kerja', [JamKerjaController::class, 'getShift']);

    //absensi
    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'getAllAttendance']);//ROle HRD
        Route::get('check/{id}', [AbsensiController::class, 'checkAttendance']);
        Route::post('chekin/{id}', [AbsensiController::class, 'storeCheckIn']);

    });

});
