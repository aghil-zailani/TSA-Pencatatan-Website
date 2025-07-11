<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_data', function (Blueprint $table) {
            $table->id(); // Kolom ID otomatis (primary key, auto-increment)
            $table->string('category', 50); // Kolom untuk kategori master data (misal: 'satuan', 'kondisi')
            $table->string('value', 255);   // Kolom untuk nilai data master (misal: 'Kg', 'Baik')
            $table->boolean('is_active')->default(true); // Kolom untuk status aktif/tidak aktif
            $table->timestamps(); // Kolom created_at dan updated_at
            $table->unique(['category', 'value']); // Kombinasi category dan value harus unik
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_data');
    }
}