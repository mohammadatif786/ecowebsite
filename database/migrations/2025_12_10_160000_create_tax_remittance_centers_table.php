<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tax_remittance_centers', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('jurisdiction');
            $table->enum('payment_method', ['ach', 'wire', 'eftps', 'state_api']);
            $table->string('routing_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('api_base')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['jurisdiction', 'payment_method']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_remittance_centers');
    }
};
