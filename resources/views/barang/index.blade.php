@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
    <h1 class="page-title">Data Barang</h1>
    <p class="page-subtitle">Kelola produk dan cek status stok</p>

    <div class="card-fm">
        <form method="GET" action="{{ route('master.barang.index') }}" class="toolbar-fm">
            <div class="search position-relative">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" class="input-fm w-100" style="padding-left: 2.4rem;" placeholder="Cari nama barang">
            </div>

            <select name="kategori" class="input-fm" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
                @endforeach
            </select>

            <select name="status" class="input-fm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aman" @selected(request('status') === 'aman')>Stok Aman</option>
                <option value="hampir_habis" @selected(request('status') === 'hampir_habis')>Hampir Habis</option>
                <option value="habis" @selected(request('status') === 'habis')>Habis</option>
            </select>

            <button type="button" class="btn btn-fm btn-sm px-4 ms-auto" disabled title="Segera">Tambah Barang</button>
        </form>

        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barang as $b)
                        <tr>
                            <td>{{ $b->kode_barang }}</td>
                            <td class="text-muted-fm">{{ $b->nama_barang }}</td>
                            <td><span class="chip-kategori">{{ $b->kategori }}</span></td>
                            <td class="text-muted-fm">{{ number_format($b->harga_beli, 0, ',', '.') }}</td>
                            <td class="text-muted-fm">{{ number_format($b->harga_jual, 0, ',', '.') }}</td>
                            <td>{{ $b->stok }} <small class="text-muted-fm">{{ $b->satuan }}</small></td>
                            <td><span class="badge-stok {{ $b->status_stok }}">{{ $b->label_status }}</span></td>
                            <td class="text-nowrap">
                                <button type="button" class="icon-btn" disabled title="Ubah (segera)" aria-label="Ubah">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </button>
                                <button type="button" class="icon-btn danger" disabled title="Hapus (segera)" aria-label="Hapus">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted-fm">Belum ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>
                @if ($barang->total() > 0)
                    Menampilkan {{ $barang->firstItem() }} sampai {{ $barang->lastItem() }} dari {{ $barang->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            <span class="pager">
                @if ($barang->onFirstPage())
                    <span class="disabled">&lsaquo; Sebelumnya</span>
                @else
                    <a href="{{ $barang->previousPageUrl() }}">&lsaquo; Sebelumnya</a>
                @endif

                @if ($barang->hasMorePages())
                    <a href="{{ $barang->nextPageUrl() }}">Berikutnya &rsaquo;</a>
                @else
                    <span class="disabled">Berikutnya &rsaquo;</span>
                @endif
            </span>
        </div>
    </div>
@endsection