<?php

use App\Support\UniversityCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every campus U Vibe knows about (seeded list + campuses members add themselves).
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name', 120);            // short name shown on chips, e.g. "UWI Mona"
            $table->string('full_name', 255);       // e.g. "The University of the West Indies, Mona"
            $table->string('country', 100)->nullable()->index();
            $table->string('flag', 16)->nullable();
            $table->boolean('is_custom')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // "My universities": schools a member attends or attended (max 5, one main).
        // A member can post to U Vibe only on these.
        Schema::create('user_universities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('student'); // student | alumni
            $table->boolean('is_main')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'university_id']);
        });

        // Campuses a member follows to see their posts (not a membership).
        Schema::create('university_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'university_id']);
        });

        Schema::table('vibes', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('campus')->constrained()->nullOnDelete();
        });

        // Seed the starting campus list.
        $now = now();
        DB::table('universities')->insert(array_map(fn ($r) => [
            'key' => $r[0],
            'name' => $r[1],
            'full_name' => $r[2],
            'country' => $r[3],
            'flag' => $r[4],
            'is_custom' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], UniversityCatalog::rows()));

        // Link existing U Vibe posts to their campus by name (short or full).
        foreach (DB::table('universities')->get(['id', 'name', 'full_name']) as $u) {
            DB::table('vibes')
                ->where('is_uvibe', true)
                ->whereNull('university_id')
                ->where(function ($q) use ($u) {
                    $q->whereRaw('LOWER(campus) = ?', [mb_strtolower($u->name)])
                      ->orWhereRaw('LOWER(campus) = ?', [mb_strtolower($u->full_name)]);
                })
                ->update(['university_id' => $u->id]);
        }
    }

    public function down(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
        });
        Schema::dropIfExists('university_follows');
        Schema::dropIfExists('user_universities');
        Schema::dropIfExists('universities');
    }
};
