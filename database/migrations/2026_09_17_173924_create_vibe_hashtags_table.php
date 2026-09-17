<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vibe_hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vibe_id')->constrained('vibes')->cascadeOnDelete();
            $table->string('tag')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vibe_hashtags');
    }
};
