<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * U Vibe posts can also appear in the main Vibes feed ("Also post on Vibes").
     * Existing posts default to true, which matches what the feed showed before.
     */
    public function up(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->boolean('show_on_vibes')->default(true)->after('is_uvibe');
        });
    }

    public function down(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->dropColumn('show_on_vibes');
        });
    }
};
