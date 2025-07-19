<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Elastic\Elasticsearch\Client as ElasticsearchClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ElasticsearchSearchController extends Controller
{
    protected $elasticsearch;

    public function __construct(ElasticsearchClient $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function searchEmployees(Request $request)
    {
        if(!Auth::check()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        try {
            $query = $request->input('q'); // Menggunakan 'q' sebagai parameter query

            if (!$query) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Search query (q) is required.'
                ], 400);
            }

            $params = [
    'index' => 'pegawai',
    'body' => [
        'query' => [
            'bool' => [
                'should' => [
                    [
                        'match_phrase_prefix' => [
                            'nama' => [
                                'query' => $query,
                                'boost' => 3
                            ]
                        ]
                    ],
                    [
                        'match_phrase_prefix' => [
                            'nip' => [
                                'query' => $query,
                                'boost' => 2
                            ]
                        ]
                    ],
                    [
                        'match_phrase_prefix' => [
                            'divisi' => [
                                'query' => $query,
                                'boost' => 1.5
                            ]
                        ]
                    ],
                    [
                        'match_phrase_prefix' => [
                            'nama_jabatan' => [
                                'query' => $query,
                                'boost' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];


            $response = $this->elasticsearch->search($params);
            $hits = collect($response['hits']['hits'])->pluck('_source');

            return response()->json([
                'status' => 'success',
                'message' => 'Employees data has been successfully retrieved from Elasticsearch',
                'data' => $hits
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving Employees data from Elasticsearch.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
