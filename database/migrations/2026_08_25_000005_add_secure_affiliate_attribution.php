<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketplace_affiliate_promotions', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('product_id');
        });
        Schema::create('marketplace_affiliate_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('marketplace_affiliate_promotions')->cascadeOnDelete();
            $table->foreignId('affiliate_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_amount', 12, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['promotion_id', 'order_item_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('marketplace_affiliate_earnings'); Schema::table('marketplace_affiliate_promotions', fn (Blueprint $table) => $table->dropUnique(['public_token'])->dropColumn('public_token')); }
};
