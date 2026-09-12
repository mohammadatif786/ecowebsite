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
        Schema::create('message_attendees_broads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('link_up_events')->onDelete('cascade');
            $table->string('audience')->nullable();
            $table->string('preset')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->boolean('chLinkUp')->default(false);
            $table->boolean('chEmail')->default(false);
            $table->boolean('chSMS')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attendees_broads');
    }
};
