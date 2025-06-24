<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            // Cek apakah kolom 'jumlahKeluar' ada sebelum rename
            if (Schema::hasColumn('barang_keluar', 'jumlahKeluar')) {
                $table->renameColumn('jumlahKeluar', 'jumlah_keluar');
            }
            // Perbaikan untuk 'penerima_barang' (jika sebelumnya 'penerima')
            if (Schema::hasColumn('barang_keluar', 'penerima')) {
                $table->renameColumn('penerima', 'penerima_barang');
            }
            // Perbaikan untuk 'keterangan_barang' (jika sebelumnya 'keterangan')
            if (Schema::hasColumn('barang_keluar', 'keterangan')) {
                $table->renameColumn('keterangan', 'keterangan_barang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            // Kembalikan nama kolom jika migrasi di-rollback
            if (Schema::hasColumn('barang_keluar', 'jumlah_keluar')) {
                $table->renameColumn('jumlah_keluar', 'jumlahKeluar');
            }
            if (Schema::hasColumn('barang_keluar', 'penerima_barang')) {
                $table->renameColumn('penerima_barang', 'penerima');
            }
            if (Schema::hasColumn('barang_keluar', 'keterangan_barang')) {
                $table->renameColumn('keterangan_barang', 'keterangan');
            }
        });
    }
};