@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="pos-layout">
        {{-- ===== Kolom produk ===== --}}
        <section>
                        <div class="mb-3">
                <div class="fw-semibold fs-5">Halo, {{ auth()->user()->nama }}</div>
                <div class="text-muted-fm small text-capitalize">{{ auth()->user()->role }}</div>
            </div>
            <div class="mb-3">
                <div class="pos-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b6b6b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                    </svg>
                    <input type="search" id="cariProduk" placeholder="Cari produk" autocomplete="off">
                    <button type="button" id="btnCari" class="btn btn-fm btn-sm">Cari</button>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-4" id="chipKategori">
                <button type="button" class="chip-kat active" data-kategori="">Semua</button>
                @foreach ($kategori as $k)
                    <button type="button" class="chip-kat" data-kategori="{{ $k }}">{{ $k }}</button>
                @endforeach
            </div>

            @if ($populer->isNotEmpty())
                <div class="pos-populer">
                    <h2 class="pos-judul">Produk Populer</h2>
                    <div class="pos-grid">
                        @foreach ($populer as $p)
                            @include('pos._kartu', ['p' => $p, 'varian' => 'populer'])
                        @endforeach
                    </div>
                </div>
            @endif

            <h2 class="pos-judul">Produk</h2>
            <div class="pos-grid" id="daftarProduk">
                @foreach ($produk as $p)
                    <div class="produk-col" data-nama="{{ mb_strtolower($p->nama_barang) }}" data-kategori="{{ $p->kategori }}">
                        @include('pos._kartu', ['p' => $p, 'varian' => 'biasa'])
                    </div>
                @endforeach
            </div>
            <p id="produkKosong" class="text-muted-fm {{ $produk->isEmpty() ? '' : 'd-none' }}">
                {{ $produk->isEmpty() ? 'Belum ada barang. Tambahkan lewat Master Data.' : 'Produk tidak ditemukan.' }}
            </p>
        </section>

        {{-- ===== Panel keranjang ===== --}}
        <aside class="pos-cart">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="text-muted-fm small">Keranjang</div>
                    <div class="fw-semibold fs-5">Pesanan baru</div>
                    <div class="text-muted-fm small">Kasir: {{ auth()->user()->nama }}</div>
                </div>
                <span class="text-muted-fm small"><span id="cartCount">0</span> Item</span>
            </div>

            <div class="cart-list" id="cartItems"></div>

            <div class="cart-ringkas">
                <div class="d-flex justify-content-between">
                    <span>Items (<span id="sumJumlah">0</span>)</span><span id="sumSubtotal">0</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>PPN ({{ $ppnPersen }}%)</span><span id="sumPpn">0</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total</span><strong class="fs-5" id="sumTotal">0</strong>
                </div>
                <button type="button" id="btnBayar" class="btn btn-fm-dark w-100 mt-3" disabled
                        data-bs-toggle="modal" data-bs-target="#modalBayar">Bayar</button>
            </div>
        </aside>
    </div>

    {{-- ===== Modal pembayaran ===== --}}
    <div class="modal fade" id="modalBayar" tabindex="-1" aria-labelledby="judulBayar" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-fm">
                <div class="modal-header border-0">
                    <h2 class="modal-title" id="judulBayar">Pembayaran</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="text-muted-fm">Total Belanja</span>
                        <strong class="fs-5" id="bayarTotal">Rp 0</strong>
                    </div>

                    <div class="form-label">Metode Pembayaran</div>
                    <div class="d-flex gap-2 mb-4" id="metodeGroup">
                        @foreach (['Tunai', 'QRIS', 'Debit'] as $m)
                            <button type="button" class="metode-btn {{ $loop->first ? 'active' : '' }}" data-metode="{{ $m }}">{{ $m }}</button>
                        @endforeach
                    </div>

                    <div id="grupTunai">
                        <label for="jumlahDiterima" class="form-label">Jumlah Diterima</label>
                        <div class="position-relative mb-3">
                            <span class="rp-prefix">Rp</span>
                            <input type="number" id="jumlahDiterima" min="0" step="any"
                                   class="form-control input-fm input-fm-lg has-prefix" placeholder="0">
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted-fm">Kembalian</span>
                            <strong class="text-success" id="bayarKembalian">Rp 0</strong>
                        </div>
                    </div>

                    <div id="pesanBayar" class="alert alert-danger mt-3 mb-0 d-none" role="alert"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-fm" id="btnKonfirmasi" disabled>Bayar Sekarang</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal struk ===== --}}
    <div class="modal fade" id="modalStruk" tabindex="-1" aria-labelledby="judulStruk" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-fm">
                <div class="modal-body struk-print text-center p-4">
                    <div class="struk-check">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                    </div>
                    <h2 class="h4 mt-3 mb-1" id="judulStruk">Transaksi Berhasil!</h2>
                    <div class="fw-semibold" id="strukId"></div>
                    <div class="text-muted-fm small mb-3" id="strukWaktu"></div>

                    <div class="fw-semibold">{{ $toko['nama'] }}</div>
                    <div class="text-muted-fm small">{{ $toko['alamat'] }}</div>
                    <div class="text-muted-fm small mb-3">Kasir: {{ auth()->user()->nama }}</div>
                    <div class="struk-box text-start mb-3">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="r">Qty</th>
                                    <th class="r">Harga</th>
                                    <th class="r">Total</th>
                                </tr>
                            </thead>
                            <tbody id="strukBaris"></tbody>
                        </table>
                    </div>

                    <div class="struk-box text-start">
                        <div class="struk-baris"><span>Items (<span id="strukJumlah">0</span>)</span><span id="strukSubtotal"></span></div>
                        <div class="struk-baris"><span>PPN ({{ $ppnPersen }}%)</span><span id="strukPpn"></span></div>
                        <div class="struk-baris fw-semibold"><span>Total</span><span id="strukTotal"></span></div>
                        <hr class="my-2">
                        <div class="struk-baris"><span>Metode</span><span id="strukMetode"></span></div>
                        <div class="struk-baris"><span>Dibayar</span><span id="strukBayar"></span></div>
                        <div class="struk-baris fw-semibold"><span>Kembalian</span><span id="strukKembali"></span></div>
                    </div>

                    <div class="d-flex gap-2 mt-4 no-print">
                        <button type="button" class="btn btn-fm-dark flex-fill" id="btnBaru" data-bs-dismiss="modal">Transaksi Baru</button>
                        <button type="button" class="btn btn-fm-outline flex-fill" id="btnCetak">Cetak Struk</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const PRODUK = @json($produkJs);
        const PPN = {{ (float) $ppnPersen }};
        const URL_BAYAR = @json(route('pos.transaksi.store', [], false));
        const CSRF = @json(csrf_token());

        const cart = new Map();          // id -> jumlah
        let metode = 'Tunai';
        let kategoriAktif = '';

        const $ = (id) => document.getElementById(id);
        const rp = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n));
        const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const IKON = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l1 13H5z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>';

        const modalBayarEl = $('modalBayar');
        const modalBayar = bootstrap.Modal.getOrCreateInstance(modalBayarEl);
        const modalStrukEl = $('modalStruk');
        const modalStruk = bootstrap.Modal.getOrCreateInstance(modalStrukEl);

        /* ---------- Perhitungan (server tetap menghitung ulang) ---------- */
        function hitung() {
            let jumlah = 0, subtotal = 0;
            cart.forEach((qty, id) => { jumlah += qty; subtotal += PRODUK[id].harga * qty; });
            const ppn = Math.round(subtotal * PPN / 100);
            return { jumlah, subtotal, ppn, total: subtotal + ppn };
        }

        /* ---------- Keranjang ---------- */
        function renderCart() {
            const box = $('cartItems');

            if (cart.size === 0) {
                box.innerHTML = '<div class="cart-kosong">Keranjang masih kosong.<br>Pilih produk di sebelah kiri.</div>';
            } else {
                box.innerHTML = [...cart].map(([id, qty]) => {
                    const p = PRODUK[id];
                    return `
                        <div class="cart-row" data-id="${id}">
                            <div class="cart-thumb">${IKON}</div>
                            <div class="cart-info">
                                <div class="cart-nama">${esc(p.nama)}</div>
                                <div class="cart-desk">${esc(p.satuan)}</div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="cart-harga">${rp(p.harga)}</span>
                                    <span class="stepper">
                                        <button type="button" data-aksi="cart-minus" aria-label="Kurangi">&minus;</button>
                                        <span>${qty}</span>
                                        <button type="button" data-aksi="cart-plus" aria-label="Tambah" ${qty >= p.stok ? 'disabled' : ''}>+</button>
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="cart-hapus" data-aksi="cart-hapus" aria-label="Hapus">&times;</button>
                        </div>`;
                }).join('');
            }

            const t = hitung();
            $('cartCount').textContent = t.jumlah;
            $('sumJumlah').textContent = t.jumlah;
            $('sumSubtotal').textContent = rp(t.subtotal);
            $('sumPpn').textContent = rp(t.ppn);
            $('sumTotal').textContent = rp(t.total);
            $('btnBayar').disabled = cart.size === 0;
        }

        function tambah(id, jumlah) {
            const p = PRODUK[id];
            const sekarang = cart.get(id) || 0;
            const baru = Math.min(p.stok, sekarang + jumlah);
            if (baru <= sekarang) return;
            cart.set(id, baru);
            renderCart();
        }

        function ubah(id, delta) {
            const baru = (cart.get(id) || 0) + delta;
            if (baru <= 0) cart.delete(id);
            else cart.set(id, Math.min(baru, PRODUK[id].stok));
            renderCart();
        }

        /* ---------- Kartu produk ---------- */
        function segarkanKartu(id) {
            const p = PRODUK[id];
            document.querySelectorAll(`.produk-card[data-id="${id}"]`).forEach((kartu) => {
                kartu.querySelector('.stok-nilai').textContent = p.stok;
                kartu.classList.toggle('habis', p.stok <= 0);
                kartu.querySelectorAll('button').forEach((b) => { b.disabled = p.stok <= 0; });
            });
        }

        function ubahStep(kartu, delta) {
            const id = kartu.dataset.id;
            const el = kartu.querySelector('.step-nilai');
            el.textContent = Math.max(1, Math.min(PRODUK[id].stok || 1, Number(el.textContent) + delta));
        }

        /* ---------- Cari dan filter kategori ---------- */
        function terapkanFilter() {
            const kata = $('cariProduk').value.trim().toLowerCase();
            let tampil = 0;

            document.querySelectorAll('#daftarProduk .produk-col').forEach((col) => {
                const cocok = (!kata || col.dataset.nama.includes(kata))
                    && (!kategoriAktif || col.dataset.kategori === kategoriAktif);
                col.classList.toggle('d-none', !cocok);
                if (cocok) tampil++;
            });

            const kosong = $('produkKosong');
            if (document.querySelectorAll('#daftarProduk .produk-col').length > 0) {
                kosong.textContent = 'Produk tidak ditemukan.';
                kosong.classList.toggle('d-none', tampil > 0);
            }
        }

        $('cariProduk').addEventListener('input', terapkanFilter);
        $('btnCari').addEventListener('click', terapkanFilter);
        $('chipKategori').addEventListener('click', (e) => {
            const chip = e.target.closest('.chip-kat');
            if (!chip) return;
            document.querySelectorAll('.chip-kat').forEach((c) => c.classList.remove('active'));
            chip.classList.add('active');
            kategoriAktif = chip.dataset.kategori;
            terapkanFilter();
        });

        /* ---------- Klik pada kartu dan keranjang ---------- */
        document.addEventListener('click', (e) => {
            const tombol = e.target.closest('[data-aksi]');
            if (!tombol || tombol.disabled) return;

            const kartu = tombol.closest('.produk-card');
            const baris = tombol.closest('.cart-row');

            switch (tombol.dataset.aksi) {
                case 'tambah':
                    tambah(kartu.dataset.id, 1);
                    break;
                case 'step-plus':
                    ubahStep(kartu, 1);
                    break;
                case 'step-minus':
                    ubahStep(kartu, -1);
                    break;
                case 'bag': {
                    const el = kartu.querySelector('.step-nilai');
                    tambah(kartu.dataset.id, Number(el.textContent));
                    el.textContent = 1;
                    break;
                }
                case 'cart-plus':
                    ubah(baris.dataset.id, 1);
                    break;
                case 'cart-minus':
                    ubah(baris.dataset.id, -1);
                    break;
                case 'cart-hapus':
                    cart.delete(baris.dataset.id);
                    renderCart();
                    break;
            }
        });

        /* ---------- Pembayaran ---------- */
        function bayarValid() {
            if (cart.size === 0) return false;
            if (metode !== 'Tunai') return true;
            const diterima = Number($('jumlahDiterima').value);
            return $('jumlahDiterima').value !== '' && diterima >= hitung().total;
        }

        function segarkanBayar() {
            const total = hitung().total;
            const diterima = Number($('jumlahDiterima').value) || 0;
            $('bayarKembalian').textContent = 'Rp ' + rp(Math.max(0, diterima - total));
            $('grupTunai').classList.toggle('d-none', metode !== 'Tunai');
            $('btnKonfirmasi').disabled = !bayarValid();
        }

        function tampilPesan(teks) {
            const el = $('pesanBayar');
            el.textContent = teks;
            el.classList.toggle('d-none', !teks);
        }

        modalBayarEl.addEventListener('show.bs.modal', () => {
            metode = 'Tunai';
            document.querySelectorAll('.metode-btn').forEach((b) => b.classList.toggle('active', b.dataset.metode === 'Tunai'));
            $('jumlahDiterima').value = '';
            $('bayarTotal').textContent = 'Rp ' + rp(hitung().total);
            tampilPesan('');
            segarkanBayar();
        });

        $('metodeGroup').addEventListener('click', (e) => {
            const b = e.target.closest('.metode-btn');
            if (!b) return;
            metode = b.dataset.metode;
            document.querySelectorAll('.metode-btn').forEach((x) => x.classList.toggle('active', x === b));
            tampilPesan('');
            segarkanBayar();
        });

        $('jumlahDiterima').addEventListener('input', segarkanBayar);

        $('btnKonfirmasi').addEventListener('click', async () => {
            const tombol = $('btnKonfirmasi');
            tombol.disabled = true;
            tampilPesan('');

            const muatan = {
                metode_bayar: metode,
                items: [...cart].map(([id, qty]) => ({ id_barang: Number(id), qty })),
            };
            if (metode === 'Tunai') muatan.jumlah_bayar = Number($('jumlahDiterima').value);

            const snapshot = [...cart];

            try {
                const res = await fetch(URL_BAYAR, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(muatan),
                });
                const json = await res.json().catch(() => ({}));

                if (res.status === 201) {
                    // Perbarui stok di layar (stok asli diubah trigger di database)
                    snapshot.forEach(([id, qty]) => { PRODUK[id].stok -= qty; segarkanKartu(id); });
                    cart.clear();
                    renderCart();

                    modalBayarEl.addEventListener('hidden.bs.modal', () => tampilStruk(json.data), { once: true });
                    modalBayar.hide();
                    return;
                }

                if (res.status === 422) {
                    const pesan = Object.values(json.errors || {}).flat().join(' ');
                    tampilPesan(pesan || json.message || 'Data tidak valid.');
                } else if (res.status === 419) {
                    tampilPesan('Sesi habis. Muat ulang halaman lalu coba lagi.');
                } else if (res.status === 403) {
                    tampilPesan('Anda tidak memiliki akses untuk transaksi ini.');
                } else {
                    tampilPesan('Terjadi kesalahan di server. Transaksi tidak tersimpan.');
                }
            } catch (err) {
                tampilPesan('Tidak dapat terhubung ke server. Transaksi tidak tersimpan.');
            }

            tombol.disabled = !bayarValid();
        });

        /* ---------- Struk ---------- */
        function tampilStruk(s) {
            const r = s.ringkasan;
            const [tgl, jam] = String(s.waktu_transaksi).split(' ');
            const [y, m, d] = tgl.split('-');

            $('strukId').textContent = '#' + s.id_transaksi;
            $('strukWaktu').textContent = `${Number(d)} ${BULAN[Number(m) - 1]} ${y}  ${String(jam).slice(0, 5)}`;
            $('strukBaris').innerHTML = s.items.map((i) => `
                <tr>
                    <td>${esc(i.nama_barang)}</td>
                    <td class="r">${i.qty}</td>
                    <td class="r">${rp(i.harga_satuan)}</td>
                    <td class="r">${rp(i.subtotal)}</td>
                </tr>`).join('');
            $('strukJumlah').textContent = s.items.reduce((a, i) => a + i.qty, 0);
            $('strukSubtotal').textContent = rp(r.subtotal);
            $('strukPpn').textContent = rp(r.ppn);
            $('strukTotal').textContent = rp(r.total);
            $('strukMetode').textContent = s.metode_bayar;
            $('strukBayar').textContent = rp(r.jumlah_bayar);
            $('strukKembali').textContent = rp(r.kembalian);

            modalStruk.show();
        }

        $('btnCetak').addEventListener('click', () => window.print());

        renderCart();
    });
</script>
@endpush