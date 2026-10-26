<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan {{ $dari->format('d/m/Y') }} - {{ $sampai->format('d/m/Y') }}</title>
    <style>
        @page { margin: 40px 36px 60px 36px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #222; }
        h1 { margin: 0; font-size: 18px; color: #1a7630; }
        h2 { margin: 18px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #40aa55; font-size: 12px; color: #1a7630; }
        .muted { color: #666; }
        .small { font-size: 8.5px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 5px 6px; border: 1px solid #ddd; background: #f1f1f1; text-align: left; }
        td { padding: 4px 6px; border: 1px solid #ddd; vertical-align: top; }
        .r { text-align: right; }
        .kartu td { padding: 8px 10px; border: 1px solid #cfe5d3; background: #f1f8f2; }
        .kartu .nilai { font-size: 14px; font-weight: bold; color: #1a7630; }
        .ringkasan { padding: 8px 10px; border-left: 3px solid #40aa55; background: #f1f8f2; line-height: 1.5; }
        .prio { font-weight: bold; }
        .Tinggi { color: #da2c2f; }
        .Sedang { color: #95661b; }
        .Rendah { color: #2f8a43; }
        .footer { position: fixed; bottom: -36px; left: 0; right: 0; height: 24px; text-align: center; font-size: 8px; color: #777; }
        .footer .halaman:after { content: "Halaman " counter(page); }
    </style>
</head>
<body>
    <div class="footer">
        {{ $toko['nama'] }} &middot; Laporan Penjualan &middot; Dicetak oleh {{ $pengguna->nama }} &middot;
        <span class="halaman"></span>
    </div>

    <h1>{{ $toko['nama'] }}</h1>
    <div class="muted">{{ $toko['alamat'] }}</div>

    <div style="margin-top: 12px; font-size: 15px; font-weight: bold;">Laporan Penjualan</div>
    <div>Periode: {{ $dari->format('d/m/Y') }} sampai {{ $sampai->format('d/m/Y') }}</div>
    <div class="muted small">Dicetak oleh {{ $pengguna->nama }} ({{ $pengguna->role }}) pada {{ $dicetakPada->format('d/m/Y H:i') }}</div>

    {{-- Ringkasan --}}
    <h2>Ringkasan</h2>
    <table class="kartu">
        <tr>
            <td style="width: 34%;">
                <div class="muted">Total Pendapatan (termasuk PPN)</div>
                <div class="nilai">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </td>
            <td style="width: 33%;">
                <div class="muted">Total Transaksi</div>
                <div class="nilai">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
            </td>
            <td style="width: 33%;">
                <div class="muted">Rata-rata per Transaksi</div>
                <div class="nilai">Rp {{ number_format($totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <h2>Pendapatan per Metode Pembayaran</h2>
    <table>
        <thead>
            <tr>
                <th>Metode</th>
                <th class="r">Jumlah Transaksi</th>
                <th class="r">Total</th>
                <th class="r">Porsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perMetode as $m)
                <tr>
                    <td>{{ $m->metode_bayar }}</td>
                    <td class="r">{{ number_format($m->jumlah, 0, ',', '.') }}</td>
                    <td class="r">Rp {{ number_format($m->total, 0, ',', '.') }}</td>
                    <td class="r">{{ $totalPendapatan > 0 ? number_format($m->total / $totalPendapatan * 100, 1, ',', '.') : 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>10 Produk Terlaris</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th>Barang</th>
                <th class="r">Terjual</th>
                <th class="r">Omzet Barang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($terlaris as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->nama_barang }}</td>
                    <td class="r">{{ number_format($t->qty, 0, ',', '.') }} {{ $t->satuan }}</td>
                    <td class="r">Rp {{ number_format($t->omzet, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Tidak ada penjualan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Analisis AI --}}
    <h2>Analisis AI</h2>
    <div class="muted small" style="margin-bottom: 6px;">
        Dihitung {{ $dibuatPada->format('d/m/Y H:i') }} dari data penjualan sampai kemarin. Prakiraan dan insight bersifat saran.
        </div>

    <strong>Prakiraan Omzet 7 Hari ke Depan</strong>
    @if ($ramalan)
        <div class="muted small" style="margin: 2px 0 4px;">
            Model: {{ $ramalan['model'] }}@if ($ramalan['wape'] !== null), galat uji (WAPE) {{ number_format($ramalan['wape'] * 100, 1, ',', '.') }}%@endif.
            Rentang adalah kepercayaan sekitar 80%.
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="r">Prakiraan</th>
                    <th class="r">Batas Bawah</th>
                    <th class="r">Batas Atas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($barisRamalan as $b)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($b['tanggal'])->format('d/m/Y') }}</td>
                        <td class="r">Rp {{ number_format($b['ramalan'], 0, ',', '.') }}</td>
                        <td class="r">Rp {{ number_format($b['bawah'], 0, ',', '.') }}</td>
                        <td class="r">Rp {{ number_format($b['atas'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="muted">Data penjualan belum cukup untuk membuat prakiraan (minimal 7 hari bertransaksi).</div>
    @endif

    <div style="margin-top: 12px;"><strong>Peringatan Stok Menipis</strong></div>
    <table style="margin-top: 4px;">
        <thead>
            <tr>
                <th>Status</th>
                <th>Barang</th>
                <th class="r">Stok</th>
                <th class="r">Sisa</th>
                <th>Perkiraan Habis</th>
                <th class="r">Saran Restok</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perhatian as $s)
                <tr>
                    <td>{{ $s['label'] }}</td>
                    <td>{{ $s['nama'] }}</td>
                    <td class="r">{{ $s['stok'] }} {{ $s['satuan'] }}</td>
                    <td class="r">{{ $s['sisa_hari'] !== null ? number_format($s['sisa_hari'], 1, ',', '.') . ' hari' : '-' }}</td>
                    <td>{{ $s['estimasi_habis'] ? \Carbon\Carbon::parse($s['estimasi_habis'])->format('d/m/Y') : '-' }}</td>
                    <td class="r">{{ $s['rekomendasi_restok'] ? '+' . $s['rekomendasi_restok'] . ' ' . $s['satuan'] : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Tidak ada barang yang perlu perhatian stok.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px;">
        <strong>Insight Penjualan</strong>
        <span class="muted small">(sumber: {{ $insight['sumber'] === 'llm' ? 'AI (LLM)' : 'analisis aturan' }})</span>
    </div>
    <div class="ringkasan" style="margin-top: 4px;">{{ $insight['ringkasan'] }}</div>

    @if (! empty($insight['temuan']))
        <ul style="margin: 6px 0 0 14px; padding: 0;">
            @foreach ($insight['temuan'] as $t)
                <li>{{ $t }}</li>
            @endforeach
        </ul>
    @endif

    <div style="margin-top: 10px;"><strong>Langkah Berikutnya (Next Step)</strong></div>
    @forelse ($insight['next_steps'] as $langkah)
        <div style="margin: 4px 0 0;">
            <span class="prio {{ $langkah['prioritas'] }}">[{{ $langkah['prioritas'] }}]</span>
            {{ $langkah['tindakan'] }}
            <div class="muted small" style="margin-left: 38px;">{{ $langkah['alasan'] }}</div>
        </div>
    @empty
        <div class="muted">Belum ada saran tindakan.</div>
    @endforelse

    {{-- Riwayat transaksi --}}
    <h2>Riwayat Transaksi</h2>
    @if ($totalTransaksi > $batasBaris)
        <div class="muted small" style="margin-bottom: 4px;">
            Menampilkan {{ $batasBaris }} transaksi terbaru dari total {{ number_format($totalTransaksi, 0, ',', '.') }} transaksi pada periode ini.
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Nama Kasir</th>
                <th>Metode</th>
                <th class="r">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayat as $r)
                @php($waktu = \Carbon\Carbon::parse($r->waktu_transaksi))
                <tr>
                    <td>{{ $r->id_transaksi }}</td>
                    <td>{{ $waktu->format('d/m/Y') }}</td>
                    <td>{{ $waktu->format('H:i') }}</td>
                    <td>{{ $r->kasir ?: '-' }}</td>
                    <td>{{ $r->metode_bayar }}</td>
                    <td class="r">{{ number_format($r->total_belanja, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>