<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdBarangToPengajuanBarangsTable extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->unsignedBigInteger('id_barang')->nullable()->after('report_id');

            // Kalau mau enforce FK:
            $table->foreign('id_barang')->references('id_barang')->on('barangs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->dropForeign(['id_barang']);
            $table->dropColumn('id_barang');
        });
    }
}
