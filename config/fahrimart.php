<?php

return [
    // SEMENTARA: stok <= batas ini dianggap "Hampir Habis".
    // TODO: tetapkan angkanya bersama Toko Fahri.
    'batas_stok_menipis' => (int) env('STOK_BATAS_MENIPIS', 5),

    // PPN dihitung aplikasi dan TIDAK disimpan di kolom terpisah.
    // total_belanja = subtotal barang + PPN, jadi PPN = total_belanja - SUM(subtotal detail).
    // TODO: konfirmasi tarif dan penerapannya dengan Toko Fahri.
    'ppn_persen' => (float) env('PPN_PERSEN', 10),
];