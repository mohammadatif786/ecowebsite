<?php

use App\Models\BlockedUser;
use App\Models\ReelComment;
use App\Models\User;
use App\Models\UserReel;
use App\Models\Vibe;
use App\Models\VibeComment;
use Illuminate\Support\Facades\DB;

function reelBy(User $owner, array $extra = []): UserReel
{
    return UserReel::create($extra + ['uid' => uniqid(), 'user_id' => $owner->id, 'type' => 'image', 'file_path' => 'r.jpg', 'status' => 'active']);
}

function postBy(User $owner, array $extra = []): Vibe
{
    return Vibe::create($extra + ['created_by' => $owner->id, 'publisher_type' => 'user', 'publisher_id' => $owner->id, 'caption' => 'hi', 'visibility' => 'public', 'status' => 'published', 'published_at' => now()]);
}

// ---- Reels and blocks ---------------------------------------------------------

it('hides a reel from someone the creator blocked, in both directions', function () {
    [$creator, $viewer] = User::factory()->count(2)->create();
    $reel = reelBy($creator);

    $this->actingAs($viewer)->getJson("/user/reels/{$reel->id}")->assertOk()->assertJson(['id' => $reel->id]);

    BlockedUser::create(['user_id' => $creator->id, 'blocked_user_id' => $viewer->id, 'is_blocked' => true]);
    $this->actingAs($viewer)->getJson("/user/reels/{$reel->id}")->assertNotFound();
    $this->actingAs($viewer)->postJson("/user/reels/{$reel->id}/like")->assertNotFound();
    $this->actingAs($viewer)->postJson("/user/reels/{$reel->id}/comments", ['comment' => 'hey'])->assertNotFound();
    $this->actingAs($viewer)->postJson("/user/reels/{$reel->id}/bigup", ['coins' => 5])->assertNotFound();
    $this->actingAs($viewer)->getJson("/user/reels/user/{$creator->id}")->assertNotFound();

    // The viewer blocking the creator hides it too.
    BlockedUser::query()->delete();
    BlockedUser::create(['user_id' => $viewer->id, 'blocked_user_id' => $creator->id, 'is_blocked' => true]);
    $this->actingAs($viewer)->getJson("/user/reels/{$reel->id}")->assertNotFound();
});

it('leaves blocked creators out of the followed reels list', function () {
    [$me, $friend, $blocker] = User::factory()->count(3)->create();
    DB::table('creator_followers')->insert([
        ['follower_id' => $me->id, 'creator_id' => $friend->id],
        ['follower_id' => $me->id, 'creator_id' => $blocker->id],
    ]);
    $keep = reelBy($friend);
    reelBy($blocker);
    BlockedUser::create(['user_id' => $blocker->id, 'blocked_user_id' => $me->id, 'is_blocked' => true]);

    $ids = collect($this->actingAs($me)->getJson('/user/reels/followed')->assertOk()->json())->pluck('id');

    expect($ids->all())->toBe([$keep->id]);
});

it('does not show an inactive reel to other members', function () {
    [$creator, $viewer] = User::factory()->count(2)->create();
    $reel = reelBy($creator, ['status' => 'removed']);

    $this->actingAs($viewer)->getJson("/user/reels/{$reel->id}")->assertNotFound();
    $this->actingAs($creator)->getJson("/user/reels/{$reel->id}")->assertOk();
});

it('keeps the reel like count right on repeated taps', function () {
    [$creator, $viewer] = User::factory()->count(2)->create();
    $reel = reelBy($creator, ['likes_count' => 7]); // drifted count

    $this->actingAs($viewer)->postJson("/user/reels/{$reel->id}/like")->assertJson(['is_liked' => true, 'likes_count' => 1]);
    $this->actingAs($viewer)->postJson("/user/reels/{$reel->id}/like")->assertJson(['is_liked' => false, 'likes_count' => 0]);
    expect($reel->fresh()->likes_count)->toBe(0);
});

// ---- Deleting other people's things -------------------------------------------------

it('lets nobody but the creator delete a reel or edit / delete a post', function () {
    [$owner, $other] = User::factory()->count(2)->create();
    $reel = reelBy($owner);
    $post = postBy($owner);

    $this->actingAs($other)->deleteJson("/user/reels/{$reel->id}")->assertForbidden();
    $this->actingAs($other)->putJson("/user/vibes/{$post->id}", ['caption' => 'hacked'])->assertForbidden();
    $this->actingAs($other)->deleteJson("/user/vibes/{$post->id}")->assertForbidden();

    expect(UserReel::find($reel->id))->not->toBeNull()
        ->and($post->fresh()->caption)->toBe('hi');
});

