<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            // Text-card look chosen in the U Vibe composer: {pal, font, motion, sticker} (the words are the caption).
            $table->json('text_style')->nullable();
            // "Posted by <you> · <role>" when a post goes out as an organization or as the university.
            $table->string('posted_role', 40)->nullable();
            // Posted as the university itself ("University (official)") rather than as the member.
            $table->boolean('as_university')->default(false);
        });

        Schema::table('user_custom_publishers', function (Blueprint $table) {
            // Student Union, Club / Society, Sports Team… (`type` stays organization|group).
            $table->string('org_type', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_custom_publishers', function (Blueprint $table) {
            $table->dropColumn('org_type');
        });

        Schema::table('vibes', function (Blueprint $table) {
            $table->dropColumn(['text_style', 'posted_role', 'as_university']);
        });
    }
};
