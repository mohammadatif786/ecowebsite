<?php

namespace App\Domain\Reels\Actions;

use App\Domain\Reels\DTOs\CreateReelData;
use App\Models\User;
use App\Models\UserReel;
use Illuminate\Support\Str;

class CreateReelAction
{
    public function execute(User $user, CreateReelData $data): UserReel
    {
        $file = $data->file;
        $type = $data->type;

        if ($type === 'video') {
            $path = $file->store('reels/videos', 'public');
        } elseif ($type === 'image') {
            $path = $file->store('reels/images', 'public');
        } else {
            $path = $file->store('reels/gallery', 'public');
        }

        $reel = UserReel::create([
            'uid' => Str::uuid(),
            'user_id' => $user->id,
            'type' => $type,
            'file_path' => $path,
            'thumbnail_path' => null,
            'caption' => $data->caption,
            'location' => $data->location,
            'status' => 'active',
        ]);

        return $reel->load('user:id,name,avatar,linkup_id,city,country');
    }
}
