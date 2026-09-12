<?php

use App\Domain\LiveStreams\Actions\EndLiveStreamAction;
use App\Models\LiveStreamGumlet;
use App\Models\User;
use App\Services\LiveStreamService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function liveStreamForCompletion(User $owner, string $status = 'live'): LiveStreamGumlet
{
    return LiveStreamGumlet::create([
        'user_id' => $owner->id,
        'title' => 'Completion test stream',
        'status' => $status,
        'visibility' => 'public',
        'stream_key' => 'test',
        'live_asset_id' => 'test',
        'live_video_source_id' => 'test',
        'resolution' => '720p',
        'stream_url' => 'test',
        'playback_url' => '',
    ]);
}

test('ending an active stream marks it completed and makes repeat shutdown requests safe', function () {
    $stream = liveStreamForCompletion(User::factory()->create());
    $action = app(EndLiveStreamAction::class);

    $completed = $action->execute($stream);
    $retried = $action->execute($completed);

    expect($completed->status)->toBe('completed')
        ->and($completed->ended_at)->not->toBeNull()
        ->and($retried->status)->toBe('completed')
        ->and(LiveStreamGumlet::findOrFail($stream->id)->status)->toBe('completed');
});

test('a shutdown request never changes a stream that is not live', function () {
    $stream = liveStreamForCompletion(User::factory()->create(), 'cancelled');

    $result = app(EndLiveStreamAction::class)->execute($stream);

    expect($result->status)->toBe('cancelled')
        ->and($result->ended_at)->toBeNull();
});

test('a recently completed stream remains in discovery as ended for ten minutes', function () {
    $owner = User::factory()->create();
    $stream = liveStreamForCompletion($owner);
    $stream->forceFill(['start_time' => now()->subMinutes(2)])->save();

    app(EndLiveStreamAction::class)->execute($stream);

    $listed = $this->actingAs($owner)
        ->app->make(LiveStreamService::class)
        ->getStreamsForFrontend()
        ->firstWhere('id', $stream->id);

    expect($listed)->not->toBeNull()
        ->and($listed['status'])->toBe('ended')
        ->and($listed['ended_at'])->not->toBeNull()
        ->and($listed['ended_expires_at'])->not->toBeNull()
        ->and($listed['duration_seconds'])->toBeGreaterThanOrEqual(119);
});

test('a completed stream leaves discovery after the ten minute window', function () {
    $owner = User::factory()->create();
    $stream = liveStreamForCompletion($owner, 'completed');
    $stream->forceFill([
        'start_time' => now()->subMinutes(20),
        'ended_at' => now()->subMinutes(11),
    ])->save();

    $listed = $this->actingAs($owner)
        ->app->make(LiveStreamService::class)
        ->getStreamsForFrontend()
        ->firstWhere('id', $stream->id);

    expect($listed)->toBeNull();
});
