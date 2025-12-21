<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /// ...
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {

            // 1. Rename Foreign Key (product_id menjadi barang_id)
            if (Schema::hasColumn('transaction_items', 'product_id')) {
                // Hapus foreign key constraint lama terlebih dahulu (ini sering jadi penyebab error 1826)
                // Coba identifikasi nama constraint lama, biasanya transaction_items_product_id_foreign
                try {
                    $table->dropForeign(['product_id']);
                } catch (\Exception $e) {
                    // Abaikan jika FKEY tidak ditemukan (sudah terhapus/belum ada)
                }

                // LAKUKAN RENAME: product_id -> barang_id
                $table->renameColumn('product_id', 'barang_id');
            }

            // 2. Tambahkan Foreign Key baru (jika belum ada)
            if (Schema::hasColumn('transaction_items', 'barang_id')) {
                // Pastikan Foreign Key baru dipasang (jika tidak menyebabkan error)
                // Jika baris ini menyebabkan error FKEY DUPLICATE, hapus baris ini!
                // $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
            }

            // KITA AKAN TANGANI RENAME product_name DI MIGRATION BERIKUTNYA
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            // Rollback: barang_id -> product_id
            if (Schema::hasColumn('transaction_items', 'barang_id')) {
                try {
                    $table->dropForeign(['barang_id']);
                } catch (\Exception $e) {
                }

                $table->renameColumn('barang_id', 'product_id');
            }
        });
    }
};
