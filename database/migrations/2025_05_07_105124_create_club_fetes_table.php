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
        Schema::create('club_fetes', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable();
            $table->boolean('is_paid')->default(false);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('status')->default(0);
            $table->decimal('cost')->nullable();
            $table->string('email')->nullable();
            $table->string('image_object')->nullable();
            $table->string('location')->nullable();
            $table->string('name')->nullable();
            $table->string('paid')->nullable();
            $table->string('phone')->nullable();
            $table->string('video_object')->nullable();
            $table->string('www')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_fetes');
    }
};
