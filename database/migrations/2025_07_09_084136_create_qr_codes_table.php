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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id('id_qr_code'); // Primary Key sesuai ERD
            $table->foreignId('id_barang')->constrained('barangs', 'id_barang')->onDelete('cascade'); // Foreign Key ke tabel 'barangs'

            $table->string('nomor_identifikasi')->unique(); // Data QR yang dipindai, harus unik
            $table->string('qr_code_path')->nullable(); // Path gambar QR (opsional)
            $table->timestamp('tanggal_pembuatan')->nullable(); // Tanggal pembuatan QR

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};