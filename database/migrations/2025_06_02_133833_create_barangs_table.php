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
            $table->string('id_barang', 50)->primary(); // VARCHAR primary key
            $table->string('created_by_role')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('short_description')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('harga_beli')->nullable(); // int
            $table->integer('harga_jual')->nullable(); // int
            $table->decimal('pajak_persen', 5, 2)->default(0.00)->nullable();
            $table->enum('stok_status', ['instock', 'outofstock'])->nullable();
            $table->boolean('featured')->default(0)->nullable();
            $table->unsignedInteger('jumlah_barang')->default(10)->nullable();
            $table->string('media')->nullable(); 
            $table->text('medias')->nullable();   
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('tipe_barang')->nullable();
            $table->string('tipe_barang_kategori')->nullable();
            $table->string('jenis_barang')->nullable();
            $table->decimal('berat_barang', 8, 2)->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->string('satuan')->nullable();
            $table->string('kondisi')->nullable();
            $table->string('status')->nullable();
            $table->string('ukuran_barang')->nullable();
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('tinggi', 10, 2)->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
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