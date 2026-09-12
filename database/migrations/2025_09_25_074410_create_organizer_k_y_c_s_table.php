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
        Schema::create('organizer_k_y_c_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizer_profiles')->onDelete('cascade');

            $table->string('passport_front')->nullable();
            $table->string('passport_back')->nullable();
            $table->string('proof_of_address')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_k_y_c_s');
    }
};
