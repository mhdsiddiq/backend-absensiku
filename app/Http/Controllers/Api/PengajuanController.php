<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKetidakhadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    public function getAllSubmission()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }
        try{
            $pengajuan = PengajuanKetidakhadiran::with(['pegawai:id,nama,nama_jabatan', 'kategori:id,nama_kategori','approver:id,nama,nama_jabatan'])
                            ->orderBy('created_at', 'desc')
                            ->get();

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

    public function getEmployeeSubmission()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $id_pegawai = Auth::id_pegawai();

        try{
            $pengajuan = PengajuanKetidakhadiran::with(['pegawai:id,nama,nama_jabatan', 'kategori:id,nama_kategori','approver:id,nama,nama_jabatan'])
                            ->where('id_pegawai', $id_pegawai)
                            ->orderBy('created_at', 'desc')
                            ->get();
            return response()->json([
                'status'  => 'success',
                'message' => 'Submission of absence data has been successfully retrieved',
                'data'    => $pengajuan
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving submission of absence data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeSubmission(Request $request)
    {
        $request->validate([
            'id_kategori'       => 'required|integer|exists:kategori_ketidakhadiran,id',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari'       => 'required|integer|gte:1',
            'alasan'            => 'required|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $id_pegawai = Auth::id_pegawai();
        $tgl_mulai  = Carbon::parse($request->tanggal_mulai);
        $tgl_selesai= Carbon::parse($request->tgl_selesai);
        $jml_hari   = $tgl_selesai->diffInDays($tgl_mulai) + 1;

        $dokumen = null;
        if($request->hasFile('dokumen_pendukung')){
            $dokumen = $request->file('dokumen_pendukung');
        }
    }
}
