<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->string('title')->nullable()->after('caption');
            $table->string('category', 64)->nullable()->after('title');
            $table->string('campus', 255)->nullable()->after('category');
            $table->decimal('price', 10, 2)->nullable()->after('campus');
            $table->boolean('is_uvibe')->default(false)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->dropColumn(['title', 'category', 'campus', 'price', 'is_uvibe']);
        });
    }
};
