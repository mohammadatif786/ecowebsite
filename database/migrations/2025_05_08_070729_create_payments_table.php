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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('firebase_id')->unique()->nullable();
            // $table->foreignId('transaction_id')->nullable()->constrained();
            $table->string('method');                // e.g., Google Play
            $table->decimal('price', 10, 2);         // e.g., 222
            $table->string('currency', 10);          // e.g., USD
            $table->string('payer_id')->nullable();              // internal or platform payer ID
            $table->string('payer')->nullable();                 // payer name or alias
            $table->string('type');                  // e.g., consumable
            $table->string('sku');                   // product identifier

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
