<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('other_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'other_user_id']);
            $table->index(['user_id', 'is_pinned']);
        });
    }
    public function down(): void { Schema::dropIfExists('conversation_preferences'); }
};
