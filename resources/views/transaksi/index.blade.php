@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
    <h1 class="page-title">Riwayat Transaksi</h1>
    <p class="page-subtitle">Daftar seluruh transaksi penjualan yang telah diproses</p>

    <div class="card-fm">
        <div class="table-fm-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksi as $t)
                        <tr>
                            <td class="fw-semibold">#{{ $t->id_transaksi }}</td>
                            <td class="text-muted-fm">{{ $t->waktu_transaksi }}</td>
                            <td>{{ $t->user->nama ?? 'Kasir' }}</td>
                            <td><span class="chip-kategori">{{ $t->metode_bayar }}</span></td>
                            {{-- Menggunakan kolom total_belanja dari database --}}
                            <td class="fw-semibold">Rp {{ number_format($t->total_belanja ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-fm-outline"
                                        data-bs-toggle="modal" data-bs-target="#modalDetail"
                                        data-id="{{ $t->id_transaksi }}"
                                        data-waktu="{{ $t->waktu_transaksi }}"
                                        data-kasir="{{ $t->user->nama ?? 'Kasir' }}"
                                        data-metode="{{ $t->metode_bayar }}"
                                        data-total="Rp {{ number_format($t->total_belanja ?? 0, 0, ',', '.') }}">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted-fm">Belum ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer dan Pager --}}
        <div class="table-footer">
            <span>
                @if ($transaksi->total() > 0)
                    Menampilkan {{ $transaksi->firstItem() }} sampai {{ $transaksi->lastItem() }} dari {{ $transaksi->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            <span class="pager">
                @if ($transaksi->onFirstPage())
                    <span class="disabled">&lsaquo; Sebelumnya</span>
                @else
                    <a href="{{ $transaksi->previousPageUrl() }}">&lsaquo; Sebelumnya</a>
                @endif

                @if ($transaksi->hasMorePages())
                    <a href="{{ $transaksi->nextPageUrl() }}">Berikutnya &rsaquo;</a>
                @else
                    <span class="disabled">Berikutnya &rsaquo;</span>
                @endif
            </span>
        </div>
    </div>

    {{-- Modal Detail Transaksi --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="judulModalDetail" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-fm">
                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulModalDetail">Detail Transaksi</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-fm">ID Transaksi:</span>
                            <span class="fw-semibold" id="detail-id">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-fm">Waktu:</span>
                            <span id="detail-waktu">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-fm">Kasir:</span>
                            <span id="detail-kasir">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-fm">Metode Bayar:</span>
                            <span id="detail-metode" class="chip-kategori">-</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total Pembayaran:</span>
                            <span class="fw-bold text-success" id="detail-total">-</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm w-100" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalDetail = document.getElementById('modalDetail');
        modalDetail.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            if (!btn) return;

            document.getElementById('detail-id').textContent = '#' + btn.dataset.id;
            document.getElementById('detail-waktu').textContent = btn.dataset.waktu;
            document.getElementById('detail-kasir').textContent = btn.dataset.kasir;
            document.getElementById('detail-metode').textContent = btn.dataset.metode;
            document.getElementById('detail-total').textContent = btn.dataset.total;
        });
    });
</script>
@endpush