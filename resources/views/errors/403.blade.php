@extends('layouts.guest')

@section('title', 'Akses ditolak')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center text-center p-4">
    <div>
        <p class="display-1 fw-bold mb-0" style="color: var(--fm-green);">403</p>
        <h1 class="h3 mb-3">Akses ditolak</h1>
        <p class="mb-4" style="color: var(--fm-muted);">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-fm px-4">Kembali ke Dashboard</a>
    </div>
</div>
@endsection