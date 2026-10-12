@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="auth-page">

    {{-- Panel kiri (hanya tampil di layar lebar) --}}
    <aside class="auth-hero d-none d-lg-flex">
        <div>
            <img src="{{ asset('images/fahrimart-wordmark.png') }}" alt="Fahrimart" class="auth-logo">
            <h2 class="auth-hero-title">Kelola Toko,<br>Lebih Mudah</h2>
            <p class="auth-hero-text">Catat penjualan, pantau stok, dan lihat laporan toko dalam satu aplikasi.</p>
        </div>

        <img src="{{ asset('images/login-illustration.png') }}" alt="" class="auth-illustration">

        <small class="auth-footer">&copy; {{ date('Y') }} Fahrimart. All rights reserved.</small>
    </aside>

    {{-- Formulir login --}}
    <main class="auth-form-wrap">
        <div class="auth-form">
            <img src="{{ asset('images/fahrimart-wordmark.png') }}" alt="Fahrimart" class="auth-logo d-lg-none mb-4">

            <h1 class="auth-title">Welcome back!</h1>
            <p class="auth-subtitle">Silakan login untuk melanjutkan</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control fm-input @error('email') is-invalid @enderror"
                           placeholder="example@gmail.com" autocomplete="email" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="position-relative">
                        <input type="password" id="password" name="password"
                               class="form-control fm-input pe-5"
                               placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="fm-eye" id="togglePassword" aria-label="Tampilkan password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-fm auth-submit">Login</button>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');

    toggleButton.addEventListener('click', () => {
        const hidden = passwordInput.type === 'password';
        passwordInput.type = hidden ? 'text' : 'password';
        toggleButton.setAttribute('aria-label', hidden ? 'Sembunyikan password' : 'Tampilkan password');
    });
</script>
@endpush