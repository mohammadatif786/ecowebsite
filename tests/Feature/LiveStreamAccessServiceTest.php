<?php

use App\Domain\LiveStreams\Services\LiveStreamAccessService;
use App\Http\Middleware\CheckWizardCompleted;
use App\Http\Middleware\EnsureOtpVerified;
use App\Models\LiveStreamGumlet;
use App\Models\PrivateLiveStreamSub;
use App\Models\User;
use App\Policies\LiveStreamPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function liveStreamForAccess(User $owner, string $visibility = 'public', string $status = 'live'): LiveStreamGumlet
{
    return LiveStreamGumlet::create([
        'user_id' => $owner->id,
        'title' => 'Test stream',
        'status' => $status,
        'visibility' => $visibility,
        'stream_key' => 'test',
        'live_asset_id' => 'test',
        'live_video_source_id' => 'test',
        'resolution' => '720p',
        'stream_url' => 'test',
        'playback_url' => '',
    ]);
}

test('owner and authorized admin can join a live session', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    Role::findOrCreate('admin');
    $admin->assignRole('admin');
    $stream = liveStreamForAccess($owner);
    $access = app(LiveStreamAccessService::class);

    expect($access->mayJoinSession($owner, $stream))->toBeTrue()
        ->and($access->mayJoinSession($admin, $stream))->toBeTrue()
        ->and(app(LiveStreamPolicy::class)->joinLiveSession($owner, $stream))->toBeTrue()
        ->and(app(LiveStreamPolicy::class)->joinLiveSession($admin, $stream))->toBeTrue();
});

test('an authenticated viewer can join a public live stream but a guest is denied', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $access = app(LiveStreamAccessService::class);
    $stream = liveStreamForAccess($owner);

    expect($access->mayJoinSession($viewer, $stream))->toBeTrue()
        ->and(app(LiveStreamPolicy::class)->joinLiveSession($viewer, $stream))->toBeTrue()
        ->and(Gate::forUser(null)->allows('joinLiveSession', $stream))->toBeFalse();
});

test('stream details support providers without a Gumlet playback URL', function () {
    $owner = User::factory()->create();
    $stream = liveStreamForAccess($owner);

    $this->withoutMiddleware([
        CheckWizardCompleted::class,
        EnsureOtpVerified::class,
    ]);

    $this->actingAs($owner)
        ->getJson(route('frontend.livestream.join', ['id' => $stream->id]))
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('liveStream.id', $stream->id)
        ->assertJsonPath('playBackKey', null);
});

test('no user can join a stream that is not live', function (string $status) {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $stream = liveStreamForAccess($owner, 'public', $status);
    $access = app(LiveStreamAccessService::class);

    expect($access->mayJoinSession($owner, $stream))->toBeFalse()
        ->and($access->mayJoinSession($viewer, $stream))->toBeFalse()
        ->and(app(LiveStreamPolicy::class)->joinLiveSession($viewer, $stream))->toBeFalse();
})->with(['ended', 'cancelled', 'draft']);

test('a private stream requires an active subscription under the current schema', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $private = liveStreamForAccess($owner, 'private');
    $access = app(LiveStreamAccessService::class);

    expect($access->mayJoinSession($viewer, $private))->toBeFalse();

    PrivateLiveStreamSub::create([
        'user_streamer_id' => $owner->id,
        'pay_user_id' => $viewer->id,
        'stream_id' => $private->id,
        'payment_type' => 'one_time',
        'pay_amount' => 1,
        'status' => 'active',
    ]);

    expect($access->mayJoinSession($viewer, $private))->toBeTrue()
        ->and(app(LiveStreamPolicy::class)->joinLiveSession($viewer, $private))->toBeTrue();
});

test('followers streams remain denied until a reliable follower access rule exists', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $stream = liveStreamForAccess($owner, 'followers');

    expect(app(LiveStreamAccessService::class)->mayJoinSession($viewer, $stream))->toBeFalse()
        ->and(app(LiveStreamPolicy::class)->joinLiveSession($viewer, $stream))->toBeFalse();
});

