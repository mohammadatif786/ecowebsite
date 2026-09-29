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
        if (Schema::hasTable('user_reels')) {
            Schema::table('user_reels', function (Blueprint $table) {
                if (!Schema::hasColumn('user_reels', 'gifts_count')) {
                    $table->unsignedInteger('gifts_count')->default(0);
                }
                if (!Schema::hasColumn('user_reels', 'bigups_count')) {
                    $table->unsignedInteger('bigups_count')->default(0);
                }
                if (!Schema::hasColumn('user_reels', 'allow_coin_gifts')) {
                    $table->boolean('allow_coin_gifts')->default(true);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
