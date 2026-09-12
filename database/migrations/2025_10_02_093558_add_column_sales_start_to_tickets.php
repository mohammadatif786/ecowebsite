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
            $table->datetime('sales_start')->nullable();
            $table->datetime('sales_end')->nullable();
            $table->integer('qty_available')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('sales_start');
            $table->dropColumn('sales_end');
            $table->dropColumn('qty_available');
        });
    }
};
