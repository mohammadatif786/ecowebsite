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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'popularity_score')) {
                $table->unsignedBigInteger('popularity_score')->default(0)->after('platform_revenue');
            }

            if (!Schema::hasColumn('users', 'popularity_score_updated_at')) {
                $table->timestamp('popularity_score_updated_at')->nullable()->after('popularity_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'popularity_score_updated_at')) {
                $table->dropColumn('popularity_score_updated_at');
            }

            if (Schema::hasColumn('users', 'popularity_score')) {
                $table->dropColumn('popularity_score');
            }
        });
    }
};
