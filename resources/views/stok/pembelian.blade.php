@extends('layouts.app')

@section('title', 'Pembelian Stok')

@section('content')
    <h1 class="page-title">Pembelian Stok</h1>
    <p class="page-subtitle">Tambah pembelian stok dari supplier</p>

    @if (session('status'))
        <div class="alert alert-fm-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Form pembelian --}}
    <div class="card-fm mb-4">
        <form method="POST" action="{{ route('stok.pembelian.store') }}" novalidate>
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="id_supplier" class="form-label">Supplier</label>
                    <select id="id_supplier" name="id_supplier"
                            class="form-select input-fm input-fm-lg {{ $errors->has('id_supplier') ? 'is-invalid' : '' }}">
                        <option value="">Pilih Supplier</option>
                        @foreach ($daftarSupplier as $s)
                            <option value="{{ $s->id_supplier }}" @selected(old('id_supplier') == $s->id_supplier)>{{ $s->nama_supplier }}</option>
                        @endforeach
                    </select>
                    @error('id_supplier')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
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
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="qty_masuk" class="form-label">Stok Masuk</label>
                    <input type="number" id="qty_masuk" name="qty_masuk" value="{{ old('qty_masuk') }}" min="1" step="1"
                           class="form-control input-fm input-fm-lg {{ $errors->has('qty_masuk') ? 'is-invalid' : '' }}" placeholder="0">
                    @error('qty_masuk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="harga_kulakan" class="form-label">Harga Beli</label>
                    <div class="position-relative">
                        <span class="rp-prefix">Rp</span>
                        <input type="number" id="harga_kulakan" name="harga_kulakan" value="{{ old('harga_kulakan') }}" min="0" step="any"
                               class="form-control input-fm input-fm-lg has-prefix {{ $errors->has('harga_kulakan') ? 'is-invalid' : '' }}" placeholder="0">
                        @error('harga_kulakan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="tanggal_beli" class="form-label">Tanggal</label>
                    <input type="date" id="tanggal_beli" name="tanggal_beli"
                           value="{{ old('tanggal_beli', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
                           class="form-control input-fm input-fm-lg {{ $errors->has('tanggal_beli') ? 'is-invalid' : '' }}">
                    @error('tanggal_beli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-fm px-5">Simpan</button>
            </div>
        </form>
    </div>

    {{-- Riwayat pembelian --}}
    <div class="card-fm">
        <h2 class="section-title">Riwayat Pembelian</h2>

        <form method="GET" action="{{ route('stok.pembelian.index') }}" class="toolbar-fm">
            <div class="search position-relative">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" class="input-fm w-100" style="padding-left: 2.4rem;" placeholder="Cari nama barang">
            </div>

            <select name="id_supplier" class="input-fm" onchange="this.form.submit()">
                <option value="">Semua Supplier</option>
                @foreach ($daftarSupplier as $s)
                    <option value="{{ $s->id_supplier }}" @selected(request('id_supplier') == $s->id_supplier)>{{ $s->nama_supplier }}</option>
                @endforeach
            </select>
        </form>

        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        <tr>
                            <td>{{ $r->tanggal_beli->format('d/m/Y') }}</td>
                            <td class="text-muted-fm">{{ $r->supplier->nama_supplier ?? '-' }}</td>
                            <td class="text-muted-fm">{{ $r->barang->nama_barang ?? '-' }}</td>
                            <td>{{ $r->qty_masuk }} <small class="text-muted-fm">{{ $r->barang->satuan ?? '' }}</small></td>
                            <td class="text-muted-fm">{{ number_format($r->harga_kulakan, 0, ',', '.') }}</td>
                            <td>{{ number_format($r->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted-fm">Belum ada riwayat pembelian.</td>
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