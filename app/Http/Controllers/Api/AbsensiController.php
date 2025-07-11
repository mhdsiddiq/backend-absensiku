<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JamKerja;
use App\Models\Pegawai;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    public function checkAttendance($id)
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $absensi = Absensi::where('id_pegawai', $id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        if (!$absensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'No attendance data found'
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

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }

    public function storeCheckIn(Request $request, $id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'latitude'   => 'required|numeric',
                'longitude'  => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
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

            // Ambil jam kerja
            $jamKerja = JamKerja::first();

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
            $kantorLat = floatval($jamKerja->latitude);
            $kantorLng = floatval($jamKerja->longitude);
            $radiusKantor = 100; // meter

            $latUser = floatval($request->latitude);
            $lngUser = floatval($request->longitude);

            $jarak = $this->hitungJarak($latUser, $lngUser, $kantorLat, $kantorLng);
            $keterangan = $jarak <= $radiusKantor ? 'Valid' : 'Tidak Valid';

            if ($existingAbsensi) {
                $existingAbsensi->update([
                    'jadwal_masuk'    => $jamMasukTerjadwal->format('H:i:s'),
                    'jadwal_keluar'   => $jamKeluarTerjadwal->format('H:i:s'),
                    'jam_masuk'       => $currentTime->format('H:i:s'),
                    'latitude_masuk'  => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                    'terlambat'       => $terlambat,
                    'keterangan'      => "Absensi masuk " . $keterangan
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
                    'terlambat'       => $terlambat,
                    'keterangan'      => $keterangan . ' - ' . ($request->keterangan ?? 'Absensi masuk')
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
                ]
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to check in: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCheckOut(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude'   => 'required|numeric',
                'longitude'  => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pegawai = Pegawai::find($id);
            if (!$pegawai) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Employee not found'
                ], 404);
            }

            $existingAbsensi = Absensi::where('id_pegawai', $id)
                ->whereDate('tanggal', Carbon::today())
                ->first();

            // if (!$existingAbsensi || is_null($existingAbsensi->jam_masuk)) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'No check-in record found for today'
            //     ], 400);
            // }

            if (!is_null($existingAbsensi->jam_keluar)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Already checked out today',
                    'data' => [
                        'jam_keluar' => $existingAbsensi->jam_keluar,
                        'tanggal'    => $existingAbsensi->tanggal
                    ]
                ], 400);
            }

            $jamKerja = JamKerja::first();

            if (!$jamKerja) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Work schedule not found'
                ], 404);
            }

            $currentTime = Carbon::now();
            $jamKeluarTerjadwal = Carbon::parse($jamKerja->jam_keluar);

            $plg_cepat = null;
            if ($currentTime->gt($jamKeluarTerjadwal)) {
                $plg_cepat = $currentTime->format('H:i:s');
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to check out: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllAttendance()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }
        try {
            $absensi = Absensi::with('pegawai:id,nama,nama_jabatan')->get();

            if ($absensi->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance data found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'List of all attendance data',
                'data' => $absensi
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance data : ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getAttendanceYear()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $year = now()->year;
            $absensi = Absensi::with('pegawai:id,nama,nama_jabatan')
                ->whereYear('tanggal', $year)
                ->get();

            if ($absensi->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance data found for this year',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'List of attendance data for the current year',
                'data' => $absensi
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance data for this year: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getAllAttendanceEmployee($id) //id_pegawai
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }
        try {
            $pegawai = Pegawai::where('id', $id)->first();

            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not associated with any employee data',
                    'data' => null
                ], 404);
            }

            $year  = now()->year;
            $month = now()->month;
            $absensi = Absensi::select('id', 'id_pegawai', 'tanggal', 'jadwal_masuk', 'jadwal_keluar', 'jam_masuk', 'jam_keluar', 'terlambat')
                ->with('pegawai:id,nama,nama_jabatan')
                ->where('id_pegawai', $pegawai->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get();

            if ($absensi->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance data found for this month',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'List of attendance data for the current month',
                'data' => $absensi
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance data for this month: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
