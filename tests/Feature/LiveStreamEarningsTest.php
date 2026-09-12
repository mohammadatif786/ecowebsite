<?php

use App\Models\LiveStreamGumlet;
use App\Models\PrivateLiveStreamSub;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LiveStreamService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('private subscription revenue is included in host earnings and live analytics', function () {
    $host = User::factory()->create();
    $viewer = User::factory()->create();
    $stream = LiveStreamGumlet::create([
        'user_id' => $host->id,
        'title' => 'Paid private live',
        'status' => 'live',
        'visibility' => 'private',
        'stream_key' => 'earnings-test',
        'live_asset_id' => 'earnings-test',
        'live_video_source_id' => 'earnings-test',
        'resolution' => '720p',
        'stream_url' => 'earnings-test',
        'playback_url' => '',
    ]);

    PrivateLiveStreamSub::create([
        'user_streamer_id' => $host->id,
        'pay_user_id' => $viewer->id,
        'stream_id' => $stream->id,
        'payment_type' => 'one-time',
        'pay_amount' => 15.00,
        'status' => 'active',
        'stripe_session_id' => 'cs_earnings_test',
    ]);

    $service = app(LiveStreamService::class);
    $earnings = $service->getEarningsData($host);
    $analytics = $service->getAnalyticsData($host);

    expect($earnings['collected'])->toBe(15.0)
        ->and($earnings['subscription_gross'])->toBe(15.0)
        ->and($earnings['transferred'])->toBe(0.0)
        ->and($analytics['subscriptions']['revenueGross'])->toBe(15.0)
        ->and($analytics['sessions'][0]['subs'])->toBe(15.0);
});

test('live earnings payout is server calculated and idempotent', function () {
    $host = User::factory()->create();
    $viewer = User::factory()->create();
    $stream = LiveStreamGumlet::create([
        'user_id' => $host->id,
        'title' => 'Secure payout stream',
        'status' => 'live',
        'visibility' => 'private',
        'stream_key' => 'secure-payout',
        'live_asset_id' => 'secure-payout',
        'live_video_source_id' => 'secure-payout',
        'resolution' => '720p',
        'stream_url' => 'secure-payout',
        'playback_url' => '',
    ]);
    PrivateLiveStreamSub::create([
        'user_streamer_id' => $host->id,
        'pay_user_id' => $viewer->id,
        'stream_id' => $stream->id,
        'payment_type' => 'one-time',
        'pay_amount' => 10.00,
        'status' => 'active',
        'stripe_session_id' => 'cs_secure_payout',
    ]);

    $key = (string) str()->uuid();
    $service = app(LiveStreamService::class);
    $first = $service->transferEarnings($host, $key);
    $retry = $service->transferEarnings($host, $key);

    expect($first['success'])->toBeTrue()
        ->and($first['transferred'])->toBe(5.0)
        ->and($retry['success'])->toBeTrue()
        ->and($retry['transferred'])->toBe(5.0)
        ->and(Transaction::where('uuid', $key)->count())->toBe(1);
});
