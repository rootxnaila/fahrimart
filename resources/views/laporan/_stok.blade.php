@php
    $c = config('fahrimart.analisis');
    $skala = max(1, $c['lead_time_hari'] + $c['buffer_hari']);
    $persen = $s['sisa_hari'] === null ? 0 : min(100, round($s['sisa_hari'] / $skala * 100));
@endphp

<div class="stok-item">
    <div class="d-flex align-items-center gap-3">
        <span class="badge-stok {{ $s['status'] }}">{{ $s['label'] }}</span>
        <div>
            <div class="fw-semibold">{{ $s['nama'] }}</div>
            <div class="small text-muted-fm">{{ $s['kode'] }} &middot; stok {{ $s['stok'] }} {{ $s['satuan'] }}</div>
        </div>
    </div>

    <div>
        <div class="small text-muted-fm">Sisa stok</div>
        <div class="fw-semibold">
            {{ $s['sisa_hari'] !== null ? number_format($s['sisa_hari'], 1, ',', '.') . ' hari' : ($s['status'] === 'habis' ? 'Habis' : '-') }}
        </div>
        <div class="sisa-bar {{ $s['status'] }}"><span style="width: {{ $persen }}%"></span></div>
        @if ($s['estimasi_habis'] && $s['status'] !== 'habis')
            <div class="small text-muted-fm mt-1">Habis sekitar {{ \Carbon\Carbon::parse($s['estimasi_habis'])->format('d/m/Y') }}</div>
        @endif
    </div>

    <div>
        <div class="small text-muted-fm">Saran restok</div>
        <div class="fw-semibold">{{ $s['rekomendasi_restok'] ? '+' . $s['rekomendasi_restok'] . ' ' . $s['satuan'] : '-' }}</div>
    </div>
</div>