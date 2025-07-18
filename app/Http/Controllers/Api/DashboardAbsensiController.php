<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DashboardAbsensi;
use Exception;

class DashboardAbsensiController extends Controller
{
    public function getTodayStatistics()
    {
        try {
            $stats = DashboardAbsensi::today()->first();

            // Memeriksa apakah ada data yang ditemukan
            if (is_null($stats)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance statistics found for today',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance statistics for today',
                'data' => $stats
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve today\'s attendance statistics: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getLast7DaysStatistics()
    {
        try {
            // Hitung tanggal dari hari ini ke 6 hari ke belakang
            $end = now()->format('Y-m-d');
            $start = now()->subDays(6)->format('Y-m-d');

            $stats = DashboardAbsensi::dateRange($start, $end)->get();

            if ($stats->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance statistics found for the last 7 days',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance statistics for the last 7 days',
                'data' => $stats
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance statistics: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
