<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_reels', function (Blueprint $table) {
            $table->boolean('is_uvibe')->default(false)->after('status');
            $table->string('campus', 255)->nullable()->after('is_uvibe');
        });
    }

    public function down(): void
    {
        Schema::table('user_reels', function (Blueprint $table) {
            $table->dropColumn(['is_uvibe', 'campus']);
        });
    }
};
