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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id('id_transaksi'); // Primary Key, auto-increment, dengan nama 'id_transaksi'

            $table->unsignedBigInteger('id_barang'); // Kolom di tabel transaksis
            $table->foreign('id_barang')->references('id_barang')->on('barangs')->onDelete('cascade');

            $table->integer('jumlah_barang'); // Untuk jumlah barang
            $table->string('tujuan');         // Untuk tujuan pengiriman/transaksi
            $table->text('keterangan')->nullable(); // Untuk keterangan tambahan, bisa null

            $table->timestamps();
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