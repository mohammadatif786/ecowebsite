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

    public function handle(\App\Services\MediaOptimizationService $service): void
    {
        $media = VibeMedia::find($this->mediaId);
        if (! $media) {
            return;
        }

        $media->update(['processing_status' => MediaProcessingStatus::Processing]);

        try {
            $disk = Storage::disk($media->disk);
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

            $extension = pathinfo($media->path, PATHINFO_EXTENSION);
            $tempInput = $tempDir . '/' . uniqid('vibe_in_') . '.' . $extension;
            $tempOutput = $tempDir . '/' . uniqid('vibe_out_') . '.' . $extension;

            // Download original
            file_put_contents($tempInput, $disk->get($media->path));

            // Process
            $type = $media->media_type === VibeMediaType::Image ? 'image' : 'video';
            $processedPath = $service->fitToVibeRatio($tempInput, $tempOutput, $type);

            if ($processedPath !== $tempInput && file_exists($processedPath)) {
                // Upload back
                $disk->put($media->path, file_get_contents($processedPath));
            }

            $attributes = ['processing_status' => MediaProcessingStatus::Completed];
            if ($media->media_type === VibeMediaType::Image) {
                $dimensions = @getimagesize($processedPath);
                if ($dimensions) {
                    $attributes['width'] = $dimensions[0];
                    $attributes['height'] = $dimensions[1];
                }
            } elseif ($media->media_type === VibeMediaType::Video) {
                // Generate thumbnail for video
                $thumbName = uniqid('thumb_') . '.jpg';
                $thumbTempPath = $tempDir . '/' . $thumbName;

                if ($service->generateVideoThumbnail($processedPath, $thumbTempPath)) {
                    $thumbPath = "vibes/{$media->vibe_id}/thumbnails/{$thumbName}";
                    $disk->put($thumbPath, file_get_contents($thumbTempPath));
                    $attributes['thumbnail_path'] = $thumbPath;
                    @unlink($thumbTempPath);
                }
            }

            // Cleanup
            @unlink($tempInput);
            @unlink($tempOutput);

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
