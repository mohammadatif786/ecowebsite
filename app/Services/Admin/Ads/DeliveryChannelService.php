<?php

namespace App\Services\Admin\Ads;

use App\Models\DeliveryChannel;
use Illuminate\Database\Eloquent\Collection;

class DeliveryChannelService
{
    public function getAllChannels(): Collection
    {
        return DeliveryChannel::orderBy('id')->get();
    }

    public function createChannel(array $data): DeliveryChannel
    {
        return DeliveryChannel::create($data);
    }

    public function updateChannel(DeliveryChannel $channel, array $data): DeliveryChannel
    {
        $channel->update($data);

        return $channel->fresh();
    }

    public function deleteChannel(DeliveryChannel $channel): void
    {
        $channel->delete();
    }
}
