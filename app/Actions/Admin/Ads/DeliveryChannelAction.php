<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Ads\DeliveryChannelData;
use App\Models\DeliveryChannel;
use App\Services\Admin\Ads\DeliveryChannelService;
use Illuminate\Database\Eloquent\Collection;

class DeliveryChannelAction
{
    public function __construct(
        private DeliveryChannelService $service
    ) {}

    public function list(): Collection
    {
        return $this->service->getAllChannels();
    }

    public function execute(DeliveryChannelData $data): DeliveryChannel
    {
        return $this->service->createChannel($data->toArray());
    }

    public function update(DeliveryChannel $channel, DeliveryChannelData $data): DeliveryChannel
    {
        return $this->service->updateChannel($channel, $data->toArray());
    }

    public function delete(DeliveryChannel $channel): void
    {
        $this->service->deleteChannel($channel);
    }
}
