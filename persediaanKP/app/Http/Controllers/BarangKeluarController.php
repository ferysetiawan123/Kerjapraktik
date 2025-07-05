<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BarangKeluarController extends Controller
{
    /**
     * Menampilkan halaman utama riwayat barang keluar.
     * Mengirimkan data produk untuk dropdown di form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        
        $produk = Produk::orderBy('kode_produk')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id_produk => $item->kode_produk . ' - ' . $item->nama_produk];
            });
        return view('barangkeluar.index', compact('produk'));
    }

    /**
     * Mengambil data barang keluar untuk DataTables.
     * Melakukan join dengan tabel produk.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        $barang_keluar = BarangKeluar::leftJoin('produk', 'produk.id_produk', '=', 'barang_keluar.id_produk')
            ->select(
                'barang_keluar.id_barang_keluar',
                'barang_keluar.tanggal_keluar',
                'barang_keluar.jumlah_keluar',
                'barang_keluar.penerima_barang',
                'barang_keluar.keterangan_barang',
                'produk.kode_produk'
            )
            ->orderBy('barang_keluar.created_at', 'desc')
            ->get();

        return datatables()
            ->of($barang_keluar)
            ->addIndexColumn()
            ->addColumn('tanggal_keluar', function ($barang_keluar) {
                return tanggal_indonesia($barang_keluar->tanggal_keluar, false);
            })
            ->addColumn('aksi', function ($barang_keluar) {
                if (empty($barang_keluar->id_barang_keluar)) {
                    Log::warning('ID Barang Keluar kosong untuk satu baris data saat membuat kolom aksi.');
                    return '';
                }

        
                if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager'))) {
                    return '
                        <div class="btn-group">
                            <button type="button" onclick="editForm(`'. route('barangkeluar.update', $barang_keluar->id_barang_keluar) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                            <button type="button" onclick="deleteData(`'. route('barangkeluar.destroy', $barang_keluar->id_barang_keluar) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                        </div>
                    ';
                }
                return '';
            })
            ->rawColumns(['aksi', 'tanggal_keluar']) 
            ->make(true);
    }

    /**
     * Menyimpan data barang keluar baru ke database.
     * Mengurangi stok produk terkait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id_produk',
            'tanggal_keluar' => 'required|date|before_or_equal:today',
            'jumlah_keluar' => 'required|integer|min:1',
            'penerima_barang' => 'required|string|max:255', // Validasi untuk penerima_barang
            'keterangan_barang' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('BarangKeluar store validation failed', ['errors' => $validator->errors()->toArray(), 'request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $produk = Produk::find($request->id_produk);

            if (!$produk) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
            }

            // Cek ketersediaan stok sebelum mengurangi
            if ($produk->stok < $request->jumlah_keluar) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $produk->stok], 400);
            }

            // Mengurangi stok produk
            $produk->decrement('stok', $request->jumlah_keluar);

            // Membuat entri barang keluar baru
            $barang_keluar = BarangKeluar::create([
                'id_produk' => $request->id_produk,
                'tanggal_keluar' => $request->tanggal_keluar,
                'jumlah_keluar' => $request->jumlah_keluar,
                'penerima_barang' => $request->penerima_barang, // Pastikan ini juga dimasukkan
                'keterangan_barang' => $request->keterangan_barang,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil disimpan dan stok produk diperbarui!',
                'data' => $barang_keluar
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangKeluar store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan barang keluar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail data barang keluar berdasarkan ID.
     * Digunakan untuk mengisi form edit.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $barang_keluar = BarangKeluar::find($id);
        if (!$barang_keluar) {
            return response()->json(['success' => false, 'message' => 'Data barang keluar tidak ditemukan.'], 404);
        }

        return response()->json([
            'id_produk' => $barang_keluar->id_produk,
            'tanggal_keluar' => $barang_keluar->tanggal_keluar,
            'jumlah_keluar' => $barang_keluar->jumlah_keluar,
            'penerima_barang' => $barang_keluar->penerima_barang, 
            'keterangan_barang' => $barang_keluar->keterangan_barang,
        ]);
    }

    /**
     * Memperbarui data barang keluar yang sudah ada.
     * Menyesuaikan stok produk berdasarkan perubahan jumlah keluar atau produk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id_produk',
            'tanggal_keluar' => 'required|date|before_or_equal:today',
            'jumlah_keluar' => 'required|integer|min:1',
            'penerima_barang' => 'required|string|max:255', 
            'keterangan_barang' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('BarangKeluar update validation failed', ['errors' => $validator->errors()->toArray(), 'request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $barang_keluar = BarangKeluar::find($id);
            if (!$barang_keluar) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Data barang keluar tidak ditemukan.'], 404);
            }

            $produkLama = Produk::find($barang_keluar->id_produk);
            $produkBaru = Produk::find($request->id_produk);

            if (!$produkLama || !$produkBaru) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Produk terkait tidak ditemukan.'], 404);
            }

            //  update stok
            if ($barang_keluar->id_produk != $request->id_produk) {
                // Jika produk berubah, kembalikan stok produk lama
                $produkLama->increment('stok', $barang_keluar->jumlah_keluar);

                // Periksa stok produk baru sebelum dikurangi
                if ($produkBaru->stok < $request->jumlah_keluar) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Update gagal: Stok produk baru tidak mencukupi.'], 400);
                }
                // Kurangi stok produk baru
                $produkBaru->decrement('stok', $request->jumlah_keluar);
            } else {
                // Jika produk tidak berubah, hitung selisih jumlah dan sesuaikan stok produk yang sama
                $selisihJumlah = $barang_keluar->jumlah_keluar - $request->jumlah_keluar; // Positif jika jumlah lama > baru (stok bertambah)
                                                                                       // Negatif jika jumlah lama < baru (stok berkurang)
                $produkBaru->stok += $selisihJumlah;

                if ($produkBaru->stok < 0) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Update gagal: Stok produk tidak bisa negatif.'], 400);
                }
                $produkBaru->save();
            }

            // Perbarui data barang keluar
            $barang_keluar->update([
                'id_produk' => $request->id_produk,
                'tanggal_keluar' => $request->tanggal_keluar,
                'jumlah_keluar' => $request->jumlah_keluar,
                'penerima_barang' => $request->penerima_barang, 
                'keterangan_barang' => $request->keterangan_barang,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data barang keluar berhasil diupdate dan stok produk diperbarui!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangKeluar update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengupdate barang keluar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus data barang keluar dan mengembalikan stok produk.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $barang_keluar = BarangKeluar::find($id);
            if (!$barang_keluar) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Data barang keluar tidak ditemukan.'], 404);
            }

            $produk = Produk::find($barang_keluar->id_produk);
            if ($produk) {
                // Mengembalikan stok produk saat barang keluar dihapus
                $produk->increment('stok', $barang_keluar->jumlah_keluar);
            } else {
                
                Log::warning('Produk dengan ID ' . $barang_keluar->id_produk . ' tidak ditemukan saat mencoba mengembalikan stok untuk barang keluar ID ' . $id);
            }

            $barang_keluar->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Barang keluar berhasil dihapus dan stok produk dikembalikan!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangKeluar destroy failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus barang keluar: ' . $e->getMessage()], 500);
        }
    }
}
