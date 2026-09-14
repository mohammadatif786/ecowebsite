<?php

namespace App\Models;

use App\Domain\Vibes\Enums\MediaProcessingStatus;
use App\Domain\Vibes\Enums\VibeMediaSource;
use App\Domain\Vibes\Enums\VibeMediaType;
use Illuminate\Database\Eloquent\Model;

class VibeMedia extends Model
{
    protected $table = 'vibe_media';

    protected $fillable = ['vibe_id', 'media_type', 'source', 'disk', 'path', 'thumbnail_path', 'original_name', 'mime_type', 'size', 'width', 'height', 'duration', 'processing_status', 'metadata', 'sort_order'];

    protected function casts(): array
    {
        return ['media_type' => VibeMediaType::class, 'source' => VibeMediaSource::class, 'processing_status' => MediaProcessingStatus::class, 'metadata' => 'array'];
    }

    public function vibe()
    {
        return $this->belongsTo(Vibe::class);
    }
}
