<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengajuanBarangsTable extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->unique(); // WAJIB ADA!
            $table->string('nama_barang')->nullable();
            $table->string('tipe_barang_kategori')->nullable();
            $table->string('tipe_barang')->nullable();
            $table->integer('jumlah_barang')->nullable();
            $table->string('satuan')->nullable();
            $table->string('kondisi_barang')->nullable();
            $table->string('berat')->nullable();
            $table->string('tanggal_kadaluarsa')->nullable();
            $table->string('ukuran_barang')->nullable();
            $table->string('panjang')->nullable();
            $table->string('lebar')->nullable();
            $table->string('tinggi')->nullable();
            $table->string('merek')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_barangs');
    }
}