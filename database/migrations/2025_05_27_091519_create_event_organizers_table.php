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
        Schema::create('event_organizers', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // Firestore "id"
            $table->foreignId('user_id')->nullable()->constrained();
            // $table->string('first_name');
            // $table->string('last_name');
            // $table->string('email')->unique();
            // $table->string('telephone')->nullable();
            // $table->string('role')->default('organizer'); // Default role
            // $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('radio')->nullable();
            // $table->string('status')->default('0'); // Default status
            // $table->string('password')->nullable();
            $table->string('passport_photo')->nullable();
            $table->string('driver_license_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_organizers');
    }
};
