<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            {{-- MASTER --}}
            {{-- Bagian MASTER hanya terlihat oleh Administrator dan Manajer --}}
            @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager')))
            <li class="header">MASTER</li>
            <li>
                <a href="{{ route('kategori.index') }}">
                    <i class="fa fa-cube" aria-hidden="true"></i> <span>Kategori</span>
                </a>
            </li>
            <li>
                <a href="{{ route('produk.index') }}">
                    <i class="fa fa-cubes" aria-hidden="true"></i> <span>Produk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('supplier.index') }}">
                    <i class="fa fa-truck" aria-hidden="true"></i> <span>Supplier</span>
                </a>
            </li>
            @endif

            {{-- Manajemen Akun hanya untuk Administrator --}}
            @if (auth()->check() && auth()->user()->hasRole('administrator'))
            <li>
                <a href="{{ route('user.index') }}">
                    <i class="fa fa-users" aria-hidden="true"></i> <span>Pengguna</span>
                </a>
            </li>
            @endif

            {{-- TRANSAKSI --}}
            {{-- Bagian TRANSAKSI terlihat oleh semua role yang relevan (Administrator, Manajer, Kasir) --}}
            @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager') || auth()->user()->hasRole('kasir')))
            <li class="header">TRANSAKSI</li>
            <li>
                <a href="{{ route('barangmasuk.index') }}">
                    <i class="fa fa-download" aria-hidden="true"></i> <span>Barang Masuk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('barangkeluar.index') }}">
                    <i class="fa fa-upload" aria-hidden="true"></i> <span>Barang Keluar</span>
                </a>
            </li>
            @endif

            {{-- LAPORAN --}}
            {{-- Bagian LAPORAN hanya terlihat oleh Administrator dan Manajer --}}
            @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager')))
            <li class="header">LAPORAN</li>
            <li>
                <a href="{{ route('laporan.index') }}">
                    <i class="fa fa-file-text" aria-hidden="true"></i> <span>Laporan Barang Masuk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('laporankeluar.index') }}">
                    <i class="fa fa-file-text-o" aria-hidden="true"></i> <span>Laporan Barang Keluar</span>
                </a>
            </li>
            @endif

        </ul>
    </section>
</aside>