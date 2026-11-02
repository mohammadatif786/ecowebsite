<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vibes/Reels "Follow". Before this, following a creator created an accepted
 * friend request, which made the two users friends without the creator agreeing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_id', 'creator_id']);
            $table->index('creator_id');
        });

        // Keep today's follower counts: until now a follow was an accepted friend
        // request (sender → receiver), which is also what the profile counted.
        DB::table('creator_followers')->insertUsing(
            ['follower_id', 'creator_id', 'created_at', 'updated_at'],
            DB::table('friend_requests')
                ->where('status', 1)
                ->whereColumn('user_id', '!=', 'receiver_id')
                ->groupBy('user_id', 'receiver_id')
                ->selectRaw('user_id, receiver_id, MIN(created_at), MIN(created_at)')
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_followers');
    }
};
