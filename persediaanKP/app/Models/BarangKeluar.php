<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table = 'barang_keluar';

    protected $primaryKey = 'id_barang_keluar';


    protected $fillable = [
        'id_produk',
        'tanggal_keluar',
        'jumlah_keluar',
        'penerima_barang', 
        'keterangan_barang',
    ];

    /**
     * Relasi dengan model Produk.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}