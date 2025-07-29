<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            // Step 1: Drop foreign key constraint
            $table->dropForeign(['id_barang']);
        });

        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            // Step 2: Ubah tipe kolom
            $table->string('id_barang')->nullable()->change();
        });

        // Jika ingin menambahkan kembali foreign key, pastikan kolom di 'barangs' juga string
        // Schema::table('pengajuan_barangs', function (Blueprint $table) {
        //     $table->foreign('id_barang')->references('id_barang')->on('barangs');
        // });
    }

    public function down(): void
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            // Kembalikan ke tipe semula
            $table->unsignedBigInteger('id_barang')->nullable()->change();

            // Tambah kembali foreign key
            $table->foreign('id_barang')->references('id_barang')->on('barangs');
        });
    }
};

