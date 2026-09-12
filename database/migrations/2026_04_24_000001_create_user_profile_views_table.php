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
        Schema::create('user_profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('viewed_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('viewed_date');
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['viewer_id', 'viewed_user_id', 'viewed_date'], 'user_profile_views_unique_per_day');
            $table->index(['viewed_user_id', 'viewed_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profile_views');
    }
};
