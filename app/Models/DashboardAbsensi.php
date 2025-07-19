<?php

namespace App\Models;

use Carbon\Carbon;
use MongoDB\Laravel\Eloquent\Model;
class DashboardAbsensi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'dashboard_absensis';

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
        $carbonDate = Carbon::parse($date)->startOfDay();
        return $query->where('tanggal', '>=', $carbonDate)
                     ->where('tanggal', '<', $carbonDate->copy()->addDay());
    }

    public function scopeToday($query)
    {
        return $query->whereBetween('tanggal', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return $query->whereBetween('tanggal', [$start, $end]);
    }
}
