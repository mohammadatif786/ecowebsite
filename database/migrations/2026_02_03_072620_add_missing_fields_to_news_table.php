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
        Schema::table('news', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('title');
            $table->string('country_code')->nullable()->after('country');
            $table->string('region')->nullable()->after('country_code');
            $table->string('source_type')->default('internal')->after('source_id');
            $table->string('source_name')->nullable()->after('source_type');
            $table->timestamp('published_at')->nullable()->after('source_name');
            $table->boolean('is_breaking')->default(false)->after('published_at');
            $table->timestamp('breaking_expires_at')->nullable()->after('is_breaking');
            $table->integer('trending')->default(0)->after('breaking_expires_at');
            $table->json('media')->nullable()->after('trending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn([
                'summary',
                'country_code',
                'region',
                'source_type',
                'source_name',
                'published_at',
                'is_breaking',
                'breaking_expires_at',
                'trending',
                'media'
            ]);
        });
    }
};
