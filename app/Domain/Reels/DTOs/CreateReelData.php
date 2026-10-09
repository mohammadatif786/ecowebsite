<?php

namespace App\Domain\Reels\DTOs;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

final readonly class CreateReelData
{
    public function __construct(
        public string $type,
        public UploadedFile $file,
        public ?string $caption,
        public ?string $location,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('type'),
            $request->file('file'),
            isset($request['caption']) ? trim($request['caption']) : $request->input('caption'),
            $request->input('location'),
        );
    }
}
