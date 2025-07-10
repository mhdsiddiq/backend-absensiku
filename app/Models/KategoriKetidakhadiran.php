<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKetidakhadiran extends Model
{
     use HasFactory;

    protected $table = 'kategori_ketidakhadiran';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pengajuanKetidakhadiran()
    {
        return $this->hasMany(PengajuanKetidakhadiran::class, 'id_kategori');
    }
}
