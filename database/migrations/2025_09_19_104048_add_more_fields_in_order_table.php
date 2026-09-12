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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_method')->nullable()->after('status');
            $table->string('payment_method')->nullable()->after('shipping_method');
            $table->string('subtotal_amount')->nullable()->after('payment_method');
            $table->string('shipping_amount')->nullable()->after('subtotal_amount');
            $table->string('tax_amount')->nullable()->after('shipping_amount');
            $table->string('discount_amount')->nullable()->after('tax_amount');
            $table->string('net_total')->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_method', 'payment_method', 'subtotal_amount', 'shipping_amount', 'tax_amount', 'discount_amount', 'net_total']);
        });
    }
};
