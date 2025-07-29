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
        Schema::create('laporan_apk', function (Blueprint $table) { // Nama tabel diubah
            $table->id('id_laporan_pemeliharaan'); // Primary Key sesuai ERD

            // Foreign Keys
            $table->string('id_qr'); // Data QR yang dipindai (nomor_identifikasi)
            $table->foreign('id_qr')->references('nomor_identifikasi')->on('qr_codes')->onDelete('cascade');

            $table->unsignedBigInteger('id_barang');
            $table->foreign('id_barang')->references('id_barang')->on('barangs')->onDelete('cascade');

            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');

            // Kolom-kolom laporan
            $table->string('username');
            $table->string('nama_barang');
            $table->string('tipe_barang');
            $table->date('tanggal_inspeksi');
            $table->string('lokasi_alat');
            $table->string('foto')->nullable();
            $table->string('kondisi_fisik');
            $table->string('selang')->nullable();
            $table->string('pressure_gauge')->nullable();
            $table->string('safety_pin')->nullable();
            $table->string('tindakan');
            $table->string('status')->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
