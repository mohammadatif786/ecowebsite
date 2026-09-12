<?php

namespace App\Domain\LiveStreams\Services;

use App\Models\LiveStreamGumlet;
use App\Models\User;
use RuntimeException;
use Yasser\Agora\RtcTokenBuilder;

class AgoraStreamingProviderService implements StreamingProviderServiceInterface
{
    public function broadcasterCredentials(LiveStreamGumlet $stream, User $user): array
    {
        return $this->credentials($stream, $user, 1);
    }

    public function viewerCredentials(LiveStreamGumlet $stream, User $user): array
    {
        return $this->credentials($stream, $user, 2);
    }

    private function credentials(LiveStreamGumlet $stream, User $user, int $role): array
    {
        $appId = (string) config('services.agora.app_id');
        $certificate = (string) config('services.agora.app_certificate');
        $channel = (string) $stream->stream_url;

        if ($appId === '' || $certificate === '' || $channel === '') {
            throw new RuntimeException('Live streaming provider is not configured.');
        }

        $expiresAt = now()->addHour()->timestamp;

        return [
            'app_id' => $appId,
            'token' => RtcTokenBuilder::buildTokenWithUid($appId, $certificate, $channel, $user->id, $role, $expiresAt),
            'channel' => $channel,
            'uid' => (int) $user->id,
            'expires_at' => $expiresAt,
        ];
    }
}
