<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'live_stream_gumlets_user_status_idx');
            $table->index(['status', 'start_time'], 'live_stream_gumlets_status_start_idx');
            $table->index('visibility', 'live_stream_gumlets_visibility_idx');
        });
    }

    public function down(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->dropIndex('live_stream_gumlets_user_status_idx');
            $table->dropIndex('live_stream_gumlets_status_start_idx');
            $table->dropIndex('live_stream_gumlets_visibility_idx');
        });
    }
};
