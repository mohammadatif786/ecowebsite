<?php

namespace App\Domain\Kyc;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Keeps identity documents (ID photos, selfies, proof of address) off the public web.
 *
 * New uploads go to the private "kyc" disk. Older uploads that still sit on the public
 * disk are found by locate() so admins can keep reviewing them until
 * `php artisan kyc:secure-documents` moves them.
 */
final class KycDocumentStore
{
    public const DISK = 'kyc';

    public function store(UploadedFile $file, string $folder): string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');

        return Storage::disk(self::DISK)->putFileAs($folder, $file, Str::uuid()->toString() . '.' . $extension);
    }

    /**
     * Where a stored value actually lives.
     *
     * @return array{disk: string, path: string}|null
     */
    public function locate(?string $value): ?array
    {
        $relative = $this->normalise($value);
        if ($relative === null) {
            return null;
        }

        if (Storage::disk(self::DISK)->exists($relative)) {
            return ['disk' => self::DISK, 'path' => $relative];
        }

        $publicRelative = str_starts_with($relative, 'storage/') ? substr($relative, strlen('storage/')) : $relative;
        if ($publicRelative !== '' && Storage::disk('public')->exists($publicRelative)) {
            return ['disk' => 'public', 'path' => $publicRelative];
        }

        return null;
    }

    public function isPrivate(?string $value): bool
    {
        return ($this->locate($value)['disk'] ?? null) === self::DISK;
    }

    /**
     * Moves a document from the public disk to the private one.
     * Returns the new stored value, the old one when it is already private, or null when the file is missing.
     */
    public function moveToPrivate(?string $value, string $folder): ?string
    {
        $located = $this->locate($value);
        if ($located === null) {
            return null;
        }

        if ($located['disk'] === self::DISK) {
            return $located['path'];
        }

        $folder = trim(str_replace('\\', '/', $folder), '/');
        $target = $folder . '/' . basename($located['path']);
        if (Storage::disk(self::DISK)->exists($target)) {
            $target = $folder . '/' . Str::uuid()->toString() . '-' . basename($located['path']);
        }

        $stream = Storage::disk($located['disk'])->readStream($located['path']);
        if ($stream === null || $stream === false) {
            return null;
        }

        $written = Storage::disk(self::DISK)->writeStream($target, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $written || ! Storage::disk(self::DISK)->exists($target)) {
            return null;
        }

        Storage::disk($located['disk'])->delete($located['path']);

        return $target;
    }

    public function delete(?string $value): void
    {
        $located = $this->locate($value);
        if ($located !== null) {
            Storage::disk($located['disk'])->delete($located['path']);
        }
    }

    /**
     * @param  array{disk: string, path: string}  $located
     */
    public function response(array $located): StreamedResponse
    {
        return Storage::disk($located['disk'])->response($located['path'], basename($located['path']), [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
            'X-Robots-Tag' => 'noindex, nofollow',
        ], 'inline');
    }

    /**
     * Turns any stored value (a disk path, "/storage/..." or a full URL to this app) into a safe relative path.
     */
    private function normalise(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace('\\', '/', trim($value));
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $value = rawurldecode(ltrim($value, '/'));

        foreach (explode('/', $value) as $segment) {
            if ($segment === '..' || $segment === '.') {
                return null;
            }
        }

        return $value === '' ? null : $value;
    }
}
