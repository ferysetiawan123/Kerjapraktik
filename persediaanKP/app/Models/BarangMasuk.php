<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';


    protected $primaryKey = 'id_barang_masuk'; 
    

    protected $guarded = []; 

    public function produk()
    {
   
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function supplier()
    {
  
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}