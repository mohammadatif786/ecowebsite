<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_fee_settings', function (Blueprint $table) {
            $table->id();
            // General service + processing fees
            $table->decimal('service_fee_pct', 5, 2)->default(0.00);
            $table->decimal('service_fee_fixed', 8, 2)->default(0.00);
            $table->decimal('processing_fee_pct', 5, 2)->default(0.00);
            $table->decimal('processing_fee_fixed', 8, 2)->default(0.00);

            // Tax
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->boolean('tax_inclusive')->default(false);

            // Currency
            $table->string('currency', 10)->default('USD');

            // Event-specific fees
            $table->decimal('drink_fee_pct', 5, 2)->default(0.00);
            $table->decimal('bottle_fee_pct', 5, 2)->default(0.00);
            $table->decimal('vip_fee_pct', 5, 2)->default(0.00);

            // SPA-specific fees
            $table->decimal('spa_platform_fee_pct', 5, 2)->default(0.00);
            $table->decimal('spa_platform_fee_fixed', 8, 2)->default(0.00);
            $table->boolean('spa_gratuity_enabled')->default(false);
            $table->decimal('spa_gratuity_default_pct', 5, 2)->default(0.00);
            $table->boolean('spa_use_global_tax')->default(true);
            $table->decimal('spa_tax_rate', 5, 2)->default(0.00);
            $table->boolean('spa_tax_inclusive')->default(false);

            // Wire transfer fee
            $table->decimal('wire_processing_fee_pct', 5, 2)->default(0.00);

            $table->timestamps();
        });

        // Default row so always at least one config
        DB::table('event_fee_settings')->insert([
            'service_fee_pct' => 0.0,
            'service_fee_fixed' => 0.0,
            'processing_fee_pct' => 0.0,
            'processing_fee_fixed' => 0.0,
            'tax_rate' => 0.0,
            'tax_inclusive' => false,
            'currency' => 'USD',
            'drink_fee_pct' => 0.0,
            'bottle_fee_pct' => 0.0,
            'vip_fee_pct' => 0.0,
            'spa_platform_fee_pct' => 0.0,
            'spa_platform_fee_fixed' => 0.0,
            'spa_gratuity_enabled' => true,
            'spa_gratuity_default_pct' => 0.0,
            'spa_use_global_tax' => true,
            'spa_tax_rate' => 0.0,
            'spa_tax_inclusive' => false,
            'wire_processing_fee_pct' => 0.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_fee_settings');
    }
};
