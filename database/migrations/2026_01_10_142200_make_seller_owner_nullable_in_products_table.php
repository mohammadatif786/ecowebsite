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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['seller_owner']);
            $table->foreignId('seller_owner')->nullable()->change();
            $table->foreign('seller_owner')->references('id')->on('merchants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['seller_owner']);
            $table->foreignId('seller_owner')->nullable(false)->change();
            $table->foreign('seller_owner')->references('id')->on('merchants');
        });
    }
};
