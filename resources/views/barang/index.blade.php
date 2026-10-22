@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
    <h1 class="page-title">Data Barang</h1>
    <p class="page-subtitle">Kelola produk dan cek status stok</p>

    @if (session('status'))
        <div class="alert alert-fm-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

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

            <button type="button" class="btn btn-fm btn-sm px-4 ms-auto" data-bs-toggle="modal" data-bs-target="#modalTambah">
                Tambah Barang
            </button>
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
                                <button type="button" class="icon-btn" title="Ubah" aria-label="Ubah"
                                        data-bs-toggle="modal" data-bs-target="#modalUbah"
                                        data-id="{{ $b->id_barang }}"
                                        data-nama="{{ $b->nama_barang }}"
                                        data-kategori="{{ $b->kategori }}"
                                        data-satuan="{{ $b->satuan }}"
                                        data-harga-beli="{{ $b->harga_beli }}"
                                        data-harga-jual="{{ $b->harga_jual }}"
                                        data-stok="{{ $b->stok }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </button>
                                <button type="button" class="icon-btn danger" title="Hapus" aria-label="Hapus"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                                        data-id="{{ $b->id_barang }}"
                                        data-nama="{{ $b->nama_barang }}">
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

    {{-- Saran isian --}}
    <datalist id="daftarKategori">
        @foreach ($kategoriSaran as $k)
            <option value="{{ $k }}">
        @endforeach
    </datalist>
    <datalist id="daftarSatuan">
        @foreach (['Pcs', 'Kg', 'Karung', 'Liter', 'Botol', 'Dus', 'Pak'] as $s)
            <option value="{{ $s }}">
        @endforeach
    </datalist>

    {{-- Modal Tambah Barang --}}
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="judulTambah" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('master.barang.store', [], false) }}" class="modal-content modal-fm" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="tambah">

                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulTambah">Tambah Barang</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @include('barang._form', ['mode' => 'tambah', 'withStok' => true])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-fm">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Ubah Barang --}}
    <div class="modal fade" id="modalUbah" tabindex="-1" aria-labelledby="judulUbah" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formUbah" action="" class="modal-content modal-fm" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="ubah">
                <input type="hidden" name="_id" value="">
                <input type="hidden" name="_stok" value="">

                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulUbah">Ubah Barang</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @include('barang._form', ['mode' => 'ubah', 'withStok' => false])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-fm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Hapus Barang --}}
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulHapus" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formHapus" action="" class="modal-content modal-fm">
                @csrf
                @method('DELETE')

                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulHapus">Hapus Barang</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Hapus barang <strong id="hapus-nama"></strong>?</p>
                    <p class="small text-muted-fm mb-0">Barang yang sudah pernah dipakai pada transaksi, pembelian stok, atau retur tidak dapat dihapus.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlBase = @json(route('master.barang.index', [], false));

        // Isi modal Ubah dari data baris yang diklik
        const modalUbah = document.getElementById('modalUbah');
        modalUbah.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            if (!btn) return; // dibuka otomatis setelah error validasi: pakai isian dari server

            const form = document.getElementById('formUbah');
            form.action = urlBase + '/' + btn.dataset.id;
            form.querySelector('[name=_id]').value = btn.dataset.id;
            form.querySelector('[name=_stok]').value = btn.dataset.stok;
            form.querySelector('[name=nama_barang]').value = btn.dataset.nama;
            form.querySelector('[name=kategori]').value = btn.dataset.kategori;
            form.querySelector('[name=satuan]').value = btn.dataset.satuan;
            form.querySelector('[name=harga_beli]').value = btn.dataset.hargaBeli;
            form.querySelector('[name=harga_jual]').value = btn.dataset.hargaJual;
            document.getElementById('ubah-stok-info').textContent = btn.dataset.stok;
        });

        // Isi modal Hapus
        const modalHapus = document.getElementById('modalHapus');
        modalHapus.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            if (!btn) return;

            document.getElementById('formHapus').action = urlBase + '/' + btn.dataset.id;
            document.getElementById('hapus-nama').textContent = btn.dataset.nama;
        });

        // Buka lagi modal yang gagal validasi
        @if ($errors->any() && old('_modal'))
            @if (old('_modal') === 'ubah')
                document.getElementById('formUbah').action = urlBase + '/' + @json(old('_id'));
                document.getElementById('ubah-stok-info').textContent = @json(old('_stok'));
                bootstrap.Modal.getOrCreateInstance(modalUbah).show();
            @else
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambah')).show();
            @endif
        @endif
    });
</script>
@endpush