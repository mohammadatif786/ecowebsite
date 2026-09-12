<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_sale_addons', function (Blueprint $table) {
            // Store per-addon fee components and rates (especially for drinks)
            $table->json('addon_fee_breakdown')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_sale_addons', function (Blueprint $table) {
            $table->dropColumn('addon_fee_breakdown');
        });
    }
};
