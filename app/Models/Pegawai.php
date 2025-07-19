<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Elastic\Elasticsearch\Client as ElasticsearchClient;

class Pegawai extends Model
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
        return $this->hasOne(Users::class, 'id_pegawai');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_pegawai');
    }

    public function pengajuanKetidakhadiran()
    {
        return $this->hasMany(PengajuanKetidakhadiran::class, 'id_pegawai');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($karyawan) {
            app(ElasticsearchClient::class)->index([
                'index' => 'karyawan',
                'id' => $karyawan->nip,
                'body' => [
                    'nip' => $karyawan->nip,
                    'nama' => $karyawan->nama,
                    'jabatan' => $karyawan->jabatan,
                    'divisi' => $karyawan->divisi,
                    'alamat' => $karyawan->alamat,
                    'no_telepon' => $karyawan->no_telepon,
                    'status' => $karyawan->status,
                ]
            ]);
        });

        static::deleted(function ($karyawan) {
            app(ElasticsearchClient::class)->delete([
                'index' => 'karyawan',
                'id' => $karyawan->nip,
            ]);
        });
    }

}
