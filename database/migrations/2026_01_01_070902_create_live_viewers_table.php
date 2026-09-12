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
        Schema::create('live_viewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_gumlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at');
            $table->timestamp('last_heartbeat')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index(['live_stream_gumlet_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
            $table->index('last_heartbeat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_viewers');
    }
};
