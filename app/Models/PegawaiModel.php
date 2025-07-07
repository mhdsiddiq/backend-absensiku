<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiModel extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'nip',
        'nama',
        'nama_jabatan',
        'divisi',
        'alamat',
        'no_telepon',
        'tanggal_bergabung',
        'status_karyawan',
        'status_kerja'
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
    ];

    public function user()
    {
        return $this->hasOne(UserModel::class, 'id_pegawai');
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiModel::class, 'id_pegawai');
    }

    public function pengajuanKetidakhadiran()
    {
        return $this->hasMany(PengajuanKetidakhadiranModel::class, 'id_pegawai');
    }
}
