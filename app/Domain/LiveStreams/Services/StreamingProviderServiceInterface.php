<?php

namespace App\Domain\LiveStreams\Services;

use App\Models\LiveStreamGumlet;
use App\Models\User;

interface StreamingProviderServiceInterface
{
    /** @return array{app_id: string, token: string, channel: string, uid: int, expires_at: int} */
    public function broadcasterCredentials(LiveStreamGumlet $stream, User $user): array;

    /** @return array{app_id: string, token: string, channel: string, uid: int, expires_at: int} */
    public function viewerCredentials(LiveStreamGumlet $stream, User $user): array;
}
