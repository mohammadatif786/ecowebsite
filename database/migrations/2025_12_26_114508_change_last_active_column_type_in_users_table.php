<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert existing Unix timestamps to datetime strings
        DB::statement("UPDATE users SET last_active = FROM_UNIXTIME(last_active / 1000) WHERE last_active IS NOT NULL AND last_active > 0");

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_active')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('last_active')->nullable()->change();
        });
    }
};
