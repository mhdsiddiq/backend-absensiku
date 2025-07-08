<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    use HasFactory;

    protected $table = 'jam_kerja';

    protected $fillable = [
        'nama_jadwal',
        'jam_masuk',
        'jam_keluar',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'jam_masuk' => 'datetime:H:i',
        'jam_keluar' => 'datetime:H:i',
    ];
}
