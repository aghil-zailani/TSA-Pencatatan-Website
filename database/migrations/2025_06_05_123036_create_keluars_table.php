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
        // Menggunakan nama tabel 'transaksis' sesuai ERD,
        // atau Anda bisa menggunakan 'barang_keluars' jika lebih sesuai.
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id('id_transaksi'); // Primary Key, auto-increment, dengan nama 'id_transaksi'

            // Foreign Key ke tabel 'barangs' (asumsi nama tabel barang adalah 'barangs')
            // Pastikan tipe data foreign key sama dengan primary key di tabel 'barangs'
            $table->unsignedBigInteger('id_barang'); // Kolom di tabel transaksis
            $table->foreign('id_barang')->references('id')->on('barangs')->onDelete('cascade');
//                                        ^^^--- Merujuk ke kolom 'id' di tabel 'barangs'
            // Jika nama primary key di tabel barang adalah 'id', gunakan:
            // $table->foreign('id_barang')->references('id')->on('barangs')->onDelete('cascade');

            $table->integer('jumlah_barang'); // Untuk jumlah barang
            $table->string('tujuan');         // Untuk tujuan pengiriman/transaksi
            $table->text('keterangan')->nullable(); // Untuk keterangan tambahan, bisa null

            // Kolom 'timestamp' di ERD Anda kemungkinan merujuk pada created_at/updated_at
            // Jika Anda ingin kolom timestamp spesifik dengan nama 'timestamp', Anda bisa menambahkannya:
            // $table->timestamp('timestamp')->useCurrent(); // Atau nullable(), default(null), dll.
            // Namun, Laravel secara otomatis menyediakan created_at dan updated_at dengan $table->timestamps();

            $table->timestamps(); // Ini akan membuat kolom `created_at` dan `updated_at`
                                  // yang bisa berfungsi sebagai 'timestamp' di ERD Anda.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis'); // Sesuaikan nama tabel jika Anda memilih nama lain
    }
};