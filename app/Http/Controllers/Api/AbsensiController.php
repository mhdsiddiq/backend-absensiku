<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JamKerja;
use App\Models\Pegawai;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    public function checkAttendance($id)
    {
        $absensi = Absensi::where('id_pegawai', $id)
                        ->whereDate('tanggal', Carbon::today())
                        ->first();

        if (!$absensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'No attendance record found'
            ], 404);
        }

        $hasCheckedIn = !is_null($absensi->jam_masuk);

        return response()->json([
            'status' => 'success',
            'message' => $hasCheckedIn ? 'Already checked in' : 'Ready for check-in',
            'data' => $absensi,
            'has_checked_in' => $hasCheckedIn
        ], 200);
    }

    public function storeCheckIn(Request $request, $id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'latitude'   => 'required|varchar',
                'longitude'  => 'required|varchar',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message'=> 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah pegawai exists
            $pegawai = Pegawai::find($id);
            if (!$pegawai) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Employee not found'
                ], 404);
            }

            // Cek apakah sudah ada absensi hari ini
            $existingAbsensi = Absensi::where('id_pegawai', $id)
                                    ->whereDate('tanggal', Carbon::today())
                                    ->first();

            if ($existingAbsensi && !is_null($existingAbsensi->jam_masuk)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Already checked in today',
                    'data' => [
                        'jam_masuk' => $existingAbsensi->jam_masuk,
                        'tanggal'   => $existingAbsensi->tanggal
                    ]
                ], 400);
            }

            //ambil jam kerja
            $jamKerja = JamKerja::where('is_active', true)->first();

            if (!$jamKerja) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Work schedule not found'
                ], 404);
            }

            $currentTime        = Carbon::now();
            $jamMasukTerjadwal  = Carbon::parse($jamKerja->jam_masuk);
            $jamKeluarTerjadwal = Carbon::parse($jamKerja->jam_keluar);

            // Hitung keterlambatan
            $terlambat = null;
            if ($currentTime->gt($jamMasukTerjadwal)) {
                $terlambat = $currentTime->format('H:i:s');
            }

            // Validasi lokasi menggunakan koordinat dari tabel jam_kerja
            $kantorLat = $jamKerja->latitude;
            $kantorLng = $jamKerja->longitude;
            $radiusKantor = 100; // meter

            $jarak = $this->hitungJarak($request->latitude, $request->longitude, $kantorLat, $kantorLng);
            $keterangan = $jarak <= $radiusKantor ? 'Valid' : 'Tidak Valid';


            if ($existingAbsensi) {
                $existingAbsensi->update([
                    'jadwal_masuk'    => $jamMasukTerjadwal->format('H:i:s'),
                    'jadwal_keluar'   => $jamKeluarTerjadwal->format('H:i:s'),
                    'jam_masuk'       => $currentTime->format('H:i:s'),
                    'latitude_masuk'  => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                    'terlambat'       => $terlambat,
                    'keterangan'      =>  "Absensi masuk " . $keterangan
                ]);

                $absensi = $existingAbsensi;
            } else {
                $absensi = Absensi::create([
                    'id_pegawai'      => $id,
                    'tanggal'         => Carbon::today()->format('Y-m-d'),
                    'jadwal_masuk'    => $jamMasukTerjadwal->format('H:i:s'),
                    'jadwal_keluar'   => $jamKeluarTerjadwal->format('H:i:s'),
                    'jam_masuk'       => $currentTime->format('H:i:s'),
                    'latitude_masuk'  => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                    'terlambat' => $terlambat,
                    'keterangan' => $keterangan . ' - ' . ($request->keterangan ?? 'Absensi masuk')
                ]);
            }

            $statusCode = $keterangan === 'Valid' ? 201 : 400;
            $message = $keterangan === 'Valid' ? 'Check-in successful' : 'Check-in failed - Invalid location';

            return response()->json([
                'status' => $keterangan === 'Valid' ? 'success' : 'error',
                'message' => $message,
                'data' => [
                    'id'                => $absensi->id,
                    'tanggal'           => $absensi->tanggal,
                    'jam_masuk'         => $absensi->jam_masuk,
                    'jadwal_masuk'      => $absensi->jadwal_masuk,
                    'jadwal_keluar'     => $absensi->jadwal_keluar,
                    'latitude_masuk'    => $absensi->latitude_masuk,
                    'longitude_masuk'   => $absensi->longitude_masuk,
                    'terlambat'         => $absensi->terlambat,
                    'keterangan'        => $absensi->keterangan,
                    'jarak_dari_kantor' => round($jarak, 2) . ' meter',
                    'validasi_lokasi'   => $keterangan
                ]
            ], $statusCode);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to check in: ' . $e->getMessage()
            ], 500);
        }
    }
}
