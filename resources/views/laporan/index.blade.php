@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="page-title">Laporan Penjualan</h1>
            <p class="page-subtitle mb-0">Pantau performa penjualan toko Anda</p>
        </div>

        <form method="GET" action="{{ route('laporan.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            <input type="date" name="dari" value="{{ $dari->toDateString() }}" max="{{ today()->toDateString() }}" class="input-fm">
            <span>-</span>
            <input type="date" name="sampai" value="{{ $sampai->toDateString() }}" max="{{ today()->toDateString() }}" class="input-fm">
            <button type="submit" class="btn btn-fm btn-sm px-4">Terapkan</button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-fm-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Ringkasan --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
                </div>
                <div>
                    <div class="stat-label">Total Pendapatan ({{ $dari->format('d/m/Y') }} - {{ $sampai->format('d/m/Y') }})</div>
                    <div class="stat-value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="M9 13l2 2 4-4"/></svg>
                </div>
                <div>
                    <div class="stat-label">Total Transaksi</div>
                    <div class="stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tren penjualan --}}
    <div class="card-fm mb-4">
        <h2 class="section-title">Tren Penjualan</h2>
        <div class="chart-box"><canvas id="chartTren"></canvas></div>
    </div>

    {{-- ===================== ANALISIS AI ===================== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h4 mb-0">Analisis AI</h2>
            <div class="small text-muted-fm">
                Dihitung {{ $dibuatPada->format('d/m/Y H:i') }}, berdasarkan data penjualan sampai kemarin.
            </div>
        </div>
        <form method="POST" action="{{ route('laporan.analisis.perbarui') }}">
            @csrf
            <button type="submit" class="btn btn-fm-outline btn-sm px-4">Perbarui Analisis</button>
        </form>
    </div>

    {{-- Prakiraan --}}
    <div class="card-fm mb-4">
        <h2 class="section-title">AI Forecasting: Prakiraan Omzet {{ $ramalan ? count($ramalan['tanggal']) : '' }} Hari ke Depan</h2>

        @if ($ramalan)
            <div class="chart-box mb-3"><canvas id="chartRamalan"></canvas></div>
            <div class="small text-muted-fm">
                Model terpilih: <strong>{{ $ramalan['model'] }}</strong>
                @if ($ramalan['wape'] !== null)
                    &middot; galat uji (WAPE) {{ number_format($ramalan['wape'] * 100, 1, ',', '.') }}%
                @endif
                &middot; prakiraan 7 hari ke depan: Rp {{ number_format(array_sum(array_slice($ramalan['ramalan'], 0, 7)), 0, ',', '.') }}.
                Area hijau muda adalah rentang kepercayaan sekitar 80%. Omzet termasuk PPN.
            </div>
        @else
            <p class="mb-0 text-muted-fm">Data penjualan belum cukup untuk membuat prakiraan (minimal 7 hari bertransaksi).</p>
        @endif
    </div>

    {{-- Peringatan stok --}}
    <div class="card-fm mb-4">
        <h2 class="section-title">Peringatan Stok Menipis</h2>

        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Barang</th>
                        <th>Stok</th>
                        <th>Sisa</th>
                        <th>Perkiraan Habis</th>
                        <th>Saran Restok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perhatian as $s)
                        <tr>
                            <td><span class="badge-stok {{ $s['status'] }}">{{ $s['label'] }}</span></td>
                            <td>{{ $s['nama'] }} <small class="text-muted-fm">{{ $s['kode'] }}</small></td>
                            <td>{{ $s['stok'] }} <small class="text-muted-fm">{{ $s['satuan'] }}</small></td>
                            <td>{{ $s['sisa_hari'] !== null ? number_format($s['sisa_hari'], 1, ',', '.') . ' hari' : '-' }}</td>
                            <td>{{ $s['estimasi_habis'] ? \Carbon\Carbon::parse($s['estimasi_habis'])->format('d/m/Y') : '-' }}</td>
                            <td class="fw-semibold">{{ $s['rekomendasi_restok'] ? '+' . $s['rekomendasi_restok'] . ' ' . $s['satuan'] : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted-fm">Tidak ada barang yang perlu perhatian stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="small text-muted-fm mt-3">
            Status dihitung dari prakiraan kebutuhan harian: Kritis jika stok habis dalam
            {{ config('fahrimart.analisis.lead_time_hari') }} hari atau kurang, Waspada jika dalam
            {{ config('fahrimart.analisis.lead_time_hari') + config('fahrimart.analisis.buffer_hari') }} hari atau kurang.
        </div>
    </div>

    {{-- Insight --}}
    <div class="card-fm mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="section-title mb-0">AI Sales Insight dan Langkah Berikutnya</h2>
            <span class="label-sumber">
                {{ $insight['sumber'] === 'llm' ? 'Sumber: AI (LLM)' : 'Sumber: analisis aturan' }}
            </span>
        </div>

        @if (! empty($insight['llm_gagal']))
            <div class="alert alert-warning py-2 small">AI eksternal tidak tersedia, jadi yang ditampilkan adalah analisis berbasis aturan.</div>
        @endif

        <div class="insight-ringkasan mb-3">{{ $insight['ringkasan'] }}</div>

        @if (! empty($insight['temuan']))
            <h3 class="h6 mt-4">Temuan</h3>
            <ul class="mb-4">
                @foreach ($insight['temuan'] as $t)
                    <li>{{ $t }}</li>
                @endforeach
            </ul>
        @endif

        <h3 class="h6">Langkah Berikutnya (Next Step)</h3>
        @forelse ($insight['next_steps'] as $langkah)
            <div class="step-item">
                <div><span class="badge-prio {{ $langkah['prioritas'] }}">{{ $langkah['prioritas'] }}</span></div>
                <div>
                    <div class="fw-semibold">{{ $langkah['tindakan'] }}</div>
                    <div class="small text-muted-fm">{{ $langkah['alasan'] }}</div>
                </div>
            </div>
        @empty
            <p class="text-muted-fm mb-0">Belum ada saran tindakan.</p>
        @endforelse

        <div class="small text-muted-fm mt-3">
            Prakiraan dan insight bersifat saran. Angka dihitung oleh sistem dari data penjualan, dan keputusan akhir tetap pada pemilik toko.
        </div>
    </div>

    {{-- Riwayat transaksi --}}
    <div class="card-fm">
        <h2 class="section-title">Riwayat Transaksi</h2>

        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Nama Kasir</th>
                        <th>Metode</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        @php($waktu = \Carbon\Carbon::parse($r->waktu_transaksi))
                        <tr>
                            <td>{{ $r->id_transaksi }}</td>
                            <td class="text-muted-fm">{{ $waktu->format('d/m/Y') }}</td>
                            <td class="text-muted-fm">{{ $waktu->format('H:i') }}</td>
                            <td class="text-muted-fm">{{ $r->kasir ?: '-' }}</td>
                            <td class="text-muted-fm">{{ $r->metode_bayar }}</td>
                            <td>{{ number_format($r->total_belanja, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted-fm">Tidak ada transaksi pada rentang ini.</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rp = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n));
        const sumbuY = (v) => v >= 1e6 ? (v / 1e6).toFixed(1).replace('.0', '') + ' jt' : (v >= 1e3 ? Math.round(v / 1e3) + ' rb' : v);
        const tooltip = { callbacks: { label: (c) => ' ' + c.dataset.label + ': Rp ' + rp(c.parsed.y) } };

        // Tren penjualan
        const tren = @json($chartTren);
        new Chart(document.getElementById('chartTren'), {
            type: 'line',
            data: {
                labels: tren.label,
                datasets: [{
                    label: 'Omzet',
                    data: tren.nilai,
                    borderColor: '#40AA55',
                    backgroundColor: 'rgba(64,170,85,0.15)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: tren.label.length > 45 ? 0 : 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: { y: { beginAtZero: true, ticks: { callback: sumbuY } }, x: { ticks: { maxTicksLimit: 10 } } },
            },
        });

        // Prakiraan
        const ram = @json($chartRamalan);
        const kanvas = document.getElementById('chartRamalan');
        if (ram && kanvas) {
            new Chart(kanvas, {
                type: 'line',
                data: {
                    labels: ram.label,
                    datasets: [
                        { label: 'Aktual', data: ram.aktual, borderColor: '#40AA55', backgroundColor: '#40AA55', tension: 0.3, pointRadius: 3 },
                        { label: 'Prakiraan', data: ram.prediksi, borderColor: '#E97A06', backgroundColor: '#E97A06', borderDash: [6, 4], tension: 0.3, pointRadius: 3 },
                        { label: 'Batas bawah', data: ram.bawah, borderColor: 'transparent', pointRadius: 0, fill: false },
                        { label: 'Rentang kepercayaan 80%', data: ram.atas, borderColor: 'transparent', backgroundColor: 'rgba(64,170,85,0.15)', pointRadius: 0, fill: '-1' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { filter: (item) => item.text !== 'Batas bawah' } },
                        tooltip,
                    },
                    scales: { y: { beginAtZero: true, ticks: { callback: sumbuY } } },
                },
            });
        }
    });
</script>
@endpush