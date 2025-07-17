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

    public function getDateRangeStatistics(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $start = $request->input('start_date');
            $end   = $request->input('end_date');
            $stats = DashboardAbsensi::dateRange($start, $end)->get();

            if ($stats->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance statistics found for the specified date range',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance statistics for date range',
                'data' => $stats
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error: ' . $e->getMessage(),
                'errors' => $e->errors(),
                'data' => null
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance statistics for date range: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
