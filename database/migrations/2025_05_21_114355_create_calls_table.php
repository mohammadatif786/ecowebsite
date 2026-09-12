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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_type')->nullable();
            $table->string('caller_name')->nullable();
            $table->text('caller_picture')->nullable(); // URLs can be long
            $table->string('caller_uid')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('channel_name')->nullable();
            $table->string('receiver')->nullable();
            $table->string('response')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
