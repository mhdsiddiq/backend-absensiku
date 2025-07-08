<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
     use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'id_pegawai',
        'tanggal',
        'jadwal_masuk',
        'jadwal_keluar',
        'jam_masuk',
        'jam_keluar',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        'terlambat',
        'pulang_cepat',
        'durasi_kerja',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jadwal_masuk' => 'datetime:H:i',
        'jadwal_keluar' => 'datetime:H:i',
        'jam_masuk' => 'datetime:H:i',
        'jam_keluar' => 'datetime:H:i',
        'latitude_masuk' => 'decimal:8,6',
        'longitude_masuk' => 'decimal:9,6',
        'latitude_keluar' => 'decimal:8,6',
        'longitude_keluar' => 'decimal:9,6',
        'terlambat' => 'integer',
        'pulang_cepat' => 'integer',
        'durasi_kerja' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
}
