<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wellness_slot_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();

            $table->unique(['ticket_id', 'slot_date', 'start_time', 'end_time'], 'wellness_slot_blocks_unique');
            $table->index(['ticket_id', 'slot_date']);
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_slot_blocks');
    }
};

