<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKetidakhadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $id_pegawai = Auth::user()->id_pegawai;

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

    public function getSubmissionById($id)
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $submission = PengajuanKetidakhadiran::with(['pegawai:id,nama,nama_jabatan', 'kategori:id,nama_kategori', 'approver:id,nama,nama_jabatan'])
                            ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Submission data has been successfully retrieved',
                'data'    => $submission
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Submission data not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the submission data.',
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

        $id_pegawai = Auth::user()->id_pegawai;
        $tgl_mulai  = Carbon::parse($request->tanggal_mulai);
        $tgl_selesai= Carbon::parse($request->tgl_selesai);
        $jml_hari   = $tgl_selesai->diffInDays($tgl_mulai) + 1;

        $dokumen = null;
        if($request->hasFile('dokumen_pendukung')){
            $dokumen = $request->file('dokumen_pendukung')->store('public/dokumen_pendukung');
            $dokumen = Storage::url($dokumen);
        }

        try{
            $pengajuan = PengajuanKetidakhadiran::create([
                'id_pegawai'      => $id_pegawai,
                'id_kategori'     => $request->id_kategori,
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jumlah_hari'     => $jml_hari,
                'alasan'          => $request->alasan,
                'dokumen_pendukung'=> $dokumen,
                'status_pengajuan' => 'PENDING'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Submission of absence data has been successfully',
                'data'    => $pengajuan
            ], 200);

        }catch (\Exception $e) {
            if ($dokumen && Storage::exists(str_replace('/storage/', 'public/', $dokumen))) {
                Storage::delete(str_replace('/storage/', 'public/', $dokumen));
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengajukan ketidakhadiran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_pengajuan' => 'required|in:APPROVED,REJECTED',
            'catatan_hrd' => 'nullable|string|max:250',
        ]);

        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $id_hrd = Auth::id_pegawai();
        try{
            $pengajuan = PengajuanKetidakhadiran::find($id);

            if($pengajuan){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Submission data not found.',
                ], 500);
            }

            $pengajuan->status_pengajuan = $request->status_pengajuan;
            $pengajuan->catatan_hrd      = $request->catatan_hrd;
            $pengajuan->disetujui_oleh   = $id_hrd;
            $pengajuan->tanggal_disetujui= Carbon::now();

            $pengajuan->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Submission data successfully approved',
                'data'    => $pengajuan
            ], 200);

        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to apporve the submission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
