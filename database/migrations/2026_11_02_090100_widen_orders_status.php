<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders use more statuses than the original enum allowed (paid, completed, received_buyer,
 * disputed, refunded…). With MySQL strict mode, saving a paid wallet order failed, so the
 * column becomes a short string. Existing values are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 30)->default('new')->change();
        });
    }

    public function down(): void
    {
        // Leave the wider column: narrowing it back would fail on rows using the newer statuses.
    }
};
