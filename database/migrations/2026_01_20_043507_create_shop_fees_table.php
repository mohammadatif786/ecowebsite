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
        Schema::create('shop_fees', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('fee_type')->default('percent'); // percent, fixed, both
            $table->string('label')->default('Marketplace Fee');
            $table->decimal('percent', 8, 2)->default(0);
            $table->decimal('fixed', 8, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->decimal('min_fee', 8, 2)->default(0);
            $table->decimal('max_fee', 8, 2)->default(0);
            $table->text('disclaimer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_fees');
    }
};
