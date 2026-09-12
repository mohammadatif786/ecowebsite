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
        Schema::create('live_stream_link_ups', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // corresponds to "id"
            $table->string('channel_name');
            $table->string('type');
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->integer('join')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_stream_link_ups');
    }
};
