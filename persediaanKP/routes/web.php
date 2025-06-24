<?php

use App\Http\Controllers\{
    DashboardController,
    KategoriController,
    ProdukController,
    UserController,
    SupplierController,
    BarangMasukController,
    BarangKeluarController,
    LaporanController,
    LaporanKeluarController,
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute untuk Kategori, Produk, Supplier
    Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
    Route::resource('/kategori', KategoriController::class);

    Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
    // Pastikan route ini ada dan didefinisikan sebelum route resource untuk produk
    Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
    Route::resource('/produk', ProdukController::class);

    Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
    Route::resource('/supplier', SupplierController::class);

    // Rute Transaksi (Barang Masuk/Keluar)
    Route::get('/barangmasuk/data',[BarangMasukController::class, 'data'])->name('barangmasuk.data');
    // Tambahkan only() untuk secara eksplisit mendefinisikan metode yang digunakan dari resource
    // Ini juga bisa membantu jika Anda tidak ingin semua metode resource terdaftar secara default
    Route::resource('/barangmasuk', BarangMasukController::class)->except(['create', 'edit']);

    Route::get('/barangkeluar/data',[BarangKeluarController::class, 'data'])->name('barangkeluar.data');
    // Tambahkan only() untuk secara eksplisit mendefinisikan metode yang digunakan dari resource
    Route::resource('/barangkeluar', BarangKeluarController::class)->except(['create', 'edit']);

    // Rute Laporan (biasanya diakses Manager/Administrator)
    // Contoh: Jika hanya manager dan admin yang boleh akses laporan:
    // Route::group(['middleware' => 'role:administrator,manager'], function () {
        Route::get('/laporanmasuk', [LaporanController::class, 'index'])->name('laporan.index');

        // --- PERBAIKAN: Tambahkan parameter id_produk opsional di route data dan exportPdf ---
        // Parameter opsional ditandai dengan '?'
        Route::get('/laporanmasuk/data/{awal}/{akhir}/{id_produk?}', [LaporanController::class, 'data'])->name('laporan.data');
        Route::get('laporanmasuk/export/pdf/{awal}/{akhir}/{id_produk?}', [LaporanController::class, 'exportPdf'])->name('laporan.export_pdf');
        // Catatan: Nama route 'laporan.export.pdf' diubah menjadi 'laporan.export_pdf'
        // agar konsisten dengan cara pemanggilan di index.blade.php Anda sebelumnya.

        Route::get('/laporankeluar', [LaporanKeluarController::class, 'index'])->name('laporankeluar.index');
        // --- PERBAIKAN: Tambahkan parameter {id_produk?} ke rute laporan keluar ---
        Route::get('/laporankeluar/data/{awal}/{akhir}/{id_produk?}', [LaporanKeluarController::class, 'data'])->name('laporankeluar.data');
        // --- PERBAIKAN: Pastikan nama route konsisten (misalnya pakai underscore) dan tambahkan parameter opsional ---
        Route::get('laporankeluar/export/pdf/{awal}/{akhir}/{id_produk?}', [LaporanKeluarController::class, 'exportPdf'])->name('laporankeluar.export_pdf');
    // });


    // Rute Profil Pengguna (bisa diakses semua role)
    Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
    Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');

    // ========================================================================
    // Rute Khusus ADMINISTRATOR untuk Manajemen Akun (Pengguna)
    // ========================================================================
    Route::group(['middleware' => 'role:administrator'], function () {
        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::resource('/user', UserController::class)->except(['show', 'create', 'edit']);
    });

});