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
            $table->string('commMode')->nullable()->after('drink_addons');
            $table->decimal('commission', 8, 2)->default(0)->after('commMode');
            $table->decimal('commFlat', 8, 2)->default(0)->after('commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['commMode', 'commission', 'commFlat']);
        });
    }
};
