<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanKetidakhadiran extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_ketidakhadiran';

    protected $fillable = [
        'id_pegawai',
        'id_kategori',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'dokumen_pendukung',
        'status_pengajuan',
        'catatan_hrd',
        'disetujui_oleh',
        'tanggal_disetujui'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_hari' => 'integer',
        'tanggal_disetujui' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKetidakhadiran::class, 'id_kategori');
    }

    public function approver()
    {
        return $this->belongsTo(Pegawai::class, 'disetujui_oleh');
    }
}
