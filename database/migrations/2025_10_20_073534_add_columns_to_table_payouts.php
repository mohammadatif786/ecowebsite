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
        Schema::table('payouts', function (Blueprint $table) {
            // Add new columns
            $table->unsignedBigInteger('organizer_id')->nullable()->after('id');
            $table->unsignedBigInteger('event_id')->nullable()->after('organizer_id');
            $table->unsignedBigInteger('bank_id')->nullable()->after('event_id');
            $table->string('reference', 50)->unique()->after('bank_id');

            // Add foreign key constraints
            $table->foreign('organizer_id')
                ->references('id')
                ->on('organizer_profiles')
                ->onDelete('set null');

            $table->foreign('event_id')
                ->references('id')
                ->on('link_up_events')
                ->onDelete('set null');

            $table->foreign('bank_id')
                ->references('id')
                ->on('organizer_bank_accounts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['organizer_id']);
            $table->dropForeign(['event_id']);
            $table->dropForeign(['bank_id']);

            // Drop columns
            $table->dropColumn(['organizer_id', 'event_id', 'bank_id', 'reference']);
        });
    }
};
