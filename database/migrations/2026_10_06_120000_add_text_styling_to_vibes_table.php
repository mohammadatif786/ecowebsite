<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vibes') && !Schema::hasColumn('vibes', 'text_bg')) {
            Schema::table('vibes', function (Blueprint $table) {
                $table->string('text_bg')->nullable()->after('caption');
                $table->string('text_font')->nullable()->after('text_bg');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vibes') && Schema::hasColumn('vibes', 'text_bg')) {
            Schema::table('vibes', function (Blueprint $table) {
                $table->dropColumn(['text_bg', 'text_font']);
            });
        }
    }
};
