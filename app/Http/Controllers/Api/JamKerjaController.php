<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JamKerja;
use Illuminate\Http\Request;

class JamKerjaController extends Controller
{
    public function getShift()
    {
        try {
            $jamKerja = JamKerja::first();
            if (!$jamKerja) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Not Found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Working hours data has been successfully retrieved',
                'data' => $jamKerja
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving working hours data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
