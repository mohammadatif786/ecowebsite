<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tracks per-event interactions with injected swipe ad cards in the
     * Find Matches profile grid. One row per event (impression / click /
     * swipe_left) so counts can be summed from the table without storing
     * running totals on the ads row itself.
     */
    public function up(): void
    {
        Schema::create('swipe_ad_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')
                  ->constrained('advertisements')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            // 'impression' | 'click' | 'swipe_left'
            $table->string('event_type', 20)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swipe_ad_events');
    }
};
