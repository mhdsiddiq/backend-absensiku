// app/Services/DashboardService.php
namespace App\Services;

use App\Models\Absensi; // Model MySQL
use App\Models\DashboardAbsensi; // Model MongoDB
use Carbon\Carbon;

class DashboardService
{
    public function updateDailyStats()
    {
        $today = Carbon::today()->toDateString();

        // Hitung statistik dari MySQL
        $stats = [
            'tanggal' => $today,
            'total_hadir' => Absensi::whereDate('tanggal', $today)->count(),
            'total_terlambat' => Absensi::whereDate('tanggal', $today)->whereNotNull('terlambat')->count(),
            'total_cuti' => Absensi::whereDate('tanggal', $today)->where('status', 'Cuti')->count(), // Misal status cuti
            'total_izin' => Absensi::whereDate('tanggal', $today)->where('status', 'Izin')->count(), // Misal status izin
            'total_sakit' => Absensi::whereDate('tanggal', $today)->where('status', 'Sakit')->count(), // Misal status sakit
        ];

        // Simpan/update ke MongoDB
        DashboardAbsensi::updateOrCreate(
            ['tanggal' => $today],
            $stats
        );
    }
}
