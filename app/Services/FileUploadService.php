<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
class FileUploadService
{
    /**
     * Upload a single image and return its storage URL.
     */
    public function uploadSingle(?UploadedFile $file, $path = 'uploads'): ?string
    {
        if (!$file) {
            return null;
        }

        $stored = $file->store($path, 'public');
        return Storage::url($stored);
    }

    /**
     * Upload multiple images and return an array of URLs.
     */
    public function uploadMultiple(?array $files, $path = 'uploads'): array
    {
        $urls = [];

        if (!$files || !is_array($files)) {
            return $urls;
        }

        foreach ($files as $file) {
            $stored = $file->store($path, 'public');
            $urls[] = Storage::url($stored);
        }

        return $urls;
    }
}
