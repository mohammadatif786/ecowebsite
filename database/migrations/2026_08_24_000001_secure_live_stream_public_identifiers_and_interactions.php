<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->char('public_id', 26)->nullable()->after('id');
        });

        DB::table('live_stream_gumlets')->orderBy('id')->chunkById(100, function ($streams) {
            foreach ($streams as $stream) {
                if ($stream->public_id) {
                    continue;
                }

                do {
                    $publicId = (string) Str::ulid();
                } while (DB::table('live_stream_gumlets')->where('public_id', $publicId)->exists());

                DB::table('live_stream_gumlets')->where('id', $stream->id)->update(['public_id' => $publicId]);
            }
        });

        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->char('public_id', 26)->unique('live_stream_gumlets_public_id_unique')->change();
        });

        Schema::table('live_gifts', function (Blueprint $table) {
            $table->uuid('idempotency_key')->nullable()->unique('live_gifts_idempotency_key_unique')->after('host_share_cents');
        });

        Schema::table('live_polls', function (Blueprint $table) {
            // NULL permits historical/closed polls; 1 is assigned only to the active poll.
            $table->unsignedTinyInteger('active_marker')->nullable()->after('active');
            $table->unique(['live_stream_gumlet_id', 'active_marker'], 'live_polls_one_active_stream_unique');
        });

        DB::table('live_polls')->where('active', true)->update(['active_marker' => 1]);

        Schema::create('live_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_poll_id')->constrained('live_polls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('live_poll_option_id')->constrained('live_poll_options')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['live_poll_id', 'user_id'], 'live_poll_votes_one_vote_per_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_poll_votes');

        Schema::table('live_polls', function (Blueprint $table) {
            $table->dropUnique('live_polls_one_active_stream_unique');
            $table->dropColumn('active_marker');
        });

        Schema::table('live_gifts', function (Blueprint $table) {
            $table->dropUnique('live_gifts_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });

        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->dropUnique('live_stream_gumlets_public_id_unique');
            $table->dropColumn('public_id');
        });
    }
};
