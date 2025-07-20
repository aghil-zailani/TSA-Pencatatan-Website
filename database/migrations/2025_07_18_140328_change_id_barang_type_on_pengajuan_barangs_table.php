<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIdBarangTypeOnPengajuanBarangsTable extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->string('id_barang')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->bigInteger('id_barang')->unsigned()->nullable()->change();
        });
    }
}
