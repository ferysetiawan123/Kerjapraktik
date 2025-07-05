<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('barang_keluar', function (Blueprint $table) {

            $table->id('id_barang_keluar'); 

   
            $table->unsignedInteger('id_produk'); 
            
            $table->date('tanggal_keluar'); 
            $table->integer('jumlah_keluar');
            $table->string('penerima_barang');
            $table->string('keterangan_barang')->nullable(); 
            
            $table->timestamps();

            $table->foreign('id_produk')
                  ->references('id_produk')
                  ->on('produk')
                  ->onDelete('cascade');
        });
    }

 
    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};