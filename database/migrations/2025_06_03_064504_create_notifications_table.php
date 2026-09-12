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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('send_by');
            $table->string('type')->default('system'); // message, match, payment, gift, system
            $table->string('context')->nullable(); // category label
            $table->boolean('unread')->default(true);
            $table->boolean('priority')->default(false);
            $table->string('icon')->nullable();
            $table->string('avatar')->nullable();
            $table->json('metadata')->nullable(); // for extra data like amount, status, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
