<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\DashboardAbsensi;
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
        if (!Auth::check()) {
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
                'message' => 'Absensi tida ditemukan'
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

            if ($existingAbsensi && !is_null($existingAbsensi->jam_masuk)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan absensi hari ini',
                    'data' => [
                        'jam_masuk' => $existingAbsensi->jam_masuk,
                        'tanggal'   => $existingAbsensi->tanggal
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

            $currentTime        = Carbon::now();
            $jamMasukTerjadwal  = Carbon::parse($jamKerja->jam_masuk);
            $jamKeluarTerjadwal = Carbon::parse($jamKerja->jam_keluar);
            $terlambat = $currentTime->gt($jamMasukTerjadwal) ? $currentTime->format('H:i:s') : null;

            // Validasi lokasi
            $kantorLat = floatval($jamKerja->latitude);
            $kantorLng = floatval($jamKerja->longitude);
            $radiusKantor = 10000; // meter

            $latUser = floatval($request->latitude);
            $lngUser = floatval($request->longitude);

            $jarak = $this->hitungJarak($latUser, $lngUser, $kantorLat, $kantorLng);

            if ($jarak > $radiusKantor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Check-in failed - You are too far from the office location',
                    'distance' => $jarak . ' meter',
                    'radius_required' => $radiusKantor . ' meter'
                ], 400);
            }

            // Lokasi valid, lanjut simpan absensi
            if ($existingAbsensi) {
                $existingAbsensi->update([
                    'jadwal_masuk'    => $jamMasukTerjadwal->format('H:i:s'),
                    'jadwal_keluar'   => $jamKeluarTerjadwal->format('H:i:s'),
                    'jam_masuk'       => $currentTime->format('H:i:s'),
                    'latitude_masuk'  => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                    'terlambat'       => $terlambat,
                    'keterangan'      => 'Valid - Absensi masuk',
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
                    'keterangan'      => 'Valid - Absensi masuk',
                ]);
            }

            $this->saveToMongoDB($absensi);

            return response()->json([
                'status' => 'success',
                'message' => 'Check-in successful',
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
            ], 201);
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
            // Validasi auth dan check-in pagi
            $attendanceCheck = $this->checkAttendance($id);
            $responseData    = json_decode($attendanceCheck->getContent(), true);

            if ($responseData['status'] === 'error') {
                return $attendanceCheck;
            }

            // Validasi input lokasi
            $validator = Validator::make($request->all(), [
                'latitude'   => 'required|numeric',
                'longitude'  => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ambil data absensi hari ini
            $absensi = Absensi::where('id_pegawai', $id)
                ->whereDate('tanggal', Carbon::today())
                ->first();

            if (!$absensi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Absensi belum dilakukan'
                ], 404);
            }

            if ($absensi->jam_keluar) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan check-out hari ini'
                ], 400);
            }

            // Ambil jadwal kerja
            $jamKerja = JamKerja::first();
            if (!$jamKerja) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal kerja tidak ditemukan'
                ], 404);
            }

            $currentTime = Carbon::now();
            $jadwalKeluar = Carbon::parse($jamKerja->jam_keluar);

            // ✅ Validasi hanya boleh checkout setelah jam_keluar
            if ($currentTime->lt($jadwalKeluar)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal check-out: belum masuk waktu pulang (jam keluar terjadwal: ' . $jadwalKeluar->format('H:i') . ')'
                ], 403);
            }

            // Hitung jarak lokasi sekarang dengan titik absensi
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $jamKerja->latitude,
                $jamKerja->longitude
            );

            if ($jarak > 100) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal check-out: lokasi di luar radius 100 meter',
                    'jarak_dari_kantor' => round($jarak) . ' meter'
                ], 403);
            }

            // Update absensi
            $absensi->jam_keluar = $currentTime->format('H:i:s');
            $absensi->latitude_keluar = $request->latitude;
            $absensi->longitude_keluar = $request->longitude;
            $absensi->pulang_cepat = null; // tidak dihitung karena sudah lewat jam_keluar
            $absensi->keterangan = 'Check-out valid';
            $absensi->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil check-out',
                'data' => [
                    'jam_keluar' => $absensi->jam_keluar,
                    'lokasi_valid' => true,
                    'jarak_dari_kantor' => round($jarak) . ' meter'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal check-out: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllAttendance()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }
        try {
            $absensi = Absensi::with('pegawai:id,nama,nama_jabatan')
                ->orderBy('tanggal', 'desc')
                ->paginate(10);

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
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $year = now()->year;
            $absensi = Absensi::with('pegawai:id,nama,nama_jabatan')
                ->whereYear('tanggal', $year)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

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

    public function getAllAttendanceEmployee($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $pegawai = Pegawai::find($id);

            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not associated with any employee data',
                    'data' => null
                ], 404);
            }

            $now = Carbon::now('Asia/Jakarta');
            $year = $now->year;
            $month = $now->month;

            // Ambil data absensi (tanpa pagination dulu karena akan diubah isinya)
            $absensi = Absensi::select('id', 'id_pegawai', 'tanggal', 'jadwal_masuk', 'jadwal_keluar', 'jam_masuk', 'jam_keluar', 'terlambat')
                ->with('pegawai:id,nama,nama_jabatan')
                ->where('id_pegawai', $pegawai->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->orderBy('tanggal', 'desc')
                ->get()
                ->map(function ($item) {
                    if ($item->terlambat !== null && $item->jam_masuk && $item->jadwal_masuk) {
                        $jamMasuk = Carbon::parse($item->jam_masuk);
                        $jadwalMasuk = Carbon::parse($item->jadwal_masuk);

                        if ($jamMasuk > $jadwalMasuk) {
                            $diffInMinutes = $jamMasuk->diffInMinutes($jadwalMasuk);
                            $jam = floor($diffInMinutes / 60);
                            $menit = $diffInMinutes % 60;

                            if ($jam > 0 && $menit > 0) {
                                $item->terlambat_dalam_jam = "$jam jam $menit menit";
                            } elseif ($jam > 0) {
                                $item->terlambat_dalam_jam = "$jam jam";
                            } else {
                                $item->terlambat_dalam_jam = "$menit menit";
                            }
                        } else {
                            $item->terlambat_dalam_jam = "0 menit";
                        }
                    } else {
                        $item->terlambat_dalam_jam = null;
                    }

                    return $item;
                });

            return response()->json([
                'success' => true,
                'message' => $absensi->isEmpty()
                    ? 'No attendance data found for this month'
                    : 'List of attendance data for the current month',
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

    private function saveToMongoDB($absensi)
    {
        // Hitung statistik yang diperlukan
        $jumlahKaryawan = Absensi::distinct('id_pegawai')->count();
        $jumlahAbsensi = Absensi::where('tanggal', $absensi->tanggal)->count();
        $jumlahTidakHadir = $jumlahKaryawan - $jumlahAbsensi;
        $persentaseKehadiran = $jumlahKaryawan > 0 ? ($jumlahAbsensi / $jumlahKaryawan) * 100 : 0;
        // Simpan ke MongoDB
        DashboardAbsensi::updateOrCreate(
            ['tanggal' => $absensi->tanggal],
            [
                'jumlah_karyawan' => $jumlahKaryawan,
                'jumlah_absensi' => $jumlahAbsensi,
                'jumlah_tidak_hadir' => $jumlahTidakHadir,
                'persentase_kehadiran' => round($persentaseKehadiran, 2),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
