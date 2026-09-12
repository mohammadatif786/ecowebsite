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
        Schema::create('private_live_stream_subs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_streamer_id');
            $table->unsignedBigInteger('pay_user_id');
            $table->unsignedBigInteger('stream_id');
            $table->string('payment_type');
            $table->decimal('pay_amount', 10, 2);
            $table->string('status');
            $table->string('stripe_session_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_live_stream_subs');
    }
};
