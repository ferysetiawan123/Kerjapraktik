<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\Produk; // --- PERBAIKAN: Import model Produk ---
use Illuminate\Http\Request;
use PDF;

class LaporanKeluarController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');
        $idProduk = $request->id_produk ?? ''; // --- PERBAIKAN: Tambahkan variabel untuk filter produk ---

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir != "") { // Menggunakan != "" agar lebih eksplisit
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        // --- PERBAIKAN: Ambil semua data produk untuk dropdown filter ---
        $produks = Produk::orderBy('nama_produk')->get();

        // --- PERBAIKAN: Teruskan variabel $produks dan $idProduk ke view ---
        return view('laporankeluar.index', compact('tanggalAwal', 'tanggalAkhir', 'produks', 'idProduk'));
    }

    // --- PERBAIKAN: Ubah parameter menjadi Request $request, $awal, $akhir ---
    public function getData(Request $request, $awal, $akhir) 
    {
        $query = BarangKeluar::with(['produk'])
            // --- PERBAIKAN: Gunakan 'tanggal_keluar' sesuai nama kolom di database ---
            ->whereBetween('tanggal_keluar', [$awal, $akhir]); 
        
        // --- PERBAIKAN: Tambahkan filter produk jika ada di request ---
        if ($request->has('id_produk') && $request->id_produk != "") {
            $query->where('id_produk', $request->id_produk);
        }

        $data = $query->select('id_produk', 'tanggal_keluar', 'jumlah_keluar', 'keterangan_barang') // --- PERBAIKAN: Pilih kolom yang benar dari DB ---
            ->get()
            ->map(function ($item) {
                return [
                    'nama_produk' => $item->produk->nama_produk ?? 'Produk Tidak Ditemukan', // Handle jika produk tidak ditemukan
                    'tanggal_keluar' => $item->tanggal_keluar, // --- PERBAIKAN: Pastikan ini cocok dengan kolom DB ---
                    'jumlah_keluar' => $item->jumlah_keluar,   // --- PERBAIKAN: Pastikan ini cocok dengan kolom DB ---
                    'keterangan_barang' => $item->keterangan_barang, // --- PERBAIKAN: Pastikan ini cocok dengan kolom DB ---
                ];
            });
        
        return $data;
    }

    // --- PERBAIKAN: Ubah parameter menjadi Request $request, $awal, $akhir ---
    public function data(Request $request, $awal, $akhir) 
    {
        $data = $this->getData($request, $awal, $akhir); // --- PERBAIKAN: Teruskan objek $request ---

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->make(true);
    }

    // --- PERBAIKAN: Ubah parameter menjadi Request $request, $awal, $akhir ---
    public function exportPdf(Request $request, $awal, $akhir) 
    {
        $data = $this->getData($request, $awal, $akhir); // --- PERBAIKAN: Teruskan objek $request ---

        $pdf = PDF::loadView('laporankeluar.pdf', compact('data', 'awal', 'akhir'));

        return $pdf->download('laporan_keluar_' . $awal . '_to_' . $akhir . '.pdf');
    }
}