<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dateTime('delivered_at')->nullable()->after('status');
            $table->dateTime('canceled_at')->nullable()->after('delivered_at');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'canceled_at']);
        });
    }
};
