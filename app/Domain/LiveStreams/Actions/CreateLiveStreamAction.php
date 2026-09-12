<?php

namespace App\Domain\LiveStreams\Actions;

use App\Domain\LiveStreams\DTOs\CreateLiveStreamData;
use App\Domain\LiveStreams\Enums\LiveStreamStatus;
use App\Domain\LiveStreams\Repositories\LiveStreamRepositoryInterface;
use App\Models\LiveStreamGumlet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateLiveStreamAction
{
    public function __construct(private readonly LiveStreamRepositoryInterface $streams) {}

    public function execute(User $user, CreateLiveStreamData $data): LiveStreamGumlet
    {
        return DB::transaction(function () use ($user, $data) {
            if ($this->streams->hasActiveStream($user)) {
                throw ValidationException::withMessages(['stream' => 'You already have an active live stream.']);
            }

            $channel = 'live_'.$user->id.'_'.Str::lower(Str::random(16));
            $coverImage = $data->coverImage?->store('streams_covers', 'public');

            return $this->streams->create([
                'user_id' => $user->id,
                'title' => $data->title,
                'broadcast_type' => $data->category,
                'location' => $data->location,
                'visibility' => $data->visibility->value,
                'subscription_rate' => $data->subscriptionRate,
                'base_resolution' => $data->baseResolution,
                'output_resolution' => $data->outputResolution,
                'resolution' => $data->outputResolution,
                'status' => LiveStreamStatus::Live->value,
                'start_time' => now(),
                'host_heartbeat_at' => now(),
                // Legacy columns are retained for compatibility; the generated channel is not a provider secret.
                'stream_key' => $channel,
                'stream_url' => $channel,
                'live_asset_id' => 'agora_'.Str::random(16),
                'live_video_source_id' => 'agora',
                'playback_url' => '',
                'cover_image' => $coverImage,
                'products' => array_slice($data->products, 0, 20),
            ]);
        });
    }
}
