<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('event_fees', function (Blueprint $table) {
        $table->id();

        $table->boolean('enable_subscriptions')->default(true);
        $table->decimal('platform_share_base_pct', 5, 2)->default(60);
        $table->decimal('creator_share_base_pct', 5, 2)->default(40);
        $table->boolean('use_tiers')->default(true);

        $table->boolean('enable_private_subs')->default(true);
        $table->enum('private_split_mode', ['fixed50', 'tiers'])->default('fixed50');
        $table->decimal('min_private_sub_price', 8, 2)->default(0);
        $table->decimal('max_private_sub_price', 8, 2)->default(0);

        $table->decimal('live_shop_fee_pct', 5, 2)->default(0);
        $table->decimal('live_shop_processing_fee_pct', 5, 2)->default(0);
        $table->decimal('live_shop_processing_fee_fixed', 8, 2)->default(0);

        $table->decimal('wire_processing_fee_pct', 5, 2)->default(0);
        $table->decimal('payout_threshold', 8, 2)->default(0);
        $table->integer('payout_hold_days')->default(0);

        $table->decimal('tax_rate', 5, 2)->default(0);
        $table->boolean('tax_inclusive')->default(false);

        $table->string('currency', 3)->default('USD');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_fee');
    }
};
