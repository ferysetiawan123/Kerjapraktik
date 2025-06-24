<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSatuanToProdukTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produk', function (Blueprint $table) {
            // Tambahkan kolom 'satuan' setelah kolom 'stok'
            // Sesuaikan tipe data dan constraint jika perlu
            $table->string('satuan')->after('stok')->nullable(); 
            // Atau $table->string('satuan')->after('stok')->default('pcs'); jika Anda punya default
            // Atau $table->integer('satuan')->after('stok')->nullable(); jika satuan berupa ID atau angka
            // Saya asumsikan 'satuan' adalah string (misal: "pcs", "lusin", "box").
            // Sesuaikan 'string' dan 'nullable()' sesuai kebutuhan Anda.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produk', function (Blueprint $table) {
            // Hapus kolom 'satuan' jika rollback
            $table->dropColumn('satuan');
        });
    }
}