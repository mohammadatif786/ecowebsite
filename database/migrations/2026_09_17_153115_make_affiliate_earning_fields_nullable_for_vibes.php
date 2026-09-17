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
        Schema::table('marketplace_affiliate_earnings', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->change();
            $table->foreignId('order_id')->nullable()->change();
            $table->foreignId('order_item_id')->nullable()->change();
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_affiliate_earnings', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable(false)->change();
            $table->foreignId('order_id')->nullable(false)->change();
            $table->foreignId('order_item_id')->nullable(false)->change();
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
