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
        Schema::create('barangs', function (Blueprint $table) {
            // PK: id_barang
            $table->id('id_barang');

            $table->string('created_by_role')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();

            // Kolom-kolom sesuai ERD dan input form
            $table->string('nama_barang');
            $table->integer('jumlah_barang')->default(0);
            $table->string('tipe_barang'); // Untuk membedakan, misal: 'Barang Jadi', 'Sparepart'
            $table->decimal('berat_barang', 8, 2)->nullable();
            $table->string('satuan');
            $table->string('kondisi');
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->string('ukuran_barang')->nullable();
            $table->string('merek_barang')->nullable();
            
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};