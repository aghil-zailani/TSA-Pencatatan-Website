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
            $table->uuid('report_id')->nullable()->after('id'); // Tambahkan kolom UUID
            // Anda bisa tambahkan index untuk pencarian yang lebih cepat
            $table->index('report_id');
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