it('lets the writer or the reel creator delete a comment, and nobody else', function () {
    [$owner, $writer, $stranger] = User::factory()->count(3)->create();
    $reel = reelBy($owner, ['comments_count' => 3]);
    $c1 = ReelComment::create(['user_reel_id' => $reel->id, 'user_id' => $writer->id, 'comment' => 'one']);
    ReelComment::create(['user_reel_id' => $reel->id, 'user_id' => $stranger->id, 'comment' => 'reply', 'parent_id' => $c1->id]);
    $c2 = ReelComment::create(['user_reel_id' => $reel->id, 'user_id' => $writer->id, 'comment' => 'two']);

    $this->actingAs($stranger)->deleteJson("/user/reel-comments/{$c1->id}")->assertForbidden();
    $this->actingAs($writer)->deleteJson("/user/reel-comments/{$c1->id}")->assertOk()->assertJson(['comments_count' => 1]);
    $this->actingAs($owner)->deleteJson("/user/reel-comments/{$c2->id}")->assertOk()->assertJson(['comments_count' => 0]);
});

it('lets only the writer or the post creator delete a post comment', function () {
    [$owner, $writer, $stranger] = User::factory()->count(3)->create();
    $post = postBy($owner);
    $c = VibeComment::create(['vibe_id' => $post->id, 'user_id' => $writer->id, 'comment' => 'x']);

    $this->actingAs($stranger)->deleteJson("/user/vibe-comments/{$c->id}")->assertForbidden();
    $this->actingAs($owner)->deleteJson("/user/vibe-comments/{$c->id}")->assertOk();
    expect(VibeComment::find($c->id))->toBeNull();
});

// ---- Editing a post -------------------------------------------------------------

it('updates hashtags when a post is edited', function () {
    $owner = User::factory()->create();
    $post = postBy($owner, ['caption' => '#fete tonight']);
    $post->syncHashtags($post->caption);

    $this->actingAs($owner)->putJson("/user/vibes/{$post->id}", ['caption' => 'now #Carnival #carnival #soca'])->assertOk();

    expect(DB::table('vibe_hashtags')->where('vibe_id', $post->id)->orderBy('tag')->pluck('tag')->all())->toBe(['carnival', 'soca'])
        ->and($post->fresh()->edited_at)->not->toBeNull();
});

it('refuses a text background that is not a plain colour or gradient', function () {
    $owner = User::factory()->create();
    $post = postBy($owner);

    $this->actingAs($owner)->putJson("/user/vibes/{$post->id}", ['text_bg' => 'red; background:url(//evil.example/x.png)'])->assertStatus(422);
    $this->actingAs($owner)->putJson("/user/vibes/{$post->id}", ['text_bg' => 'linear-gradient(135deg, #a855f7, #ec4899)'])->assertOk();
    $this->actingAs($owner)->putJson("/user/vibes/{$post->id}", ['text_bg' => '#0d9488'])->assertOk();
});

it('keeps the post like count right on repeated taps', function () {
    [$owner, $viewer] = User::factory()->count(2)->create();
    $post = postBy($owner, ['likes_count' => 9]);

    $this->actingAs($viewer)->postJson("/user/vibes/{$post->id}/like")->assertJson(['is_liked' => true, 'likes_count' => 1]);
    $this->actingAs($viewer)->postJson("/user/vibes/{$post->id}/like")->assertJson(['is_liked' => false, 'likes_count' => 0]);
});

// ---- Say thank you ----------------------------------------------------------------

it('only lets you thank someone who actually sent you a gift', function () {
    [$me, $fan, $stranger] = User::factory()->count(3)->create();
    \App\Models\GiftCoins::create(['sender_id' => $fan->id, 'recieved_id' => $me->id, 'name' => 'Big Up', 'emoji' => '⚡', 'coins' => 25]);

    $this->actingAs($me)->postJson('/user/vibes/thank-sender', ['user_id' => $stranger->id, 'message' => 'spam'])->assertStatus(422);
    $this->actingAs($me)->postJson('/user/vibes/thank-sender', ['user_id' => $me->id, 'message' => 'me'])->assertStatus(422);
    $this->actingAs($me)->postJson('/user/vibes/thank-sender', ['user_id' => $fan->id, 'message' => str_repeat('x', 501)])->assertStatus(422);
    $this->actingAs($me)->postJson('/user/vibes/thank-sender', ['user_id' => $fan->id, 'message' => '<b>Thanks!</b>'])->assertOk();

    expect(DB::table('notifications')->where('user_id', $fan->id)->value('message'))->toBe('Thanks!')
        ->and(DB::table('notifications')->where('user_id', $stranger->id)->count())->toBe(0);
});

// ---- Stories bar ----------------------------------------------------------------------

it('shows one story circle per followed creator with a recent reel, never a blocked one', function () {
    [$me, $a, $b, $old] = User::factory()->count(4)->create();
    reelBy($a); reelBy($a); reelBy($b);
    $stale = reelBy($old);
    DB::table('user_reels')->where('id', $stale->id)->update(['created_at' => now()->subDays(3)]);
    BlockedUser::create(['user_id' => $b->id, 'blocked_user_id' => $me->id, 'is_blocked' => true]);

    $stories = \App\Support\ReelPresenter::stories($me, [$a->id, $b->id, $old->id, $me->id]);

    expect(collect($stories)->pluck('user_id')->all())->toBe([$a->id]);
});
