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
        Schema::create('event_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained('link_up_events')->onDelete('cascade');

            $table->json('scanner_id')->nullable();

            $table->json('media')->nullable();

            $table->json('audiences')->nullable();
            $table->boolean('attendees')->nullable();
            $table->boolean('enable_views')->default(false);
            $table->boolean('seating_plan')->default(false);
            $table->string('event_type')->nullable();

            $table->boolean('single_event')->default(false);
            $table->date('single_event_date')->nullable();
            $table->time('single_start_time')->nullable();
            $table->time('single_end_time')->nullable();

            $table->string('recurr_pattern')->nullable();
            $table->date('recurr_start_date')->nullable();
            $table->date('recurr_end_date')->nullable();

            $table->json('image_gallery')->nullable();
            $table->json('artists')->nullable();

            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('linkedin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_details');
    }
};
