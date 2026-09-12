<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_campaign_launches', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('campaign_type_id')->nullable()->constrained('campaign_types')->nullOnDelete();
            $table->foreignId('territory_tier_id')->nullable()->constrained('territory_tiers')->nullOnDelete();
            $table->foreignId('ad_industry_id')->nullable()->constrained('ad_industries')->nullOnDelete();
            $table->foreignId('exclusivity_upgrade_id')->nullable()->constrained('exclusivity_upgrades')->nullOnDelete();
            $table->json('campaign_type_snapshot');
            $table->json('territory_tier_snapshot');
            $table->json('selected_countries');
            $table->json('selected_diaspora_markets');
            $table->json('delivery_channels_snapshot');
            $table->json('industry_snapshot');
            $table->json('frequency_snapshot');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('surge_active')->default(false);
            $table->json('surge_snapshot')->nullable();
            $table->json('exclusivity_snapshot')->nullable();
            $table->json('pricing_snapshot');
            $table->decimal('calculated_total', 14, 2)->default(0);
            $table->decimal('final_total', 14, 2)->default(0);
            $table->text('internal_notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('launched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_campaign_launches');
    }
};
