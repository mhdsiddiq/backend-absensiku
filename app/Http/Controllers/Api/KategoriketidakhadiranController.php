<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriKetidakhadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoriketidakhadiranController extends Controller
{
    public function getCategory()
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $kategori = KategoriKetidakhadiran::all();
            if (!$kategori) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Not Found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Absence Category data has been successfully retrieved',
                'data' => $kategori
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving Absence category data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
