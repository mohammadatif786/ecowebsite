<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vibes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('publisher_type', 32);
            $table->unsignedBigInteger('publisher_id');
            $table->text('caption')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_place_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('allow_coin_gifts')->default(true);
            $table->string('visibility', 20)->default('public');
            $table->string('status', 20)->default('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['publisher_type', 'publisher_id']);
            $table->index(['created_by', 'created_at']);
            $table->index(['status', 'published_at']);
        });

        Schema::create('vibe_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vibe_id')->constrained('vibes')->cascadeOnDelete();
            $table->string('media_type', 16);
            $table->string('source', 24);
            $table->string('disk', 64);
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('processing_status', 20)->default('pending');
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();

            $table->unique(['vibe_id', 'sort_order']);
        });

        Schema::create('vibe_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vibe_id')->constrained('vibes')->cascadeOnDelete();
            $table->string('attachable_type', 32);
            $table->unsignedBigInteger('attachable_id');
            $table->timestamps();

            $table->unique(['vibe_id', 'attachable_type', 'attachable_id'], 'vibe_attachment_unique');
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vibe_attachments');
        Schema::dropIfExists('vibe_media');
        Schema::dropIfExists('vibes');
    }
};
