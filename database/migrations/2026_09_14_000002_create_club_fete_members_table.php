<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_fete_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_fete_id')->constrained('club_fetes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['club_fete_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_fete_members');
    }
};
