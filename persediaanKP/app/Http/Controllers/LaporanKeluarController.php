<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\Produk; 
use Illuminate\Http\Request;
use PDF;

class LaporanKeluarController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');
        $idProduk = $request->id_produk ?? ''; 

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir != "") { // Menggunakan != "" agar lebih eksplisit
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }


        $produks = Produk::orderBy('nama_produk')->get();


        return view('laporankeluar.index', compact('tanggalAwal', 'tanggalAkhir', 'produks', 'idProduk'));
    }


    public function getData(Request $request, $awal, $akhir) 
    {
        $query = BarangKeluar::with(['produk'])

            ->whereBetween('tanggal_keluar', [$awal, $akhir]); 
        

        if ($request->has('id_produk') && $request->id_produk != "") {
            $query->where('id_produk', $request->id_produk);
        }

        $data = $query->select('id_produk', 'tanggal_keluar', 'jumlah_keluar', 'keterangan_barang') 
            ->get()
            ->map(function ($item) {
                return [
                    'nama_produk' => $item->produk->nama_produk ?? 'Produk Tidak Ditemukan', 
                    'tanggal_keluar' => $item->tanggal_keluar, 
                    'jumlah_keluar' => $item->jumlah_keluar,   
                    'keterangan_barang' => $item->keterangan_barang, 
                ];
            });
        
        return $data;
    }

    
    public function data(Request $request, $awal, $akhir) 
    {
        $data = $this->getData($request, $awal, $akhir); 

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->make(true);
    }


    public function exportPdf(Request $request, $awal, $akhir) 
    {
        $data = $this->getData($request, $awal, $akhir); 

        $pdf = PDF::loadView('laporankeluar.pdf', compact('data', 'awal', 'akhir'));

        return $pdf->download('laporan_keluar_' . $awal . '_to_' . $akhir . '.pdf');
    }
}