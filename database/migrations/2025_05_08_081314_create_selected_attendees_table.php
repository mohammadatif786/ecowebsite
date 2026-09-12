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
        Schema::create('selected_attendees', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // maps from "id"
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('address')->nullable();
            $table->string('event_id');
            $table->string('org_id');
            $table->string('scanner_image_object')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selected_attendees');
    }
};
