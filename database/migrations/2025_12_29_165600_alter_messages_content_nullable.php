<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Change content to TEXT and nullable to support non-text messages (gif/image/pdf/ticket)
            $table->text('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Revert to string, not nullable (may fail if long contents exist)
            $table->string('content')->nullable(false)->change();
        });
    }
};
