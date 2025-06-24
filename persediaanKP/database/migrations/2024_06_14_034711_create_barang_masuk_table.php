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
        Schema::create('barang_masuk', function (Blueprint $table) {
            // Menggunakan id() untuk primary key auto-increment dengan nama custom 'id_barang_masuk'
            // Ini akan membuat primary key auto-increment 'id_barang_masuk' sebagai UNSIGNED BIGINT
            $table->id('id_barang_masuk'); 

            // Foreign Key untuk id_supplier:
            // Karena id_supplier di tabel 'supplier' dibuat dengan ->increments() (UNSIGNED INTEGER),
            // maka id_supplier di sini harus UNSIGNED INTEGER juga.
            $table->unsignedInteger('id_supplier'); 
            
            // Foreign Key untuk id_produk:
            // Sesuai error yang muncul, id_produk di tabel 'produk' kemungkinan juga dibuat dengan ->increments() (UNSIGNED INTEGER).
            // Maka id_produk di sini harus UNSIGNED INTEGER juga.
            $table->unsignedInteger('id_produk'); // Perbaikan kedua: Ubah dari foreignId() ke unsignedInteger()
            
            $table->date('tanggal_masuk'); // Kolom tanggal (sesuai yang ada di controller/view)
            $table->integer('jumlah_masuk');
            $table->string('penerima_barang');
            $table->string('keterangan_barang')->nullable();

            $table->timestamps();

            // Definisi Foreign Key secara manual untuk id_supplier
            $table->foreign('id_supplier')
                  ->references('id_supplier')
                  ->on('supplier')
                  ->onDelete('cascade');

            // Definisi Foreign Key secara manual untuk id_produk (perbaikan kedua)
            $table->foreign('id_produk')
                  ->references('id_produk')
                  ->on('produk')
                  ->onDelete('cascade');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};