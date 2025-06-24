<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Produk; // Import model Produk
use Illuminate\Http\Request;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');
        $idProduk = $request->id_produk ?? ''; // Tambahkan variabel untuk filter produk

        // Memastikan tanggal awal dan akhir terisi dari request jika ada
        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir != "") {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        // --- Perbaikan: Ambil semua data produk ---
        $produks = Produk::orderBy('nama_produk')->get();

        // Teruskan variabel $produks dan $idProduk ke view
        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir', 'produks', 'idProduk'));
    }

    public function getData(Request $request, $awal, $akhir) // Ubah parameter menjadi Request $request
    {
        $query = BarangMasuk::with(['produk', 'supplier'])
            // Memfilter berdasarkan tanggal_masuk antara tanggal awal dan akhir
            ->whereBetween('tanggal_masuk', [$awal, $akhir]);

        // --- Perbaikan: Tambahkan filter produk jika ada di request ---
        if ($request->has('id_produk') && $request->id_produk != "") {
            $query->where('id_produk', $request->id_produk);
        }

        $data = $query->select('id_produk', 'tanggal_masuk', 'jumlah_masuk', 'id_supplier')
            ->get()
            // Memetakan hasil untuk menyesuaikan format yang diharapkan oleh DataTables dan PDF
            ->map(function ($item) {
                return [
                    'nama_produk' => $item->produk->nama_produk ?? 'Produk Tidak Ditemukan',
                    'tanggal' => $item->tanggal_masuk,
                    'jumlahMasuk' => $item->jumlah_masuk,
                    'nama_supplier' => $item->supplier->nama ?? 'Supplier Tidak Ditemukan', // Perbaiki key dari 'nama' menjadi 'nama_supplier'
                ];
            });

        return $data;
    }

    // Metode 'data' sekarang akan menerima Request object untuk membaca filter id_produk
    public function data(Request $request, $awal, $akhir, $id_produk = null)
    {
        $request->merge(['id_produk' => $id_produk]);
        $data = $this->getData($request, $awal, $akhir); // Teruskan objek $request

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function exportPdf(Request $request, $awal, $akhir) // Ubah parameter menjadi Request $request
    {
        $data = $this->getData($request, $awal, $akhir); // Teruskan objek $request
        // Memuat view 'laporan.pdf' dengan data dan rentang tanggal
        $pdf = PDF::loadView('laporan.pdf', compact('data', 'awal', 'akhir'));
        // Mengunduh PDF dengan nama file yang informatif
        return $pdf->download('laporan_masuk_' . $awal . '_to_' . $akhir . '.pdf');
    }
}
