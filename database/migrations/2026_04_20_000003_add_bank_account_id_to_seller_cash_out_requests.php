<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('seller_cash_out_requests', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('seller_cash_out_requests', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }
};
