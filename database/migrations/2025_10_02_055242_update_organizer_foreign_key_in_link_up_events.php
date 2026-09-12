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
            // Drop old foreign key if exists
            if (Schema::hasColumn('link_up_events', 'organizer_id')) {
                $table->dropConstrainedForeignId('organizer_id');
            }

            // Add organizer_id referencing organizer_profiles
            $table->foreignId('organizer_id')
                ->nullable()
                ->constrained('organizer_profiles')
                ->cascadeOnDelete(); // optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('link_up_events', function (Blueprint $table) {
            // Drop the reference to organizer_profiles
            $table->dropConstrainedForeignId('organizer_id');

            // Restore organizer_id referencing users
            $table->foreignId('organizer_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete(); // optional
        });
    }
};
