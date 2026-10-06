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
        if (Schema::hasTable('gift_coins')) {
            Schema::table('gift_coins', function (Blueprint $table) {
                if (!Schema::hasColumn('gift_coins', 'source')) {
                    $table->string('source')->default('vibe');
                }
                if (!Schema::hasColumn('gift_coins', 'reel_id')) {
                    $table->unsignedBigInteger('reel_id')->nullable();
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
