<?php

namespace App\Domain\LiveStreams\Repositories;

use App\Models\LiveStreamGumlet;
use App\Models\User;

class EloquentLiveStreamRepository implements LiveStreamRepositoryInterface
{
    public function hasActiveStream(User $user): bool
    {
        return LiveStreamGumlet::query()->where('user_id', $user->id)->where('status', 'live')->exists();
    }

    public function create(array $attributes): LiveStreamGumlet
    {
        return LiveStreamGumlet::create($attributes);
    }
}
