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
        Schema::create('ticket_sales', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable();
            $table->foreignId('ticket_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ticket_type')->nullable();
            $table->string('ticket_name');
            $table->string('ticket_qrcode_id')->nullable();
            $table->bigInteger('ticket_qrcode')->nullable();
            $table->unsignedInteger('no_of_tickets')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('link_up_event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ticket_status')->default('pending');

            $table->string('payment_method')->nullable();
            $table->string('pay_type')->nullable();

            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('coupan_amount', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_sales');
    }
};
