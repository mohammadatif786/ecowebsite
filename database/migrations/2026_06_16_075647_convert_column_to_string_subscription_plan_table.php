<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
            DB::statement('ALTER TABLE subscription_plans CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('status')->default(0)->change();
            DB::statement('ALTER TABLE subscription_plans CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci');
        });
    }
};
