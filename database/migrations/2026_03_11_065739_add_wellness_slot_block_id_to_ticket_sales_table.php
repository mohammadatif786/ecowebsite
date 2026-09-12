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
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_sales', 'wellness_slot_block_id')) {
                $table->unsignedBigInteger('wellness_slot_block_id')->nullable()->after('wellness_total');
                $table->foreign('wellness_slot_block_id')->references('id')->on('wellness_slot_blocks')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sales', 'wellness_slot_block_id')) {
                $table->dropForeign(['wellness_slot_block_id']);
                $table->dropColumn('wellness_slot_block_id');
            }
        });
    }
};
