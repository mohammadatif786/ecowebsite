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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // maps from "id"
            $table->string('image_object')->nullable();
            $table->string('sponsor_image_object')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('event')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('link_up_event_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
