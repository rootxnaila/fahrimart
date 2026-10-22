@php($habis = $p->stok <= 0)

<div class="produk-card {{ $habis ? 'habis' : '' }}" data-id="{{ $p->id_barang }}">
    <div class="produk-gambar">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 7h12l1 13H5z"/><path d="M9 7a3 3 0 0 1 6 0"/>
        </svg>
    </div>
    <div class="produk-nama">{{ $p->nama_barang }}</div>
    <div class="produk-desk">{{ $p->kategori }} &middot; {{ $p->satuan }}</div>
    <div class="produk-harga">{{ number_format($p->harga_jual, 0, ',', '.') }}</div>
    <div class="produk-stok">Stok : <span class="stok-nilai">{{ $p->stok }}</span></div>

    <div class="produk-aksi">
        @if ($varian === 'populer')
            <span></span>
            <button type="button" class="btn-add" data-aksi="tambah" aria-label="Tambah ke keranjang" {{ $habis ? 'disabled' : '' }}>+</button>
        @else
            <span class="stepper">
                <button type="button" data-aksi="step-minus" aria-label="Kurangi" {{ $habis ? 'disabled' : '' }}>&minus;</button>
                <span class="step-nilai">1</span>
                <button type="button" data-aksi="step-plus" aria-label="Tambah" {{ $habis ? 'disabled' : '' }}>+</button>
            </span>
            <button type="button" class="btn-bag" data-aksi="bag" aria-label="Masukkan ke keranjang" {{ $habis ? 'disabled' : '' }}>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 7h12l1 13H5z"/><path d="M9 7a3 3 0 0 1 6 0"/>
                </svg>
            </button>
        @endif
    </div>
</div>