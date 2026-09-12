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
        Schema::table('tickets', function (Blueprint $table) {
            $table->json('main_bottles')->nullable()->after('sections');
            $table->json('chasers_or_mixers')->nullable()->after('main_bottles');
            $table->json('water_options')->nullable()->after('chasers_or_mixers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['main_bottles', 'chasers_or_mixers', 'water_options']);
        });
    }
};
