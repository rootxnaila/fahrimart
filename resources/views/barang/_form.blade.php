@php
    // isi ulang dan tampilkan pesan error hanya untuk modal yang sedang dikirim
    $isOld = old('_modal') === $mode;
    $val = fn ($f) => $isOld ? old($f) : '';
    $err = fn ($f) => $isOld ? $errors->first($f) : '';
@endphp

<div class="mb-3">
    <label for="{{ $mode }}-nama" class="form-label">Nama Barang</label>
    <input type="text" id="{{ $mode }}-nama" name="nama_barang" value="{{ $val('nama_barang') }}"
           class="form-control input-fm input-fm-lg {{ $err('nama_barang') ? 'is-invalid' : '' }}"
           placeholder="Contoh: Beras Sederhana" maxlength="100">
    @if ($err('nama_barang'))
        <div class="invalid-feedback">{{ $err('nama_barang') }}</div>
    @endif
</div>

<div class="mb-3">
    <label for="{{ $mode }}-kategori" class="form-label">Kategori</label>
    <input type="text" id="{{ $mode }}-kategori" name="kategori" value="{{ $val('kategori') }}" list="daftarKategori"
           class="form-control input-fm input-fm-lg {{ $err('kategori') ? 'is-invalid' : '' }}"
           placeholder="Pilih atau ketik kategori" maxlength="50" autocomplete="off">
    @if ($err('kategori'))
        <div class="invalid-feedback">{{ $err('kategori') }}</div>
    @endif
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <label for="{{ $mode }}-beli" class="form-label">Harga Beli</label>
        <div class="position-relative">
            <span class="rp-prefix">Rp</span>
            <input type="number" id="{{ $mode }}-beli" name="harga_beli" value="{{ $val('harga_beli') }}" min="0" step="any"
                   class="form-control input-fm input-fm-lg has-prefix {{ $err('harga_beli') ? 'is-invalid' : '' }}" placeholder="0">
            @if ($err('harga_beli'))
                <div class="invalid-feedback">{{ $err('harga_beli') }}</div>
            @endif
        </div>
    </div>
    <div class="col-6">
        <label for="{{ $mode }}-jual" class="form-label">Harga Jual</label>
        <div class="position-relative">
            <span class="rp-prefix">Rp</span>
            <input type="number" id="{{ $mode }}-jual" name="harga_jual" value="{{ $val('harga_jual') }}" min="0" step="any"
                   class="form-control input-fm input-fm-lg has-prefix {{ $err('harga_jual') ? 'is-invalid' : '' }}" placeholder="0">
            @if ($err('harga_jual'))
                <div class="invalid-feedback">{{ $err('harga_jual') }}</div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-2">
    <div class="col-6">
        <label for="{{ $mode }}-satuan" class="form-label">Satuan</label>
        <input type="text" id="{{ $mode }}-satuan" name="satuan" value="{{ $val('satuan') }}" list="daftarSatuan"
               class="form-control input-fm input-fm-lg {{ $err('satuan') ? 'is-invalid' : '' }}"
               placeholder="Pcs, Kg, Karung..." maxlength="20" autocomplete="off">
        @if ($err('satuan'))
            <div class="invalid-feedback">{{ $err('satuan') }}</div>
        @endif
    </div>

    <div class="col-6">
        @if ($withStok)
            <label for="{{ $mode }}-stok" class="form-label">Stok Awal</label>
            <input type="number" id="{{ $mode }}-stok" name="stok" value="{{ $val('stok') }}" min="0" step="1"
                   class="form-control input-fm input-fm-lg {{ $err('stok') ? 'is-invalid' : '' }}" placeholder="0">
            @if ($err('stok'))
                <div class="invalid-feedback">{{ $err('stok') }}</div>
            @endif
        @else
            <label class="form-label">Stok Saat Ini</label>
            <div class="form-control input-fm input-fm-lg d-flex align-items-center bg-light" id="ubah-stok-info">-</div>
        @endif
    </div>
</div>

@unless ($withStok)
    <p class="small text-muted-fm mb-0">Stok tidak diubah di sini. Stok berubah lewat penjualan, pembelian stok, atau retur.</p>
@endunless