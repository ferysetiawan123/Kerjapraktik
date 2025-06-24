@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@section('content')
<div class="row">

    {{-- Kategori Box (Hanya untuk Administrator dan Manajer) --}}
    @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager')))
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background-color: #BF9264 !important;">
            <div class="inner" style="color: #FFFFFF;">
                <h3>{{ $kategori }}</h3>
                <p>Total Kategori</p>
            </div>
            <div class="icon" style="color: rgba(255,255,255,0.2);">
                <i class="fa fa-cube"></i>
            </div>
            <a href="{{ route('kategori.index') }}" class="small-box-footer" style="color: #FFFFFF;">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endif

    {{-- Produk Box (Untuk Semua Peran: Administrator, Manajer, Kasir) --}}
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background-color: #A6D6D6 !important;">
            <div class="inner" style="color: #FFFFFF;">
                <h3>{{ $produk }}</h3>
                <p>Total Produk</p>
            </div>
            <div class="icon" style="color: rgba(255,255,255,0.2);">
                <i class="fa fa-cubes"></i>
            </div>
            <a href="{{ route('produk.index') }}" class="small-box-footer" style="color: #FFFFFF;">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- Supplier Box (Hanya untuk Administrator dan Manajer) --}}
    @if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager')))
    <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background-color: #6F826A !important;">
            <div class="inner" style="color: #FFFFFF;">
                <h3>{{ $supplier }}</h3>
                <p>Total Supplier</p>
            </div>
            <div class="icon" style="color: rgba(255,255,255,0.2);">
                <i class="fa fa-truck" aria-hidden="true"></i> {{-- Ikon diperbaiki menjadi truk --}}
            </div>
            <a href="{{ route('supplier.index') }}" class="small-box-footer" style="color: #FFFFFF;">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endif

    {{-- Barang Masuk Box (Untuk Semua Peran: Administrator, Manajer, Kasir) --}}
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $barang_masuk }}</h3>
                <p>Total Transaksi Barang Masuk</p>
            </div>
            <div class="icon">
                <i class="fa fa-download" aria-hidden="true"></i> {{-- Ikon diperbaiki menjadi download --}}
            </div>
            <a href="{{ route('barangmasuk.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- Barang Keluar Box (Untuk Semua Peran: Administrator, Manajer, Kasir) --}}
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $barang_keluar }}</h3>
                <p>Total Transaksi Barang Keluar</p>
            </div>
            <div class="icon">
                <i class="fa fa-upload" aria-hidden="true"></i> {{-- Ikon diperbaiki menjadi upload --}}
            </div>
            <a href="{{ route('barangkeluar.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a> {{-- Route diperbaiki menjadi barangkeluar.index --}}
        </div>
    </div>

    {{-- Total Pengguna Box (Hanya untuk Administrator) --}}
    @if (auth()->check() && auth()->user()->hasRole('administrator'))
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-purple"> {{-- Warna ungu sebagai contoh --}}
            <div class="inner">
                <h3>{{ $user_count }}</h3>
                <p>Total Pengguna</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="{{ route('user.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endif

</div>
@endsection