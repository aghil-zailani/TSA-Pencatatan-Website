<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto_instansi')->nullable();
        });

        Schema::create('inspektors', function (Blueprint $table) {
            $table->id('id_inspektor');
            $table->foreignId('id_supervisor')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspektors');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto_instansi');
        });
    }
};
