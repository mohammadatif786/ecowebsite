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
            if (!Schema::hasColumn('ticket_sales', 'cookout_included_protein')) {
                $table->string('cookout_included_protein')->nullable()->after('table_addons');
            }
            if (!Schema::hasColumn('ticket_sales', 'cookout_included_sides')) {
                $table->json('cookout_included_sides')->nullable()->after('cookout_included_protein');
            }
            if (!Schema::hasColumn('ticket_sales', 'cookout_addons')) {
                $table->json('cookout_addons')->nullable()->after('cookout_included_sides');
            }
            if (!Schema::hasColumn('ticket_sales', 'cookout_total')) {
                $table->decimal('cookout_total', 10, 2)->default(0)->after('cookout_addons');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sales', 'cookout_total')) {
                $table->dropColumn('cookout_total');
            }
            if (Schema::hasColumn('ticket_sales', 'cookout_addons')) {
                $table->dropColumn('cookout_addons');
            }
            if (Schema::hasColumn('ticket_sales', 'cookout_included_sides')) {
                $table->dropColumn('cookout_included_sides');
            }
            if (Schema::hasColumn('ticket_sales', 'cookout_included_protein')) {
                $table->dropColumn('cookout_included_protein');
            }
        });
    }
};
