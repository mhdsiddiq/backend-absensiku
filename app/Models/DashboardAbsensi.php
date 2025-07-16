<?php

namespace App\Models;

use Carbon\Carbon;
use MongoDB\Laravel\Eloquent\Model;
class DashboardAbsensi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'dashboard_absensi';

      protected $fillable = [
        'tanggal',
        'jumlah_karyawan',
        'jumlah_absensi',
        'jumlah_tidak_hadir',
        'persentase_kehadiran',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_karyawan' => 'integer',
        'jumlah_absensi' => 'integer',
        'jumlah_tidak_hadir' => 'integer',
        'persentase_kehadiran' => 'decimal:2',
    ];

    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal', Carbon::parse($date)->format('Y-m-d'));
    }

    public function scopeToday($query)
    {
        return $query->where('tanggal', Carbon::today()->format('Y-m-d'));
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [
            Carbon::parse($startDate)->format('Y-m-d'),
            Carbon::parse($endDate)->format('Y-m-d')
        ]);
    }
}
