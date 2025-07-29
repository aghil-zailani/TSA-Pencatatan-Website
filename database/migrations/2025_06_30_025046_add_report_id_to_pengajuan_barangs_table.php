<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportIdToPengajuanBarangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            // Cek apakah kolom belum ada sebelum menambahkan
            if (!Schema::hasColumn('pengajuan_barangs', 'report_id')) {
                $table->uuid('report_id')->nullable()->after('id');
                $table->index('report_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengajuan_barangs', function (Blueprint $table) {
            $table->dropIndex(['report_id']); // Hapus index saat rollback
            $table->dropColumn('report_id');
        });
    }
}
