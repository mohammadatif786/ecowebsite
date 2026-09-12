<?php

namespace App\Services;
use Illuminate\Http\UploadedFile;

class ImageService
{
    /**
     * Handle single file upload (supports both images and videos).
     */
    public function single(string $location, UploadedFile $file): string
    {
        return $file->store($location, 'public');
    }

    /**
     * Handle multiple file uploads (supports both images and videos).
     */
    public function multi(string $location, array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store($location, 'public');
            }
        }

        return $paths;
    }
}