test('only the owner or a persisted joined guest may broadcast to a live stream', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    $viewer = User::factory()->create();
    $stream = liveStreamForAccess($owner);
    $policy = app(LiveStreamPolicy::class);

    expect($policy->broadcast($owner, $stream))->toBeTrue()
        ->and($policy->broadcast($guest, $stream))->toBeFalse()
        ->and($policy->broadcast($viewer, $stream))->toBeFalse();

    $stream->update([
        'guests' => [[
            'id' => $guest->id,
            'name' => $guest->name,
            'status' => 'joined',
        ]],
    ]);

    expect($policy->broadcast($guest, $stream->fresh()))->toBeTrue()
        ->and($policy->broadcast($viewer, $stream->fresh()))->toBeFalse();

    $stream->update(['status' => 'completed']);

    expect($policy->broadcast($guest, $stream->fresh()))->toBeFalse();
});

test('guest invitations and removal enforce ownership and a real invitation', function () {
    $owner = User::factory()->create(['name' => 'Invite Owner']);
    $guest = User::factory()->create(['name' => 'Invited Guest', 'linkup_id' => 'invited-guest-test']);
    $outsider = User::factory()->create(['name' => 'Outside User']);
    $stream = liveStreamForAccess($owner);
    $this->withoutMiddleware([CheckWizardCompleted::class, EnsureOtpVerified::class]);

    $this->actingAs($outsider)->postJson(route('frontend.live.invite', $stream), ['username' => 'invited-guest-test'])->assertForbidden();
    $this->actingAs($guest)->postJson(route('frontend.live.invite.reply', $stream), ['accept' => true])->assertForbidden();
    $this->actingAs($owner)->postJson(route('frontend.live.invite', $stream), ['username' => 'invited-guest-test'])->assertOk();
    $this->actingAs($guest)->postJson(route('frontend.live.invite.reply', $stream), ['accept' => true])->assertOk();
    $this->actingAs($outsider)->postJson(route('frontend.live.guest.remove', $stream), ['guest_id' => $guest->id])->assertForbidden();
});

test('audience can ask questions and vote once while only the host manages polls', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $stream = liveStreamForAccess($owner);
    $this->withoutMiddleware([CheckWizardCompleted::class, EnsureOtpVerified::class]);

    $this->actingAs($viewer)->postJson(route('frontend.live.poll.create', $stream), [
        'question' => 'Viewer poll?', 'options' => ['Yes', 'No'],
    ])->assertForbidden();

    $pollId = $this->actingAs($owner)->postJson(route('frontend.live.poll.create', $stream), [
        'question' => 'Choose one', 'options' => ['First', 'Second'],
    ])->assertOk()->json('poll.id');

    $this->actingAs($viewer)->postJson(route('frontend.live.poll.vote', ['stream' => $stream, 'poll' => $pollId]), ['option_id' => 1])->assertOk();
    $this->actingAs($viewer)->postJson(route('frontend.live.poll.vote', ['stream' => $stream, 'poll' => $pollId]), ['option_id' => 2])->assertStatus(409);

    $this->actingAs($viewer)->postJson(route('frontend.live.qna.submit', $stream), ['text' => 'Can the host see this?'])->assertOk();
    $this->actingAs($owner)->getJson(route('frontend.live.state', $stream))
        ->assertOk()
        ->assertJsonPath('qna.0.text', 'Can the host see this?');
});

test('the access service queries subscriptions only for private non-owner viewers', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $access = app(LiveStreamAccessService::class);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $access->mayJoinSession($viewer, liveStreamForAccess($owner, 'public'));
    $publicSubscriptionQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query) => str_contains($query['query'], 'private_live_stream_subs'));

    DB::flushQueryLog();
    $access->mayJoinSession($viewer, liveStreamForAccess($owner, 'private'));
    $privateSubscriptionQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query) => str_contains($query['query'], 'private_live_stream_subs'));
    DB::disableQueryLog();

    expect($publicSubscriptionQueries)->toHaveCount(0)
        ->and($privateSubscriptionQueries)->toHaveCount(2);
});
