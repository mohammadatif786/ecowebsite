<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizer_medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('organizer_profiles')->onDelete('cascade');

            $table->string('logo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('profile_photo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizer_medias');
    }
};
