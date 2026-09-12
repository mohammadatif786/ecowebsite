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
        Schema::create('cancellation_requests', function (Blueprint $table) {
            $table->id();
            // Foreign keys (assuming you have users, orders, events tables)
            $table->string('uuid')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained('ticket_sales')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('link_up_events')->onDelete('cascade');
            $table->text('admin_reason')->nullable();
            $table->string('status')->nullable();
            $table->string('admin_controll')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->decimal('deduct_ammount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_requests');
    }
};
