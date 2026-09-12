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
        Schema::create('ticket_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_sale_id')->constrained('ticket_sales')->cascadeOnDelete();
            $table->foreignId('scanner_id')->nullable()->constrained('scan_sign_users')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('link_up_events')->nullOnDelete();
            $table->string('ticket_qrcode_id')->index();
            $table->string('scanned_by_email')->nullable();
            $table->timestamp('checked_in_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure a ticket can only be checked in once
            $table->unique('ticket_sale_id', 'unique_ticket_checkin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_checkins');
    }
};

