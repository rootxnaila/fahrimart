<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Analisis\AnalisisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalisisController extends Controller
{
    private const PERHATIAN = ['habis', 'kritis', 'waspada'];

    public function __construct(private AnalisisService $analisis)
    {
    }

    public function forecastPenjualan(): JsonResponse
    {
        $a = $this->analisis->ambil();

        return response()->json([
            'success' => true,
            'message' => 'Prakiraan penjualan',
            'dibuat_pada' => $a['dibuat_pada'],
            'data' => $a['penjualan'],
        ]);
    }

    public function stokAlert(Request $request): JsonResponse
    {
        $status = array_filter(explode(',', (string) $request->query('status', implode(',', self::PERHATIAN))));
        $a = $this->analisis->ambil();
        $data = array_values(array_filter($a['stok'], fn ($s) => in_array($s['status'], $status, true)));

        return response()->json([
            'success' => true,
            'message' => 'Peringatan stok',
            'dibuat_pada' => $a['dibuat_pada'],
            'data' => $data,
            'meta' => ['total' => count($data)],
        ]);
    }

    public function insight(): JsonResponse
    {
        $a = $this->analisis->ambil();

        return response()->json([
            'success' => true,
            'message' => 'Insight penjualan',
            'dibuat_pada' => $a['dibuat_pada'],
            'data' => $a['insight'],
        ]);
    }

    public function jalankan(): JsonResponse
    {
        $a = $this->analisis->ambil(true);
        $perlu = count(array_filter($a['stok'], fn ($s) => in_array($s['status'], self::PERHATIAN, true)));

        return response()->json([
            'success' => true,
            'message' => 'Analisis dihitung ulang',
            'data' => [
                'dibuat_pada' => $a['dibuat_pada'],
                'barang_perlu_perhatian' => $perlu,
                'sumber_insight' => $a['insight']['sumber'],
            ],
        ]);
    }
}