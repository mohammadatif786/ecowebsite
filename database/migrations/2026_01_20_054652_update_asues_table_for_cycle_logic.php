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
        Schema::table('asues', function (Blueprint $table) {
            $table->integer('current_turn')->default(1);
            $table->boolean('payout_accepted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asues', function (Blueprint $table) {
            $table->dropColumn(['current_turn', 'payout_accepted']);
        });
    }
};
