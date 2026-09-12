<?php

namespace App\Domain\LiveStreams\Repositories;

use App\Models\LiveStreamGumlet;
use App\Models\User;

interface LiveStreamRepositoryInterface
{
    public function hasActiveStream(User $user): bool;

    public function create(array $attributes): LiveStreamGumlet;
}
