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
        Schema::table('event_fee_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('event_fee_settings', 'cookout_platform_fee_percent')) {
                $table->decimal('cookout_platform_fee_percent', 5, 2)->default(0)->after('wire_processing_fee_pct');
            }

            if (!Schema::hasColumn('event_fee_settings', 'cookout_platform_fee_fixed')) {
                $table->decimal('cookout_platform_fee_fixed', 8, 2)->default(0)->after('cookout_platform_fee_percent');
            }

            if (!Schema::hasColumn('event_fee_settings', 'cookout_default_gratuity')) {
                $table->decimal('cookout_default_gratuity', 5, 2)->default(0)->after('cookout_platform_fee_fixed');
            }

            if (!Schema::hasColumn('event_fee_settings', 'cookout_enable_gratuity')) {
                $table->boolean('cookout_enable_gratuity')->default(false)->after('cookout_default_gratuity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_fee_settings', function (Blueprint $table) {
            if (Schema::hasColumn('event_fee_settings', 'cookout_enable_gratuity')) {
                $table->dropColumn('cookout_enable_gratuity');
            }

            if (Schema::hasColumn('event_fee_settings', 'cookout_default_gratuity')) {
                $table->dropColumn('cookout_default_gratuity');
            }

            if (Schema::hasColumn('event_fee_settings', 'cookout_platform_fee_fixed')) {
                $table->dropColumn('cookout_platform_fee_fixed');
            }

            if (Schema::hasColumn('event_fee_settings', 'cookout_platform_fee_percent')) {
                $table->dropColumn('cookout_platform_fee_percent');
            }
        });
    }
};
