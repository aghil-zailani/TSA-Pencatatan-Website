<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPengajuanBarangsAddDefaultNamaLaporan extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->string('nama_laporan')->default('Laporan Barang Masuk')->change();
        });
    }

    public function down()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->string('nama_laporan')->default(null)->change();
        });
    }
}

