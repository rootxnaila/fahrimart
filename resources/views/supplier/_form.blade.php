@php
    $isOld = old('_modal') === $mode;
    $val = fn ($f) => $isOld ? old($f) : '';
    $err = fn ($f) => $isOld ? $errors->first($f) : '';
@endphp

<div class="mb-3">
    <label for="{{ $mode }}-nama" class="form-label">Nama Supplier</label>
    <input type="text" id="{{ $mode }}-nama" name="nama_supplier" value="{{ $val('nama_supplier') }}"
           class="form-control input-fm input-fm-lg {{ $err('nama_supplier') ? 'is-invalid' : '' }}"
           placeholder="Contoh: PT. Cahaya Baru" maxlength="100">
    @if ($err('nama_supplier'))
        <div class="invalid-feedback">{{ $err('nama_supplier') }}</div>
    @endif
</div>

<div class="mb-3">
    <label for="{{ $mode }}-telp" class="form-label">Nomor Telepon</label>
    <input type="text" inputmode="tel" id="{{ $mode }}-telp" name="no_telepon" value="{{ $val('no_telepon') }}"
           class="form-control input-fm input-fm-lg {{ $err('no_telepon') ? 'is-invalid' : '' }}"
           placeholder="Masukkan nomor" maxlength="15">
    @if ($err('no_telepon'))
        <div class="invalid-feedback">{{ $err('no_telepon') }}</div>
    @endif
</div>

<div class="mb-2">
    <label for="{{ $mode }}-alamat" class="form-label">Alamat</label>
    <input type="text" id="{{ $mode }}-alamat" name="alamat" value="{{ $val('alamat') }}"
           class="form-control input-fm input-fm-lg {{ $err('alamat') ? 'is-invalid' : '' }}"
           placeholder="Contoh: Jl. Kemayoran" maxlength="500">
    @if ($err('alamat'))
        <div class="invalid-feedback">{{ $err('alamat') }}</div>
    @endif
</div>