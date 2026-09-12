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
            $table->decimal('processing_linkup_share_pct', 5, 2)->default(60)->after('processing_fee_fixed');
            $table->decimal('processing_bank_share_pct', 5, 2)->default(40)->after('processing_linkup_share_pct');
            $table->decimal('addon_fee_pct', 5, 2)->default(0)->after('vip_fee_pct');
            $table->decimal('wire_processing_fee_fixed', 8, 2)->default(0)->after('wire_processing_fee_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_fee_settings', function (Blueprint $table) {
            $table->dropColumn([
                'processing_linkup_share_pct',
                'processing_bank_share_pct',
                'addon_fee_pct',
                'wire_processing_fee_fixed',
            ]);
        });
    }
};
