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
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_sales', 'wellness_addons')) {
                $table->json('wellness_addons')->nullable()->after('cookout_total');
            }
            if (!Schema::hasColumn('ticket_sales', 'wellness_total')) {
                $table->decimal('wellness_total', 10, 2)->default(0)->after('wellness_addons');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sales', 'wellness_total')) {
                $table->dropColumn('wellness_total');
            }
            if (Schema::hasColumn('ticket_sales', 'wellness_addons')) {
                $table->dropColumn('wellness_addons');
            }
        });
    }
};
