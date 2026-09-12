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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable();
            $table->string('note')->nullable();
            $table->decimal('balance', 10, 2)->default(0);                // amount transferred
            $table->decimal('debit', 10, 2)->default(0);                // amount transferred
            $table->decimal('credit', 10, 2)->default(0);                // amount transferred
            $table->string('transaction_type');               // e.g. send_money
            $table->timestamps();

            $table->foreignId('from_user_id')->nullable()->constrained('users');
            $table->foreignId('to_user_id')->nullable()->constrained('users');
            $table->foreignId('ref_transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('user_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
