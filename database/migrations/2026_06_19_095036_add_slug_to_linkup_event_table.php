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
        Schema::table('link_up_events', function (Blueprint $table) {
            // Nullable for now — existing rows get backfilled by the
            // events:backfill-slugs command, then new rows always get one
            // via the LinkUpEventObserver on creation.
            $table->string('slug')->nullable()->unique()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('link_up_events', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
