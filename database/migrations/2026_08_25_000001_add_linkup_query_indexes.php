<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_matches', fn (Blueprint $table) => $table->index(['target_user_id', 'status'], 'user_matches_target_status_index'));
        Schema::table('friend_requests', fn (Blueprint $table) => $table->index(['receiver_id', 'status'], 'friend_requests_receiver_status_index'));
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['from_user_id', 'to_user_id', 'created_at'], 'messages_from_to_created_index');
            $table->index(['to_user_id', 'from_user_id', 'is_read'], 'messages_to_from_read_index');
        });
    }
    public function down(): void
    {
        Schema::table('user_matches', fn (Blueprint $table) => $table->dropIndex('user_matches_target_status_index'));
        Schema::table('friend_requests', fn (Blueprint $table) => $table->dropIndex('friend_requests_receiver_status_index'));
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_from_to_created_index');
            $table->dropIndex('messages_to_from_read_index');
        });
    }
};
