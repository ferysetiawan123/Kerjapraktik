<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Produk; 
use Illuminate\Http\Request;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');
        $idProduk = $request->id_produk ?? ''; 


        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir != "") {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

     
        $produks = Produk::orderBy('nama_produk')->get();


        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir', 'produks', 'idProduk'));
    }

    public function getData(Request $request, $awal, $akhir) 
    {
        $query = BarangMasuk::with(['produk', 'supplier'])

            ->whereBetween('tanggal_masuk', [$awal, $akhir]);


        if ($request->has('id_produk') && $request->id_produk != "") {
            $query->where('id_produk', $request->id_produk);
        }

        $data = $query->select('id_produk', 'tanggal_masuk', 'jumlah_masuk', 'id_supplier')
            ->get()

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


    public function data(Request $request, $awal, $akhir, $id_produk = null)
    {
        $request->merge(['id_produk' => $id_produk]);
        $data = $this->getData($request, $awal, $akhir); 

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function exportPdf(Request $request, $awal, $akhir) 
    {
        $data = $this->getData($request, $awal, $akhir); 
       
        $pdf = PDF::loadView('laporan.pdf', compact('data', 'awal', 'akhir'));
      
        return $pdf->download('laporan_masuk_' . $awal . '_to_' . $akhir . '.pdf');
    }
}
