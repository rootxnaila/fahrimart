@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="page-title">Laporan Penjualan</h1>
            <p class="page-subtitle mb-0">Pantau performa penjualan toko Anda</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" action="{{ route('laporan.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <input type="date" name="dari" value="{{ $dari->toDateString() }}" max="{{ today()->toDateString() }}" class="input-fm">
                <span>-</span>
                <input type="date" name="sampai" value="{{ $sampai->toDateString() }}" max="{{ today()->toDateString() }}" class="input-fm">
                <button type="submit" class="btn btn-fm-outline btn-sm px-4">Terapkan</button>
            </form>

            <a href="{{ route('laporan.pdf', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]) }}"
               class="btn btn-fm btn-sm px-4">Export PDF</a>
        </div>
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

    {{-- ringkasan --}}
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

    {{-- tren penjualan --}}
    <div class="card-fm mb-4">
        <h2 class="section-title">Tren Penjualan</h2>
        <div class="chart-box"><canvas id="chartTren"></canvas></div>
    </div>

    {{-- ===================== ANALISIS AI ===================== --}}
    @php
        $rpRingkas = function ($v) {
            if ($v >= 1000000000) {
                return 'Rp ' . number_format($v / 1000000000, 1, ',', '.') . ' M';
            }
            if ($v >= 1000000) {
                return 'Rp ' . number_format($v / 1000000, 1, ',', '.') . ' jt';
            }
            if ($v >= 1000) {
                return 'Rp ' . number_format($v / 1000, 0, ',', '.') . ' rb';
            }

            return 'Rp ' . number_format($v, 0, ',', '.');
        };
        $panah = fn ($p) => ($p >= 0 ? '&#9650; ' : '&#9660; ') . number_format(abs($p), 1, ',', '.') . '%';
        $kelas = fn ($p) => $p === null ? '' : ($p >= 0 ? 'naik' : 'turun');

        $perhatianTotal = $kpi['habis'] + $kpi['kritis'] + $kpi['waspada'];
        $akurasi = ($ramalan && $ramalan['wape'] !== null) ? max(0, round(100 - $ramalan['wape'] * 100)) : null;
        $langkahUtama = array_slice($insight['next_steps'], 0, 3);
        $langkahLain = array_slice($insight['next_steps'], 3);
        $stokUtama = $perhatian->take(5);
        $stokLain = $perhatian->slice(5);
        $ac = config('fahrimart.analisis');
    @endphp

    <div class="ai-header">
        <div class="d-flex align-items-center gap-3">
            <span class="ai-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 16l.8 2 2 .8-2 .8L19 22l-.8-2.4-2-.8 2-.8z"/></svg>
            </span>
            <div>
                <h2 class="h4 mb-0">Analisis AI</h2>
                <div class="small text-muted-fm">
                    Diperbarui {{ $dibuatPada->format('d/m/Y H:i') }} &middot; berdasarkan penjualan sampai kemarin
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('laporan.analisis.perbarui') }}">
            @csrf
            <button type="submit" class="btn btn-fm-outline btn-sm px-4">Perbarui Analisis</button>
        </form>
    </div>

    {{-- angka kunci --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Prakiraan omzet 7 hari ke depan</div>
                <div class="kpi-nilai">{{ $kpi['ramalan_7_hari'] !== null ? $rpRingkas($kpi['ramalan_7_hari']) : '-' }}</div>
                @if ($kpi['ramalan_perubahan_persen'] !== null)
                    <div class="kpi-sub {{ $kelas($kpi['ramalan_perubahan_persen']) }}">{!! $panah($kpi['ramalan_perubahan_persen']) !!} dari 7 hari terakhir</div>
                @else
                    <div class="kpi-sub">Data belum cukup</div>
                @endif
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Omzet 7 hari terakhir</div>
                <div class="kpi-nilai">{{ $rpRingkas($kpi['omzet_7_hari']) }}</div>
                @if ($kpi['tren_persen'] !== null)
                    <div class="kpi-sub {{ $kelas($kpi['tren_persen']) }}">{!! $panah($kpi['tren_persen']) !!} dari minggu sebelumnya</div>
                @else
                    <div class="kpi-sub">Belum ada pembanding</div>
                @endif
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Barang perlu perhatian stok</div>
                <div class="kpi-nilai">{{ $perhatianTotal }} barang</div>
                <div>
                    @if ($kpi['habis'])<span class="kpi-chip habis">Habis {{ $kpi['habis'] }}</span>@endif
                    @if ($kpi['kritis'])<span class="kpi-chip kritis">Kritis {{ $kpi['kritis'] }}</span>@endif
                    @if ($kpi['waspada'])<span class="kpi-chip waspada">Waspada {{ $kpi['waspada'] }}</span>@endif
                    @if ($perhatianTotal === 0)<span class="kpi-sub">Semua stok aman</span>@endif
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Akurasi uji prakiraan</div>
                <div class="kpi-nilai">{{ $akurasi !== null ? $akurasi . '%' : '-' }}</div>
                <div class="kpi-sub">{{ $ramalan ? 'Model: ' . $ramalan['model'] : 'Data belum cukup' }}</div>
            </div>
        </div>
    </div>

    {{-- ringkasan --}}
    <div class="insight-ringkasan mb-4">{{ $insight['ringkasan'] }}</div>

    {{-- langkah berikutnya --}}
    <div class="card-fm mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="ai-judul mb-0">Langkah Berikutnya</h3>
            <span class="label-sumber">{{ $insight['sumber'] === 'llm' ? 'Disusun oleh AI (LLM)' : 'Disusun dari aturan analisis' }}</span>
        </div>

        @if (! empty($insight['llm_gagal']))
            <div class="alert alert-warning py-2 small">AI eksternal tidak tersedia, jadi yang ditampilkan adalah analisis berbasis aturan.</div>
        @endif

        @forelse ($langkahUtama as $l)
            @include('laporan._langkah', ['l' => $l])
        @empty
            <p class="text-muted-fm mb-0">Belum ada saran tindakan.</p>
        @endforelse

        @if ($langkahLain)
            <div class="collapse" id="langkahLain">
                @foreach ($langkahLain as $l)
                    @include('laporan._langkah', ['l' => $l])
                @endforeach
            </div>
            <button type="button" class="btn btn-link px-0" data-bs-toggle="collapse" data-bs-target="#langkahLain">
                Lihat {{ count($langkahLain) }} langkah lainnya
            </button>
        @endif
    </div>

    {{-- peringatan stok --}}
    <div class="card-fm mb-4">
        <h3 class="ai-judul mb-3">Barang yang Perlu Diisi Ulang</h3>

        @forelse ($stokUtama as $s)
            @include('laporan._stok', ['s' => $s])
        @empty
            <p class="text-muted-fm mb-0">Tidak ada barang yang perlu perhatian stok.</p>
        @endforelse

        @if ($stokLain->isNotEmpty())
            <div class="collapse" id="stokLain">
                @foreach ($stokLain as $s)
                    @include('laporan._stok', ['s' => $s])
                @endforeach
            </div>
            <button type="button" class="btn btn-link px-0 mt-2" data-bs-toggle="collapse" data-bs-target="#stokLain">
                Lihat {{ $stokLain->count() }} barang lainnya
            </button>
        @endif
    </div>

    {{-- grafik prakiraan --}}
    <div class="card-fm mb-4">
        <h3 class="ai-judul mb-1">Prakiraan Omzet {{ $ramalan ? count($ramalan['tanggal']) : '' }} Hari ke Depan</h3>

        @if ($ramalan)
            <p class="small text-muted-fm mb-3">
                Garis oranye putus-putus adalah prakiraan. Area hijau muda adalah rentang yang mungkin terjadi (kira-kira 80%). Omzet termasuk PPN.
            </p>
            <div class="chart-box"><canvas id="chartRamalan"></canvas></div>
        @else
            <p class="mb-0 text-muted-fm">Data penjualan belum cukup untuk membuat prakiraan (minimal 7 hari bertransaksi).</p>
        @endif
    </div>

    {{-- temuan dan cara membaca --}}
    <div class="card-fm mb-4">
        <div class="accordion accordion-flush" id="akordeonAi">
            @if (! empty($insight['temuan']))
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#temuanAi">
                            Temuan lainnya ({{ count($insight['temuan']) }})
                        </button>
                    </h3>
                    <div id="temuanAi" class="accordion-collapse collapse" data-bs-parent="#akordeonAi">
                        <div class="accordion-body">
                            <ul class="mb-0">
                                @foreach ($insight['temuan'] as $t)
                                    <li class="mb-1">{{ $t }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#caraBaca">
                        Cara membaca analisis ini
                    </button>
                </h3>
                <div id="caraBaca" class="accordion-collapse collapse" data-bs-parent="#akordeonAi">
                    <div class="accordion-body small">
                        <ul class="mb-0">
                            <li class="mb-1"><strong>Habis:</strong> stok sudah 0.</li>
                            <li class="mb-1"><strong>Kritis:</strong> stok diperkirakan habis dalam {{ $ac['lead_time_hari'] }} hari atau kurang (kira-kira waktu menunggu barang dari supplier tiba).</li>
                            <li class="mb-1"><strong>Waspada:</strong> stok diperkirakan habis dalam {{ $ac['lead_time_hari'] + $ac['buffer_hari'] }} hari atau kurang.</li>
                            <li class="mb-1"><strong>Saran restok:</strong> jumlah yang menutup {{ $ac['lead_time_hari'] }} hari masa tunggu, {{ $ac['target_cover_hari'] }} hari penjualan, dan {{ $ac['stok_pengaman_hari'] }} hari cadangan.</li>
                            <li class="mb-1"><strong>Akurasi uji:</strong> model diuji pada 7 hari terakhir. Angkanya 100% dikurangi tingkat galat, jadi semakin tinggi semakin akurat.</li>
                            <li class="mb-1"><strong>Sumber angka:</strong> semua angka dihitung oleh sistem dari data penjualan. Bila AI (LLM) aktif, ia hanya merangkum dan mengurutkan prioritas.</li>
                            <li>Prakiraan dan saran bersifat masukan. Keputusan akhir tetap pada pemilik toko.</li>
                        </ul>
                    </div>
                </div>
            </div>
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

        // tren penjualan
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

        // prakiraan
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
                        { label: 'Rentang yang mungkin terjadi', data: ram.atas, borderColor: 'transparent', backgroundColor: 'rgba(64,170,85,0.15)', pointRadius: 0, fill: '-1' },
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