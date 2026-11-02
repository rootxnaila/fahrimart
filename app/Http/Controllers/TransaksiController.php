<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = Transaksi::with('user')
            ->orderBy('waktu_transaksi', 'desc')
            ->paginate(10);

        return view('transaksi.index', compact('transaksi'));
    }
}