<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');
        return view('produk.index', compact('kategori'));
    }

    public function data()
    {
        $produk = Produk::leftJoin('kategori', 'kategori.id_kategori', '=', 'produk.id_kategori')
            ->select('produk.*', 'nama_kategori')
            ->orderBy('id_produk', 'desc')
            ->get();

        return DataTables::of($produk)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($produk) {
                return '<span class="label label-success">' . $produk->kode_produk . '</span>';
            })
            ->addColumn('harga_beli', function ($produk) {
                return format_uang($produk->harga_beli);
            })
            ->addColumn('harga_jual', function ($produk) {
                return format_uang($produk->harga_jual);
            })
            ->addColumn('stok', function ($produk) {
                if ($produk->stok == 0) {
                    return '<span class="text-danger"><strong>0</strong> <i class="fa fa-times-circle" title="Stok Habis"></i></span>';
                } elseif ($produk->stok < 10) {
                    return '<span class="text-warning"><strong>' . $produk->stok . '</strong> <i class="fa fa-exclamation-triangle" title="Stok Hampir Habis"></i></span>';
                } else {
                    return $produk->stok;
                }
            })
            ->addColumn('aksi', function ($produk) {

                if (empty($produk->id_produk)) {
                    Log::warning('ID Produk kosong untuk satu baris data saat membuat kolom aksi.');
                    return '';
                }

                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('produk.show', $produk->id_produk) . '`)" class="btn btn-info btn-xs btn-flat"><i class="fa fa-edit"></i></button>
                    <button type="button" onclick="deleteData(`' . route('produk.destroy', $produk->id_produk) . '`)" class="btn btn-danger btn-xs btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_produk','stok'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'merk' => 'nullable|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gte:harga_beli',
            'satuan' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::error('Produk store validation failed', ['errors' => $validator->errors()->toArray(), 'request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();


            $today = now()->format('Ymd');

            $lastProduk = Produk::where('kode_produk', 'like', "PROD-{$today}-%")
                                 ->orderBy('kode_produk', 'desc')
                                 ->first();

            $nextCode = 1;
            if ($lastProduk) {
                $parts = explode('-', $lastProduk->kode_produk);
                $lastNumber = end($parts);
                $nextCode = (int)$lastNumber + 1;
            }

            $formattedNextCode = str_pad($nextCode, 4, '0', STR_PAD_LEFT);
            $data['kode_produk'] = "PROD-{$today}-{$formattedNextCode}";


            while (Produk::where('kode_produk', $data['kode_produk'])->exists()) {
                $nextCode++;
                $formattedNextCode = str_pad($nextCode, 4, '0', STR_PAD_LEFT);
                $data['kode_produk'] = "PROD-{$today}-{$formattedNextCode}";
            }

            $produk = Produk::create($data);

            return response()->json(['success' => true, 'message' => 'Data produk berhasil disimpan!', 'data' => $produk], 200);

        } catch (\Exception $e) {
            Log::error('Produk store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan produk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($produk);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($produk);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255|unique:produk,nama_produk,' . $id . ',id_produk',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'merk' => 'nullable|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gte:harga_beli',
            'satuan' => 'required|string|max:50',

        ]);

        if ($validator->fails()) {
            Log::error('Produk update validation failed', ['errors' => $validator->errors()->toArray(), 'request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();


            unset($data['kode_produk']);

            $produk->update($data);

            return response()->json(['success' => true, 'message' => 'Data produk berhasil diperbarui!'], 200);

        } catch (\Exception $e) {
            Log::error('Produk update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui produk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $produk = Produk::find($id);

            if (!$produk) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $produk->delete();

            return response()->json(['success' => true, 'message' => 'Data produk berhasil dihapus!'], 200);

        } catch (\Exception $e) {
            Log::error('Produk destroy failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus produk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove multiple specified resources from storage.
     */
    public function deleteSelected(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:produk,id_produk',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Produk::whereIn('id_produk', $request->ids)->delete();

            return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting selected products: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data terpilih. Silakan coba lagi.'], 500);
        }
    }
}
