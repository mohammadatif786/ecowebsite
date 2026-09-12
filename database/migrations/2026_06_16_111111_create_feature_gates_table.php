<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_gates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon');
            $table->text('description');
            $table->string('lock_type')->default('paid');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('feature_gate_subscription_plan', function (Blueprint $table) {
            $table->foreignId('feature_gate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->primary(['feature_gate_id', 'subscription_plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_gate_subscription_plan');
        Schema::dropIfExists('feature_gates');
    }
};
