<?php

namespace App\Domain\Vibes\Actions;

use App\Domain\Vibes\Enums\MediaProcessingStatus;
use App\Domain\Vibes\Enums\VibeMediaSource;
use App\Domain\Vibes\Enums\VibeMediaType;
use App\Models\Vibe;
use App\Models\VibeMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadVibeMediaAction
{
    public function execute(Vibe $vibe, UploadedFile $file, int $order, ?string $source = null): VibeMedia
    {
        $disk = config('vibes.disk');
        $path = $file->store("vibes/{$vibe->id}", $disk);

        if ($path === false) {
            throw new \RuntimeException('The vibe media could not be stored.');
        }

        try {
            return $vibe->media()->create([
                'media_type' => str_starts_with((string) $file->getMimeType(), 'image/') ? VibeMediaType::Image : VibeMediaType::Video,
                'source' => $source ? VibeMediaSource::from($source) : VibeMediaSource::Gallery,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) $file->getMimeType(),
                'size' => $file->getSize(),
                'processing_status' => MediaProcessingStatus::Pending,
                'sort_order' => $order,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
    }
}
