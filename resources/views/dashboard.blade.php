@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Halo, {{ auth()->user()->nama }}.</p>

    <div class="card-fm">
        <p class="mb-0">Halaman kasir (produk dan keranjang) menyusul di tahap POS.</p>
    </div>
@endsection