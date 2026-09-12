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
        Schema::create('merchants', function (Blueprint $table) {

            $table->id();
            $table->string('merchant_type');
            $table->string('name');
            $table->string('owner_name');
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->json('pickup_locations')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('merchant_type');
            $table->index(['country', 'state', 'city']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
