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
        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('category')->default('general')->after('firebase_id');
            $table->string('headline')->nullable()->after('phone');
            $table->text('description')->nullable()->after('headline');
            $table->string('ad_type')->default('image')->after('description');
            $table->string('thumbnail')->nullable()->after('video');
            $table->string('max_duration')->nullable()->after('thumbnail');
            $table->string('autoplay_sound')->nullable()->after('max_duration');
            $table->string('cta_overlay_timing')->nullable()->after('autoplay_sound');
            $table->string('loop_video')->nullable()->after('cta_overlay_timing');
            $table->string('cta_text')->nullable()->after('loop_video');
            $table->string('brand_color')->nullable()->after('cta_text');
            $table->integer('duration_days')->nullable()->after('end_date');
            $table->string('price_package')->nullable()->after('duration_days');
            $table->string('custom_cost_override')->nullable()->after('price_package');
            $table->string('payment_ref')->nullable()->after('custom_cost_override');
            $table->string('publication_status')->nullable()->after('payment_ref');
            $table->text('targeting_notes')->nullable()->after('publication_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'headline',
                'description',
                'ad_type',
                'thumbnail',
                'max_duration',
                'autoplay_sound',
                'cta_overlay_timing',
                'loop_video',
                'cta_text',
                'brand_color',
                'duration_days',
                'price_package',
                'custom_cost_override',
                'payment_ref',
                'publication_status',
                'targeting_notes',
            ]);
        });
    }
};
