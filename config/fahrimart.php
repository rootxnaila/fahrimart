<?php

return [
    // SEMENTARA: stok <= batas ini dianggap "Hampir Habis".
    'batas_stok_menipis' => (int) env('STOK_BATAS_MENIPIS', 5),

    // PPN dihitung aplikasi dan TIDAK disimpan di kolom terpisah.
    'ppn_persen' => (float) env('PPN_PERSEN', 10),

    // Identitas toko di struk. TODO: konfirmasi dengan Toko Fahri.
    'toko_nama' => env('TOKO_NAMA', 'Fahrimart'),
    'toko_alamat' => env('TOKO_ALAMAT', 'Jl. Danau R No. 2, Malang'),

    // AI: forecasting, peringatan stok, insight
    'analisis' => [
        'horizon_hari' => (int) env('FORECAST_HORIZON', 14),
        'riwayat_hari' => 90,
        // parameter stok
        'lead_time_hari' => (int) env('STOK_LEAD_TIME_HARI', 3),
        'buffer_hari' => (int) env('STOK_BUFFER_HARI', 4),
        'target_cover_hari' => (int) env('STOK_TARGET_COVER_HARI', 7),
        'stok_pengaman_hari' => (int) env('STOK_PENGAMAN_HARI', 1),
        'cache_menit' => (int) env('ANALISIS_CACHE_MENIT', 30),
        // Insight: 'aturan' (bawaan) atau 'llm' (butuh kunci API dan nama model)
        'insight_driver' => env('AI_INSIGHT_DRIVER', 'aturan'),
        'ai_api_key' => env('AI_API_KEY'),
        'ai_model' => env('AI_MODEL'),
        'ai_timeout' => (int) env('AI_TIMEOUT', 25),
    ],
];