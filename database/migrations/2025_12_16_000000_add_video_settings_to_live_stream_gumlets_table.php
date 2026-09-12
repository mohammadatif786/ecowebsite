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
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('thumbnail');
            $table->string('location')->nullable()->after('broadcast_type');
            $table->string('base_resolution')->default('1920x1080')->after('resolution');
            $table->string('output_resolution')->default('1280x720')->after('base_resolution');
            $table->string('downscale_filter')->default('bicubic')->after('output_resolution');
            $table->decimal('subscription_rate', 10, 2)->nullable()->after('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->dropColumn([
                'cover_image',
                'location',
                'base_resolution',
                'output_resolution',
                'downscale_filter',
                'subscription_rate',
            ]);
        });
    }
};

