<?php

namespace App\Jobs;

use App\Domain\Vibes\Enums\MediaProcessingStatus;
use App\Domain\Vibes\Enums\VibeMediaType;
use App\Models\VibeMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessVibeMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $mediaId) {}

    public function handle(): void
    {
        $media = VibeMedia::find($this->mediaId);
        if (! $media) {
            return;
        }

        $media->update(['processing_status' => MediaProcessingStatus::Processing]);

        try {
            $attributes = ['processing_status' => MediaProcessingStatus::Completed];
            if ($media->media_type === VibeMediaType::Image) {
                $dimensions = @getimagesizefromstring(Storage::disk($media->disk)->get($media->path));
                if ($dimensions) {
                    $attributes['width'] = $dimensions[0];
                    $attributes['height'] = $dimensions[1];
                }
            }
            $media->update($attributes);
            $media->vibe()->whereDoesntHave('media', fn ($query) => $query->where('processing_status', '!=', MediaProcessingStatus::Completed->value))
                ->update(['status' => 'published']);
        } catch (\Throwable $exception) {
            $media->update(['processing_status' => MediaProcessingStatus::Failed, 'metadata' => ['error' => $exception->getMessage()]]);
            $media->vibe()->update(['status' => 'failed']);
            throw $exception;
        }
    }
}
