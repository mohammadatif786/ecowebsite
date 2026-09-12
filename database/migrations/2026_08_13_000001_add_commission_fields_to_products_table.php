<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('commMode')->default('pct')->after('images');
            $table->decimal('commission', 8, 2)->default(10)->after('commMode');
            $table->decimal('commFlat', 8, 2)->default(0)->after('commission');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['commMode', 'commission', 'commFlat']);
        });
    }
};
