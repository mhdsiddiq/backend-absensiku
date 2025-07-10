<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function getAllEmployee()
    {
        try {
            $pegawai = Pegawai::select('id', 'nip', 'nama', 'nama_jabatan')->get();
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
}
