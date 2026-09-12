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
        Schema::create('flagged_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->nullable();
            $table->foreignId('from_user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users', 'id')->cascadeOnDelete();

            $table->string('from_first_name')->nullable();
            $table->string('from_last_name')->nullable();

            $table->string('to_first_Name')->nullable();
            $table->string('to_last_name')->nullable();

            $table->text('message');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flagged_users');
    }
};
