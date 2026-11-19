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

            $table->string('id_barang', 50);
            $table->foreign('id_barang')->references('id_barang')->on('barangs')->onDelete('cascade');

            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');

            $table->string('created_by_role')->nullable();

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
            $table->string('tekanan_air')->nullable();
            $table->string('katup')->nullable();
            $table->string('selang_hydrant')->nullable();
            $table->string('tindakan');
            $table->string('catatan_tindakan')->nullable();
            $table->string('bagian_rusak')->nullable();
            $table->string('tingkat_kerusakan')->nullable();
            $table->string('jenis_media_isi_ulang')->nullable();
            $table->string('jumlah_isi_ulang')->nullable();
            $table->string('komponen_diganti')->nullable();
            $table->string('kondisi_komponen_lama')->nullable();
            $table->text('deskripsi_kerusakan')->nullable();
            $table->string('status')->default('Pending');
            $table->string('catatan_validasi')->nullable();

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