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
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'catatan_penolakan')) {
                if (Schema::hasColumn('transaksis', 'status')) {
                    $table->text('catatan_penolakan')->nullable()->after('status');
                } else {
                    $table->text('catatan_penolakan')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('catatan_penolakan');
        });
    }
};
