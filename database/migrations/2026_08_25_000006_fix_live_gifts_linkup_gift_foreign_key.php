<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('live_gifts', 'linkup_live_gift_id')) {
            return;
        }

        Schema::table('live_gifts', function (Blueprint $table) {
            // Historical rows use gift_id for the legacy gifts catalogue.
            // Keep them intact and use this separately constrained column for
            // every LinkUp Live gift going forward.
            $table->foreignId('linkup_live_gift_id')
                ->nullable()
                ->after('gift_id')
                ->constrained('linkup_live_gifts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('live_gifts', 'linkup_live_gift_id')) {
            return;
        }

        Schema::table('live_gifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('linkup_live_gift_id');
        });
    }
};
