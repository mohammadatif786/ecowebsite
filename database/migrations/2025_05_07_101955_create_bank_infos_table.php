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
        Schema::create('bank_infos', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable();
            $table->string('uid')->index();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('routing_Number')->nullable();
            $table->string('cash_app_Id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('paypal_id')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('zell_Id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_infos');
    }
};
