<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->timestamp('host_heartbeat_at')->nullable()->after('start_time');
            $table->timestamp('ended_at')->nullable()->after('host_heartbeat_at');
            $table->index(['status', 'host_heartbeat_at'], 'live_streams_status_host_heartbeat_idx');
        });
    }

    public function down(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->dropIndex('live_streams_status_host_heartbeat_idx');
            $table->dropColumn(['host_heartbeat_at', 'ended_at']);
        });
    }
};
