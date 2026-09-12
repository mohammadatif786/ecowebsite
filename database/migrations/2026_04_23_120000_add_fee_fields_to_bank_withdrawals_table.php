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
        Schema::table('bank_withdrawals', function (Blueprint $table) {
            $table->decimal('fee_percent', 8, 2)->nullable()->after('amount');
            $table->decimal('fee_amount', 10, 2)->nullable()->after('fee_percent');
            $table->decimal('payout_amount', 10, 2)->nullable()->after('fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_withdrawals', function (Blueprint $table) {
            $table->dropColumn(['fee_percent', 'fee_amount', 'payout_amount']);
        });
    }
};
