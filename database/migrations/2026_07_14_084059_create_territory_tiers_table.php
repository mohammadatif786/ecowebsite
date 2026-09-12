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
        Schema::create('territory_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('name');
            $table->decimal('price_min', 12, 2)->default(0);
            $table->decimal('price_max', 12, 2)->default(0);
            $table->decimal('multiplier', 6, 2)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territory_tiers');
    }
};
