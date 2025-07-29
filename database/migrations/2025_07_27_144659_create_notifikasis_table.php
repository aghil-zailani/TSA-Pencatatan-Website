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
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id'); // relasi ke barang
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('tipe')->default('warning'); // success / warning / info
            $table->date('tanggal');
            $table->boolean('baru')->default(true);
            $table->timestamps();

            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
