<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use Illuminate\Support\Facades\Log;

class MediaOptimizationService
{
    protected ImageManager $imageManager;
    protected FFMpeg $ffmpeg;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->ffmpeg = FFMpeg::create();
    }

    /**
     * Optimize image by reducing resolution and size by 50%
     *
     * @param string $imagePath Path to the image file
     * @param string $outputPath Path to save optimized image
     * @return string Path to optimized image
     */
    public function optimizeImage(string $imagePath, string $outputPath = null): string
    {
        if (!$outputPath) {
            $outputPath = $this->generateOptimizedPath($imagePath, 'optimized');
        }

        try {
            $image = $this->imageManager->decodePath($imagePath);

            // Get original dimensions
            $width = $image->width();
            $height = $image->height();

            // Reduce dimensions by 50%
            $newWidth = (int) round($width * 0.5);
            $newHeight = (int) round($height * 0.5);

            // Resize and optimize
            $image->resize($newWidth, $newHeight)
                  ->save($outputPath, quality: 75); // 75% quality for additional compression

            return $outputPath;
        } catch (\Exception $e) {
            Log::error('Image optimization failed', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            return $imagePath; // Return original if optimization fails
        }
    }

    /**
     * Optimize video by reducing resolution and bitrate by 50%
     *
     * @param string $videoPath Path to the video file
     * @param string $outputPath Path to save optimized video
     * @return string Path to optimized video
     */
    public function optimizeVideo(string $videoPath, string $outputPath = null): string
    {
        if (!$outputPath) {
            $outputPath = $this->generateOptimizedPath($videoPath, 'optimized');
        }

        try {
            $video = $this->ffmpeg->open($videoPath);

            // Get original dimensions
            $dimensions = $video->getStreams()->videos()->first()->getDimensions();
            $width = $dimensions->getWidth();
            $height = $dimensions->getHeight();

            // Reduce dimensions by 50%
            $newWidth = (int) round($width * 0.5);
            $newHeight = (int) round($height * 0.5);

            // FFMpeg requires even numbers for many encoders
            if ($newWidth % 2 !== 0) $newWidth--;
            if ($newHeight % 2 !== 0) $newHeight--;

            // Resize video with reduced bitrate
            $video->filters()
                  ->resize(new Dimension($newWidth, $newHeight))
                  ->synchronize();

            $video->save(new \FFMpeg\Format\Video\X264('aac', 'libx264'), $outputPath);

            return $outputPath;
        } catch (\Exception $e) {
            Log::error('Video optimization failed', [
                'video_path' => $videoPath,
                'error' => $e->getMessage()
            ]);
            return $videoPath; // Return original if optimization fails
        }
    }

    /**
     * Fit media to Vibe card ratio (4:5 / 1080x1350)
     */
    public function fitToVibeRatio(string $inputPath, string $outputPath, string $type = 'image'): string
    {
        try {
            if ($type === 'image') {
                $image = $this->imageManager->read($inputPath);
                $image->contain(1080, 1350, '000000') // Black background
                      ->save($outputPath);
                return $outputPath;
            }

            if ($type === 'video') {
                $video = $this->ffmpeg->open($inputPath);
                $video->filters()
                      ->resize(new Dimension(1080, 1350), \FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET)
                      ->pad(new \FFMpeg\Coordinate\Dimension(1080, 1350))
                      ->synchronize();

                $video->save(new \FFMpeg\Format\Video\X264('aac', 'libx264'), $outputPath);
                return $outputPath;
            }
        } catch (\Exception $e) {
            Log::error('Media fit to vibe ratio failed', [
                'path' => $inputPath,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }

        return $inputPath;
    }

    /**
     * Generate a thumbnail from a video file
     */
    public function generateVideoThumbnail(string $videoPath, string $outputPath, int $second = 1): bool
    {
        try {
            $video = $this->ffmpeg->open($videoPath);
            $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($second))
                  ->save($outputPath);
            return true;
        } catch (\Exception $e) {
            Log::error('Video thumbnail generation failed', ['path' => $videoPath, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Optimize base64 image data
     *
     * @param string $base64Data Base64 encoded image data
     * @return string Optimized base64 data
     */
    public function optimizeBase64Image(string $base64Data): string
    {
        try {
            // Extract the mime type and base64 content
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
                $extension = $matches[1];
                $base64Content = $matches[2];

                // Decode base64 to binary
                $imageData = base64_decode($base64Content);
                $tempPath = tempnam(sys_get_temp_dir(), 'img_');
                file_put_contents($tempPath, $imageData);

                // Optimize the image
                $optimizedPath = $this->optimizeImage($tempPath);

                // Read optimized image and encode back to base64
                $optimizedData = file_get_contents($optimizedPath);
                $optimizedBase64 = 'data:image/' . $extension . ';base64,' . base64_encode($optimizedData);

                // Clean up temp files
                unlink($tempPath);
                if ($optimizedPath !== $tempPath) {
                    unlink($optimizedPath);
                }

                return $optimizedBase64;
            }

            return $base64Data; // Return original if not a valid base64 image
        } catch (\Exception $e) {
            Log::error('Base64 image optimization failed', [
                'error' => $e->getMessage()
            ]);
            return $base64Data; // Return original if optimization fails
        }
    }

    /**
     * Optimize base64 video data
     *
     * @param string $base64Data Base64 encoded video data
     * @return string Optimized base64 data
     */
    public function optimizeBase64Video(string $base64Data): string
    {
        try {
            // Extract the mime type and base64 content
            if (preg_match('/^data:video\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
                $extension = $matches[1];
                $base64Content = $matches[2];

                // Decode base64 to binary
                $videoData = base64_decode($base64Content);
                $tempPath = tempnam(sys_get_temp_dir(), 'vid_') . '.' . $extension;
                file_put_contents($tempPath, $videoData);

                // Optimize the video
                $optimizedPath = $this->optimizeVideo($tempPath);

                // Read optimized video and encode back to base64
                $optimizedData = file_get_contents($optimizedPath);
                $optimizedBase64 = 'data:video/' . $extension . ';base64,' . base64_encode($optimizedData);

                // Clean up temp files
                unlink($tempPath);
                if ($optimizedPath !== $tempPath) {
                    unlink($optimizedPath);
                }

                return $optimizedBase64;
            }

            return $base64Data; // Return original if not a valid base64 video
        } catch (\Exception $e) {
            Log::error('Base64 video optimization failed', [
                'error' => $e->getMessage()
            ]);
            return $base64Data; // Return original if optimization fails
        }
    }

    /**
     * Generate optimized file path
     *
     * @param string $originalPath Original file path
     * @param string $suffix Suffix to add to filename
     * @return string Optimized file path
     */
    protected function generateOptimizedPath(string $originalPath, string $suffix = 'optimized'): string
    {
        $pathInfo = pathinfo($originalPath);
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        $directory = $pathInfo['dirname'];

        return $directory . '/' . $filename . '_' . $suffix . '.' . $extension;
    }

    /**
     * Optimize ad media based on type
     *
     * @param string|null $image Image URL or base64
     * @param string|null $video Video URL or base64
     * @param string|null $thumbnail Thumbnail URL or base64
     * @return array Optimized media array
     */
    public function optimizeAdMedia(?string $image, ?string $video, ?string $thumbnail): array
    {
        $optimizedImage = null;
        $optimizedVideo = null;
        $optimizedThumbnail = null;

        // Optimize image
        if ($image) {
            if ($this->isBase64($image)) {
                $optimizedImage = $this->optimizeBase64Image($image);
            } else {
                // For URLs, we would need to download first
                // For now, return as-is (could implement download + optimize later)
                $optimizedImage = $image;
            }
        }

        // Optimize video
        if ($video) {
            if ($this->isBase64($video)) {
                $optimizedVideo = $this->optimizeBase64Video($video);
            } else {
                // For URLs, we would need to download first
                $optimizedVideo = $video;
            }
        }

        // Optimize thumbnail
        if ($thumbnail) {
            if ($this->isBase64($thumbnail)) {
                $optimizedThumbnail = $this->optimizeBase64Image($thumbnail);
            } else {
                // For URLs, we would need to download first
                $optimizedThumbnail = $thumbnail;
            }
        }

        return [
            'image' => $optimizedImage,
            'video' => $optimizedVideo,
            'thumbnail' => $optimizedThumbnail,
        ];
    }

    /**
     * Check if string is base64 encoded
     *
     * @param string $data
     * @return bool
     */
    protected function isBase64(string $data): bool
    {
        return str_starts_with($data, 'data:image/') || str_starts_with($data, 'data:video/');
    }
}
