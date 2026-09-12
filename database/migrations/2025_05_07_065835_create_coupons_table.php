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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable();
            $table->string('firebase_event_id')->nullable();
            $table->foreignId('link_up_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('image_object')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->string('title');
            $table->string('discount_type')->nullable();
            $table->string('status')->default('live');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
