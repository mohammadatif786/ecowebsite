<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a member can do with a U Vibe post: repost it, hide it, report it, edit it, boost it,
 * and (for Campus Gigs) apply to it and get paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            // A repost points back at the post it came from (and keeps the poster's name, in case the original is deleted).
            $table->unsignedBigInteger('reposted_from_id')->nullable()->index();
            $table->string('reposted_from_name', 120)->nullable();
            // Set when the poster changes the title/text/price of a published post ("Edited").
            $table->timestamp('edited_at')->nullable();
            // Total coins members have sent to this post as Boosts (appreciation only).
            $table->unsignedBigInteger('boost_coins')->default(0);
        });

        // The Vibes feed already counts reposts; make sure the column is there.
        if (! Schema::hasColumn('vibes', 'reposts_count')) {
            Schema::table('vibes', function (Blueprint $table) {
                $table->unsignedInteger('reposts_count')->default(0);
            });
        }

        // Campus Gigs: a member applies to a gig post; the poster pays the worker from their wallet.
        Schema::create('uvibe_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vibe_id')->constrained('vibes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 16)->default('applied'); // applied | paid
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['vibe_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        // Boosts: coins a member sent to a post. Appreciation only — the poster is not paid coins.
        Schema::create('uvibe_boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vibe_id')->constrained('vibes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('gift', 40);
            $table->unsignedInteger('coins');
            $table->timestamps();

            $table->index(['vibe_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // "Hide this post": only for the member who hid it.
        Schema::create('uvibe_hidden_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('vibe_id');
            $table->timestamps();

            $table->unique(['user_id', 'vibe_id']);
            $table->index('vibe_id');
        });

        // Post reports for the moderation team. Kept even if the post is later removed.
        Schema::create('uvibe_post_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vibe_id');
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('reason_key', 24);
            $table->string('reason', 80);
            $table->string('severity', 10); // High | Medium
            $table->text('notes')->nullable();
            $table->string('excerpt', 240)->nullable();
            $table->string('campus')->nullable();
            $table->boolean('has_media')->default(false);
            $table->string('status', 16)->default('Open'); // Open | Reviewed | Dismissed
            $table->timestamps();

            $table->unique(['vibe_id', 'reporter_id']);
            $table->index(['status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uvibe_post_reports');
        Schema::dropIfExists('uvibe_hidden_posts');
        Schema::dropIfExists('uvibe_boosts');
        Schema::dropIfExists('uvibe_applications');

        Schema::table('vibes', function (Blueprint $table) {
            $table->dropColumn(['reposted_from_id', 'reposted_from_name', 'edited_at', 'boost_coins']);
        });
    }
};
