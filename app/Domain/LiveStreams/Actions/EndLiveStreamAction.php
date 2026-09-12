<?php

namespace App\Domain\LiveStreams\Actions;

use App\Models\LiveStreamGumlet;
use App\Services\LiveStreamLifecycleService;

class EndLiveStreamAction
{
    public function __construct(private readonly LiveStreamLifecycleService $lifecycle)
    {
    }

    public function execute(LiveStreamGumlet $stream): LiveStreamGumlet
    {
        // A page-close request can be delivered more than once. Only a currently
        // live stream may be completed; all retries are harmless no-ops.
        if ($stream->status === 'live') {
            $this->lifecycle->complete($stream);
        }

        return $stream->fresh();
    }
}
