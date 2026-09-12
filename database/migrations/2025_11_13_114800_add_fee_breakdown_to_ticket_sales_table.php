<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            // Store snapshot of order-level fee components and rates at purchase time
            $table->json('fee_breakdown')->nullable()->after('drink_fees');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->dropColumn('fee_breakdown');
        });
    }
};
