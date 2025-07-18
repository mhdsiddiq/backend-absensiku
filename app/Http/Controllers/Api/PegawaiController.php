<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function getAllEmployee()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $pegawai = Pegawai::select('id', 'nip', 'nama', 'nama_jabatan')->paginate(10);
            if (!$pegawai) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Not Found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Employees data has been successfully retrieved',
                'data' => $pegawai
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving Employees data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeStatistic()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $statistics = [
                'total_karyawan' => Pegawai::count(),
                'karyawan_aktif' => Pegawai::where('status_kerja', 'Aktif')->count(),
                'jumlah_divisi' => Pegawai::selectRaw('LOWER(divisi) as divisi_lower')
                    ->whereNotNull('divisi')
                    ->where('divisi', '!=', '')
                    ->distinct()
                    ->count(),
                'jumlah_jabatan' => Pegawai::selectRaw('LOWER(nama_jabatan) as jabatan_lower')
                    ->whereNotNull('nama_jabatan')
                    ->where('nama_jabatan', '!=', '')
                    ->distinct()
                    ->count()
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Employee statistics retrieved successfully',
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving employee statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
