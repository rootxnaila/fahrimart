@extends('layouts.app')

@section('title', 'Data Supplier')

@section('content')
    <h1 class="page-title">Data Supplier</h1>
    <p class="page-subtitle">Kelola data supplier untuk pembelian stok</p>

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
        <form method="GET" action="{{ route('master.supplier.index') }}" class="toolbar-fm">
            <div class="search position-relative">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" class="input-fm w-100" style="padding-left: 2.4rem;" placeholder="Cari nama supplier">
            </div>

            <button type="button" class="btn btn-fm btn-sm px-4 ms-auto" data-bs-toggle="modal" data-bs-target="#modalTambah">
                Tambah Supplier
            </button>
        </form>

        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Supplier</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplier as $s)
                        <tr>
                            <td>{{ $s->kode_supplier }}</td>
                            <td class="text-muted-fm">{{ $s->nama_supplier }}</td>
                            <td class="text-muted-fm">{{ $s->no_telepon ?: '-' }}</td>
                            <td class="text-muted-fm">{{ $s->alamat ? \Illuminate\Support\Str::limit($s->alamat, 40) : '-' }}</td>
                            <td class="text-nowrap">
                                <button type="button" class="icon-btn" title="Ubah" aria-label="Ubah"
                                        data-bs-toggle="modal" data-bs-target="#modalUbah"
                                        data-id="{{ $s->id_supplier }}"
                                        data-nama="{{ $s->nama_supplier }}"
                                        data-telp="{{ $s->no_telepon }}"
                                        data-alamat="{{ $s->alamat }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </button>
                                <button type="button" class="icon-btn danger" title="Hapus" aria-label="Hapus"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                                        data-id="{{ $s->id_supplier }}"
                                        data-nama="{{ $s->nama_supplier }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted-fm">Belum ada data supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>
                @if ($supplier->total() > 0)
                    Menampilkan {{ $supplier->firstItem() }} sampai {{ $supplier->lastItem() }} dari {{ $supplier->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            <span class="pager">
                @if ($supplier->onFirstPage())
                    <span class="disabled">&lsaquo; Sebelumnya</span>
                @else
                    <a href="{{ $supplier->previousPageUrl() }}">&lsaquo; Sebelumnya</a>
                @endif

                @if ($supplier->hasMorePages())
                    <a href="{{ $supplier->nextPageUrl() }}">Berikutnya &rsaquo;</a>
                @else
                    <span class="disabled">Berikutnya &rsaquo;</span>
                @endif
            </span>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="judulTambah" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('master.supplier.store', [], false) }}" class="modal-content modal-fm" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="tambah">

                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulTambah">Tambah Supplier</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @include('supplier._form', ['mode' => 'tambah'])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-fm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUbah" tabindex="-1" aria-labelledby="judulUbah" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formUbah" action="" class="modal-content modal-fm" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="ubah">
                <input type="hidden" name="_id" value="">

                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulUbah">Ubah Supplier</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @include('supplier._form', ['mode' => 'ubah'])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-fm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulHapus" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formHapus" action="" class="modal-content modal-fm">
                @csrf
                @method('DELETE')

                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulHapus">Hapus Supplier</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Hapus supplier <strong id="hapus-nama"></strong>?</p>
                    <p class="small text-muted-fm mb-0">Supplier yang sudah dipakai pada pembelian stok tidak dapat dihapus.</p>
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
        const urlBase = @json(route('master.supplier.index', [], false));

        const modalUbah = document.getElementById('modalUbah');
        modalUbah.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            if (!btn) return; // dibuka otomatis setelah error validasi: pakai isian dari server

            const form = document.getElementById('formUbah');
            form.action = urlBase + '/' + btn.dataset.id;
            form.querySelector('[name=_id]').value = btn.dataset.id;
            form.querySelector('[name=nama_supplier]').value = btn.dataset.nama;
            form.querySelector('[name=no_telepon]').value = btn.dataset.telp;
            form.querySelector('[name=alamat]').value = btn.dataset.alamat;
        });

        const modalHapus = document.getElementById('modalHapus');
        modalHapus.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            if (!btn) return;

            document.getElementById('formHapus').action = urlBase + '/' + btn.dataset.id;
            document.getElementById('hapus-nama').textContent = btn.dataset.nama;
        });

        @if ($errors->any() && old('_modal'))
            @if (old('_modal') === 'ubah')
                document.getElementById('formUbah').action = urlBase + '/' + @json(old('_id'));
                bootstrap.Modal.getOrCreateInstance(modalUbah).show();
            @else
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambah')).show();
            @endif
        @endif
    });
</script>
@endpush