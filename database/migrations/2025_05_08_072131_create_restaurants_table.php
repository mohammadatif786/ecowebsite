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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('location');
            $table->string('www')->nullable(); // website
            $table->text('image_object')->nullable();
            $table->text('video_object')->nullable();
            $table->string('paid')->nullable(); // weekly, monthly, etc.
            $table->boolean('is_paid')->default(false);
            $table->decimal('cost', 10, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
