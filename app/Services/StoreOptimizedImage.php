<?php

namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreOptimizedImage
{
    public function storeOptimized(UploadedFile $file, string $directory, int $maxWidth = 1080, int $quality = 82): string
    {
        $contents = @file_get_contents($file->getRealPath());
        if ($contents === false) {
            return $file->store($directory, 'public');
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            return $file->store($directory, 'public');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if (!$width || !$height) {
            imagedestroy($image);
            return $file->store($directory, 'public');
        }

        $scale = min(1, $maxWidth / $width);
        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, $quality);
        $binary = ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        if (!is_string($binary) || $binary === '') {
            return $file->store($directory, 'public');
        }

        $filename = uniqid('', true) . '.jpg';
        $path = trim($directory, '/') . '/' . $filename;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
