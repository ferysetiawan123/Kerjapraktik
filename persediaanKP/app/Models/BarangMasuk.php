<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';

    // Sudah benar, Laravel akan otomatis mencari 'id_barang_masuk' jika primaryKey tidak diset
    // tapi lebih eksplisit lebih baik.
    protected $primaryKey = 'id_barang_masuk'; 
    
    // Mengizinkan mass assignment untuk semua kecuali yang ada di $guarded
    protected $guarded = []; 

    public function produk()
    {
        // Pastikan foreign key dan local key sudah benar
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function supplier()
    {
        // Pastikan foreign key dan local key sudah benar
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}