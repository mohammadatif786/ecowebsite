<?php

use App\Domain\LiveStreams\Enums\LiveStreamStatus;

test('live stream status transitions are explicit', function () {
    expect(LiveStreamStatus::canTransition('created', LiveStreamStatus::Live))->toBeTrue()
        ->and(LiveStreamStatus::canTransition('live', LiveStreamStatus::Ended))->toBeTrue()
        ->and(LiveStreamStatus::canTransition('ended', LiveStreamStatus::Live))->toBeFalse()
        ->and(LiveStreamStatus::canTransition('live', LiveStreamStatus::Live))->toBeFalse();
});
