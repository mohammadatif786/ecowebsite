<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vibes') && !Schema::hasColumn('vibes', 'reposts_count')) {
            Schema::table('vibes', function (Blueprint $table) {
                $table->unsignedInteger('reposts_count')->default(0)->after('shares_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vibes') && Schema::hasColumn('vibes', 'reposts_count')) {
            Schema::table('vibes', function (Blueprint $table) {
                $table->dropColumn('reposts_count');
            });
        }
    }
};
