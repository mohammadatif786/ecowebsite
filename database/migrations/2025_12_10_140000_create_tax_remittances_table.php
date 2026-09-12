<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_remittances', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('jurisdiction');
            $table->enum('payment_method', ['ach', 'wire', 'eftps', 'state_api']);
            $table->string('routing_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->json('api_headers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tax_remittances');
    }
};
