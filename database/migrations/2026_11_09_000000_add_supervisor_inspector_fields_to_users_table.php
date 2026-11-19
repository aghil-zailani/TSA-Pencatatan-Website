<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_instansi')->nullable();
            $table->text('lokasi_instansi')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->foreignId('supervisor_umum_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supervisor_umum_id']);
            $table->dropColumn([
                'nama_instansi',
                'lokasi_instansi',
                'no_hp',
                'supervisor_umum_id',
            ]);
        });
    }
};
