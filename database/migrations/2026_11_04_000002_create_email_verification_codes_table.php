<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->id();
            // One live code per member. A new send replaces the old row.
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            // The address the code was sent to. A code only counts for this exact address.
            $table->string('email');
            $table->string('code_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            // dateTime, not timestamp: MySQL gives a second NOT NULL timestamp column an invalid
            // zero default and refuses to create the table.
            $table->dateTime('expires_at');
            $table->dateTime('last_sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_codes');
    }
};
