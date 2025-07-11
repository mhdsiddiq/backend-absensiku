<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKetidakhadiran;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function getAllSubmission()
    {
        try{
            $pengajuan = PengajuanKetidakhadiran::with('pegawai:id,nama,nama_jabatan')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Submission of absence data has been successfully retrieved',
                'data' => $pengajuan
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving submission of absence data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
