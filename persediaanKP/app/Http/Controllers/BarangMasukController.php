<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Penting: Pastikan ini ada

class BarangMasukController extends Controller
{
    /**
     * Menampilkan halaman utama riwayat barang masuk.
     * Mengirimkan data produk dan supplier untuk dropdown di form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $produk = Produk::orderBy('nama_produk')->pluck('nama_produk', 'id_produk');
        $supplier = Supplier::orderBy('nama')->pluck('nama', 'id_supplier');
        
        return view('barangmasuk.index', compact('produk', 'supplier'));
    }

    /**
     * Mengambil data barang masuk untuk DataTables.
     * Melakukan join dengan tabel produk dan supplier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        $barang_masuk = BarangMasuk::leftJoin('produk', 'barang_masuk.id_produk', '=', 'produk.id_produk')
            ->leftJoin('supplier', 'barang_masuk.id_supplier', '=', 'supplier.id_supplier')
            ->select(
                'barang_masuk.id_barang_masuk',
                'barang_masuk.tanggal_masuk',
                'barang_masuk.jumlah_masuk',
                'barang_masuk.penerima_barang',
                'produk.nama_produk',
                'supplier.nama'
            )
            ->orderBy('barang_masuk.created_at', 'desc')
            ->get();

        return datatables()
            ->of($barang_masuk)
            ->addIndexColumn()
            ->addColumn('tanggal_masuk', function ($barang_masuk) {
                // Pastikan fungsi helper tanggal_indonesia tersedia di project Anda
                // Jika belum ada, Anda bisa menambahkannya di app/Helpers/helpers.php atau sejenisnya
                return tanggal_indonesia($barang_masuk->tanggal_masuk, false); 
            })
            ->addColumn('aksi', function ($barang_masuk) {
                // Pastikan id_barang_masuk tidak kosong sebelum membuat tombol aksi
                if (empty($barang_masuk->id_barang_masuk)) {
                    Log::warning('ID Barang Masuk kosong untuk satu baris data saat membuat kolom aksi.');
                    return ''; // Mengembalikan string kosong jika ID tidak valid
                }
                
                // Hanya tampilkan tombol edit/hapus jika user adalah administrator atau manager
                if (auth()->check() && (auth()->user()->hasRole('administrator') || auth()->user()->hasRole('manager'))) {
                    return '
                        <div class="btn-group">
                            <button type="button" onclick="editForm(`'. route('barangmasuk.update', $barang_masuk->id_barang_masuk) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                            <button type="button" onclick="deleteData(`'. route('barangmasuk.destroy', $barang_masuk->id_barang_masuk) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                        </div>
                    ';
                }
                return ''; // Mengembalikan string kosong jika user tidak memiliki role yang diizinkan
            })
            ->rawColumns(['aksi', 'tanggal_masuk'])
            ->make(true);
    }

    /**
     * Menyimpan data barang masuk baru ke database.
     * Memperbarui stok produk terkait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id_produk',
            'tanggal_masuk' => 'required|date_format:Y-m-d|before_or_equal:today', // Menggunakan date_format
            'jumlah_masuk' => 'required|integer|min:1',
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'penerima_barang' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('BarangMasuk store validation failed', ['errors' => $validator->errors()->toArray(), 'request' => $request->all()]);
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

            // Memperbarui stok produk dengan menambahkan jumlah_masuk
            $produk->increment('stok', $request->jumlah_masuk);

            // Membuat entri barang masuk baru
            $barang_masuk = BarangMasuk::create([
                'id_produk' => $request->id_produk,
                'id_supplier' => $request->id_supplier,
                'tanggal_masuk' => $request->tanggal_masuk, // Menggunakan $request->tanggal_masuk
                'jumlah_masuk' => $request->jumlah_masuk,
                'penerima_barang' => $request->penerima_barang,
            ]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil disimpan dan stok produk diperbarui!',
                'data' => $barang_masuk
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangMasuk store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan barang masuk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail data barang masuk berdasarkan ID.
     * Digunakan untuk mengisi form edit.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $barang_masuk = BarangMasuk::find($id);
        if (!$barang_masuk) {
            return response()->json(['success' => false, 'message' => 'Data barang masuk tidak ditemukan.'], 404);
        }
        // Pastikan mengembalikan data dengan nama kolom snake_case
        return response()->json([
            'id_produk' => $barang_masuk->id_produk,
            'tanggal_masuk' => $barang_masuk->tanggal_masuk, // Menggunakan 'tanggal_masuk'
            'jumlah_masuk' => $barang_masuk->jumlah_masuk,
            'id_supplier' => $barang_masuk->id_supplier,
            'penerima_barang' => $barang_masuk->penerima_barang,
        ]);
    }

    /**
     * Memperbarui data barang masuk yang sudah ada.
     * Menyesuaikan stok produk berdasarkan perubahan jumlah masuk atau produk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id_produk',
            'tanggal_masuk' => 'required|date_format:Y-m-d|before_or_equal:today', // Menggunakan date_format
            'jumlah_masuk' => 'required|integer|min:1',
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'penerima_barang' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('BarangMasuk update validation failed', ['errors' => $validator->errors()->toArray(), 'request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $barang_masuk = BarangMasuk::find($id);
            if (!$barang_masuk) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Data barang masuk tidak ditemukan.'], 404);
            }

            $produkLama = Produk::find($barang_masuk->id_produk);
            $produkBaru = Produk::find($request->id_produk);

            if (!$produkLama || !$produkBaru) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Produk terkait tidak ditemukan.'], 404);
            }

            // Logika update stok
            if ($barang_masuk->id_produk != $request->id_produk) {
                // Jika produk berubah, kembalikan stok produk lama dan tambahkan ke produk baru
                $produkLama->decrement('stok', $barang_masuk->jumlah_masuk);
                $produkBaru->increment('stok', $request->jumlah_masuk);
            } else {
                // Jika produk tidak berubah, hitung selisih jumlah dan sesuaikan stok produk yang sama
                $selisihJumlah = $request->jumlah_masuk - $barang_masuk->jumlah_masuk;
                $produkBaru->stok += $selisihJumlah;

                if ($produkBaru->stok < 0) { 
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Update gagal: Stok produk tidak bisa negatif.'], 400);
                }
                $produkBaru->save();
            }
            
            // Perbarui data barang masuk dengan nama kolom snake_case
            $barang_masuk->update([
                'id_produk' => $request->id_produk,
                'id_supplier' => $request->id_supplier,
                'tanggal_masuk' => $request->tanggal_masuk, // Menggunakan $request->tanggal_masuk
                'jumlah_masuk' => $request->jumlah_masuk,
                'penerima_barang' => $request->penerima_barang,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data barang masuk berhasil diupdate dan stok produk diperbarui!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangMasuk update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengupdate barang masuk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus data barang masuk dan mengembalikan stok produk.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $barang_masuk = BarangMasuk::find($id);
            if (!$barang_masuk) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Data barang masuk tidak ditemukan.'], 404);
            }

            $produk = Produk::find($barang_masuk->id_produk);
            if ($produk) {
                // Jika dihapus, stok harus dikurangi dari produk
                // Tidak perlu cek stok negatif di sini karena ini adalah rollback transaksi.
                // Logika ini mengasumsikan stok produk tidak akan negatif saat proses aslinya.
                // Jika ingin lebih ketat, bisa ditambahkan validasi.
                $produk->decrement('stok', $barang_masuk->jumlah_masuk);
            }

            $barang_masuk->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Barang masuk berhasil dihapus dan stok produk dikembalikan!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangMasuk destroy failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus barang masuk: ' . $e->getMessage()], 500);
        }
    }
}