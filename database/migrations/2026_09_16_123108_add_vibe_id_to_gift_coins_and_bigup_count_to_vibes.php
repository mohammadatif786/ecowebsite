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
        Schema::table('gift_coins', function (Blueprint $table) {
            $table->foreignId('vibe_id')->nullable()->constrained('vibes')->onDelete('cascade');
        });

        Schema::table('vibes', function (Blueprint $table) {
            $table->unsignedInteger('bigups_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->dropColumn('bigups_count');
        });

        Schema::table('gift_coins', function (Blueprint $table) {
            $table->dropForeign(['vibe_id']);
            $table->dropColumn('vibe_id');
        });
    }
};
