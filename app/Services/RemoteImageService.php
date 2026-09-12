<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RemoteImageService
{
    public function storeFromUrl(string $url, string $prefix): string
    {
        //Prevent SSRF (internal network access)
        $host = parse_url($url, PHP_URL_HOST);
        $ip = gethostbyname($host);

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new \Exception('Invalid image host');
        }

        //Download
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (LinkupBot/1.0)',
                'Accept' => 'image/*'
            ])
            ->get($url);


        if (!$response->successful()) {
            throw new \Exception('Image download failed');
        }

        $binary = $response->body();

        //Enforce max size (5MB)
        if (strlen($binary) > 5_000_000) {
            throw new \Exception('Image too large');
        }

        //Verify it is a real image
        $imageInfo = getimagesizefromstring($binary);
        if ($imageInfo === false) {
            throw new \Exception('File is not a valid image');
        }

        //Block SVG & HTML
        $mime = $imageInfo['mime'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!in_array($mime, $allowed)) {
            throw new \Exception('Unsupported image type');
        }

        //Safe extension from real file, not URL
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        };

        //Store safely
        $filename = $prefix . '_' . uniqid() . '.' . $extension;
        $path = "products/$filename";

        Storage::disk('public')->put($path, $binary);

        return Storage::url($path);
    }
}
