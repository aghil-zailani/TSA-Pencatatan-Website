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
        Schema::table('barangs', function (Blueprint $table) {
            if (!Schema::hasColumn('barangs', 'tipe_barang_kategori')) {
                $table->string('tipe_barang_kategori')->nullable()->after('tipe_barang');
            }
            if (!Schema::hasColumn('barangs', 'panjang')) {
                $table->decimal('panjang', 10, 2)->nullable()->after('ukuran_barang');
            }
            if (!Schema::hasColumn('barangs', 'lebar')) {
                $table->decimal('lebar', 10, 2)->nullable()->after('panjang');
            }
            if (!Schema::hasColumn('barangs', 'tinggi')) {
                $table->decimal('tinggi', 10, 2)->nullable()->after('lebar');
            }
            if (!Schema::hasColumn('barangs', 'tanggal_kadaluarsa')) {
                $table->date('tanggal_kadaluarsa')->nullable()->after('berat_barang');
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['tipe_barang_kategori', 'panjang', 'lebar', 'tinggi', 'tanggal_kadaluarsa']);
        });
    }

};
