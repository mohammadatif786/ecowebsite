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
        Schema::create('link_up_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('firebase_id')->index()->nullable();
            $table->string('category_id')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('coupon_visibility')->nullable();
            $table->text('description')->nullable();
            $table->text('disclaimer')->nullable();
            $table->string('email')->nullable();
            $table->string('image_object')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longtitude')->nullable(); // If you meant "longitude", update the field name.
            $table->json('organizer_image_object')->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('state')->nullable();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->string('venue')->nullable();
            $table->string('website')->nullable();
            $table->bigInteger('likes_count')->default(0);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_up_events');
    }
};
