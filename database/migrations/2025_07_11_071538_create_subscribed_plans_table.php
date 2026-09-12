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
        Schema::create('subscribed_plans', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id')->unique(); // Stripe subscription ID
            $table->string('customer_id'); // Stripe customer ID
            $table->integer('user_id');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('plan_id')->nullable(); // Plan ID (if applicable)
            $table->string('status'); // Subscription status (e.g., active, canceled)
            $table->decimal('amount_paid', 10, 2); // Amount paid (e.g., 99.99)
            $table->string('currency', 10); // Currency code (e.g., USD, EUR)
            $table->dateTime('start_date'); // Subscription start date
            $table->dateTime('end_date'); // Subscription end date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribed_plans');
    }
};
