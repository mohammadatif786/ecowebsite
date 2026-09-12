<?php

use App\Http\Middleware\CheckWizardCompleted;
use App\Http\Middleware\EnsureOtpVerified;
use App\Models\ConversationPreference;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([CheckWizardCompleted::class, EnsureOtpVerified::class]);
});

test('a pass swipe is persisted as dislike', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create(['uid' => 'linkup-target']);

    $this->actingAs($actor)->postJson(route('new_frontend.linkup.swipe'), [
        'target_id' => $target->id, 'action' => 'pass',
    ])->assertOk()->assertJsonPath('match', false);

    expect(UserMatch::where('user_id', $actor->id)->where('target_user_id', $target->id)->value('status'))->toBe('dislike');
});

test('a user cannot swipe themselves', function () {
    $actor = User::factory()->create(['uid' => 'linkup-self']);

    $this->actingAs($actor)->postJson(route('new_frontend.linkup.swipe'), [
        'target_id' => $actor->id, 'action' => 'like',
    ])->assertForbidden();
});

test('conversation pins are personal', function () {
    $first = User::factory()->create();
    $second = User::factory()->create(['uid' => 'linkup-pin-target']);
    UserMatch::create(['user_id' => $first->id, 'target_user_id' => $second->id, 'status' => 'like']);
    UserMatch::create(['user_id' => $second->id, 'target_user_id' => $first->id, 'status' => 'like']);

    $this->actingAs($first)->postJson(route('new_frontend.dating.chats.pin', $second))->assertOk()->assertJsonPath('pinned', true);

    expect(ConversationPreference::where('user_id', $first->id)->where('other_user_id', $second->id)->value('is_pinned'))->toBeTrue()
        ->and(ConversationPreference::where('user_id', $second->id)->where('other_user_id', $first->id)->exists())->toBeFalse();
});
