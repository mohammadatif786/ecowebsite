<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_poll_id')->constrained('live_polls')->cascadeOnDelete();
            $table->string('text');
            $table->unsignedInteger('votes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_poll_options');
    }
};


