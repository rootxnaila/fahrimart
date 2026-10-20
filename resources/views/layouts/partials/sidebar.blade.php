@php($role = auth()->user()->role)

<aside class="app-sidebar">
    <a href="{{ route('dashboard') }}" class="brand d-block">
        <img src="{{ asset('images/fahrimart-wordmark.png') }}" alt="Fahrimart">
    </a>

    <ul class="nav-fm">
        {{-- Dashboard: semua peran --}}
        <li>
            <a href="{{ route('dashboard') }}" class="nav-fm-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-fm-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                Dashboard
            </a>
        </li>

        @if (in_array($role, ['pemilik', 'admin']))
            {{-- Master Data --}}
            <li>
                <button type="button" class="nav-fm-link {{ request()->routeIs('master.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" data-bs-target="#menuMaster"
                        aria-expanded="{{ request()->routeIs('master.*') ? 'true' : 'false' }}">
                    <svg class="nav-fm-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 17v-3M12 17v-5M15 17v-2"/>
                    </svg>
                    Master Data
                    <svg class="nav-fm-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                </button>
                <ul id="menuMaster" class="nav-fm-sub collapse {{ request()->routeIs('master.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('master.barang.index') }}" class="nav-fm-link {{ request()->routeIs('master.barang.*') ? 'active' : '' }}">Data Barang</a>
                    </li>
                    <li>
                        <a href="{{ route('master.supplier.index') }}" class="nav-fm-link {{ request()->routeIs('master.supplier.*') ? 'active' : '' }}">Data Supplier</a>
                    </li>
                    <li><span class="nav-fm-link disabled" title="Segera">Data Pelanggan</span></li>
                    @if ($role === 'pemilik')
                        <li><span class="nav-fm-link disabled" title="Segera">Kelola User</span></li>
                    @endif
                </ul>
            </li>

            {{-- Manajemen Stok --}}
            <li>
                <button type="button" class="nav-fm-link {{ request()->routeIs('stok.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" data-bs-target="#menuStok"
                        aria-expanded="{{ request()->routeIs('stok.*') ? 'true' : 'false' }}">
                    <svg class="nav-fm-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="M9 10h6M9 14h6M9 18h3"/>
                    </svg>
                    Manajemen Stok
                    <svg class="nav-fm-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                </button>
                <ul id="menuStok" class="nav-fm-sub collapse {{ request()->routeIs('stok.*') ? 'show' : '' }}">
                    <li>
                        <a href="{{ route('stok.pembelian.index') }}" class="nav-fm-link {{ request()->routeIs('stok.pembelian.*') ? 'active' : '' }}">Pembelian Stok</a>
                    </li>
<li>
    <a href="{{ route('stok.retur.index') }}" class="nav-fm-link {{ request()->routeIs('stok.retur.*') ? 'active' : '' }}">Retur dan Barang Rusak</a>
</li>                </ul>
            </li>

            {{-- Laporan --}}
            <li>
                <span class="nav-fm-link disabled" title="Segera">
                    <svg class="nav-fm-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>
                    </svg>
                    Laporan
                </span>
            </li>
        @endif
    </ul>

    <div class="sidebar-user">
        <div class="name">{{ auth()->user()->nama }}</div>
        <div class="role mb-2">{{ $role }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Logout</button>
        </form>
    </div>
</aside>