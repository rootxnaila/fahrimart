@extends('layouts.app')

@section('title', 'Retur dan Barang Rusak')

@section('content')
    <h1 class="page-title">Retur &amp; Barang Rusak</h1>
    <p class="page-subtitle">Kelola barang retur dari supplier atau barang rusak</p>

    @if (session('status'))
        <div class="alert alert-fm-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Form pencatatan --}}
    <div class="card-fm mb-4">
        <form method="POST" action="{{ route('stok.retur.store') }}" novalidate>
            @csrf

            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <span class="form-label mb-0">Jenis</span>
                @foreach (['Retur Supplier', 'Barang Rusak'] as $j)
                    <label class="jenis-chip">
                        <input type="radio" name="jenis" value="{{ $j }}" @checked(old('jenis', 'Retur Supplier') === $j)>
                        <span>{{ $j }}</span>
                    </label>
                @endforeach
            </div>
            @error('jenis')
                <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label for="id_barang" class="form-label">Barang</label>
                    <select id="id_barang" name="id_barang"
                            class="form-select input-fm input-fm-lg {{ $errors->has('id_barang') ? 'is-invalid' : '' }}">
                        <option value="">Pilih Barang</option>
                        @foreach ($daftarBarang as $b)
                            <option value="{{ $b->id_barang }}" @selected(old('id_barang') == $b->id_barang)>
                                {{ $b->nama_barang }} ({{ $b->satuan }}, stok {{ $b->stok }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_barang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="qty_rusak" class="form-label">Jumlah</label>
                    <input type="number" id="qty_rusak" name="qty_rusak" value="{{ old('qty_rusak') }}" min="1" step="1"
                           class="form-control input-fm input-fm-lg {{ $errors->has('qty_rusak') ? 'is-invalid' : '' }}" placeholder="0">
                    @error('qty_rusak')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="tanggal_catat" class="form-label">Tanggal</label>
                    <input type="date" id="tanggal_catat" name="tanggal_catat"
                           value="{{ old('tanggal_catat', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
                           class="form-control input-fm input-fm-lg {{ $errors->has('tanggal_catat') ? 'is-invalid' : '' }}">
                    @error('tanggal_catat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="keterangan" class="form-label">Alasan</label>
                <textarea id="keterangan" name="keterangan" rows="3" maxlength="100"
                          class="form-control input-fm h-auto py-2 {{ $errors->has('keterangan') ? 'is-invalid' : '' }}"
                          placeholder="Jelaskan alasan retur/kerusakan (maksimal 100 karakter)">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-fm px-5">Simpan</button>
            </div>
        </form>
    </div>

    {{-- Riwayat --}}
    <div class="card-fm">
        <h2 class="section-title">Riwayat Retur &amp; Barang Rusak</h2>

        <form method="GET" action="{{ route('stok.retur.index') }}" class="toolbar-fm">
            <div class="search position-relative">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" class="input-fm w-100" style="padding-left: 2.4rem;" placeholder="Cari nama barang">
            </div>

            <select name="jenis" class="input-fm" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="Retur Supplier" @selected(request('jenis') === 'Retur Supplier')>Retur Supplier</option>
                <option value="Barang Rusak" @selected(request('jenis') === 'Barang Rusak')>Barang Rusak</option>
            </select>
        </form>

        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        <tr>
                            <td>{{ $r->tanggal_catat->format('d/m/Y') }}</td>
                            <td class="text-muted-fm">{{ $r->barang->nama_barang ?? '-' }}</td>
                            <td><span class="fw-semibold" title="{{ $r->jenis }}">{{ $r->jenis_singkat }}</span></td>
                            <td>{{ $r->qty_rusak }} <small class="text-muted-fm">{{ $r->barang->satuan ?? '' }}</small></td>
                            <td class="text-muted-fm">{{ $r->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted-fm">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>
                @if ($riwayat->total() > 0)
                    Menampilkan {{ $riwayat->firstItem() }} sampai {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            <span class="pager">
                @if ($riwayat->onFirstPage())
                    <span class="disabled">&lsaquo; Sebelumnya</span>
                @else
                    <a href="{{ $riwayat->previousPageUrl() }}">&lsaquo; Sebelumnya</a>
                @endif

                @if ($riwayat->hasMorePages())
                    <a href="{{ $riwayat->nextPageUrl() }}">Berikutnya &rsaquo;</a>
                @else
                    <span class="disabled">Berikutnya &rsaquo;</span>
                @endif
            </span>
        </div>
    </div>
@endsection