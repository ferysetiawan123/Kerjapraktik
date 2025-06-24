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
            // Menggunakan id() untuk primary key auto-increment dengan nama custom 'id_barang_keluar'
            // Ini akan membuat primary key auto-increment 'id_barang_keluar' sebagai UNSIGNED BIGINT
            $table->id('id_barang_keluar'); 

            // PERBAIKAN PENTING:
            // Karena id_produk di tabel 'produk' dibuat dengan ->increments() (UNSIGNED INTEGER),
            // maka id_produk di sini harus UNSIGNED INTEGER juga.
            $table->unsignedInteger('id_produk'); // Ubah dari foreignId() ke unsignedInteger()
            
            $table->date('tanggal_keluar'); // Ubah dari 'tanggal' menjadi 'tanggal_keluar'
            $table->integer('jumlah_keluar'); // Tambahkan kolom jumlah_keluar
            $table->string('penerima_barang'); // Tambahkan kolom penerima_barang
            $table->string('keterangan_barang')->nullable(); // Tambahkan kolom keterangan_barang
            
            $table->timestamps();

            // Definisi Foreign Key secara manual untuk id_produk
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
        Schema::dropIfExists('barang_keluar');
    }
};