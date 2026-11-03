<?php

use App\Http\Middleware\CheckWizardCompleted;
use App\Http\Middleware\EnsureOtpVerified;
use App\Models\BlockedUser;
use App\Models\CreatorFollower;
use App\Models\MarketplaceAffiliateEarning;
use App\Models\ReelComment;
use App\Models\User;
use App\Models\UserReel;
use App\Models\Vibe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([EnsureOtpVerified::class, CheckWizardCompleted::class]);
});

function apiUser(array $attributes = []): User
{
    return User::factory()->create(array_merge(['status' => true, 'is_active' => true, 'coins' => 0], $attributes));
}

function makeFriends(User $a, User $b): void
{
    DB::table('friend_requests')->insert([
        'uid' => (string) Str::uuid(), 'user_id' => $a->id, 'receiver_id' => $b->id,
        'status' => 1, 'type' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function makeReel(User $owner): UserReel
{
    return UserReel::create(['uid' => (string) Str::uuid(), 'user_id' => $owner->id, 'type' => 'image', 'file_path' => 'reels/images/test.jpg', 'status' => 'active']);
}

function feedIds(): array
{
    return collect(test()->getJson('/api/regular-user/vibes/posts')->assertOk()->json('posts'))->pluck('id')->all();
}

// ---------------------------------------------------------------- 1. coins

test('Big Up moves coins once and refuses when the balance is too low', function () {
    $sender = apiUser(['coins' => 100]);
    $creator = apiUser();
    $vibe = Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id]);
    Sanctum::actingAs($sender);

    $this->postJson("/api/regular-user/vibes/{$vibe->id}/bigup", ['coins' => 60, 'gift_name' => 'Big Up', 'emoji' => '🙌'])
        ->assertOk()->assertJsonPath('user_coins', 40)->assertJsonPath('bigups_count', 1);

    $this->postJson("/api/regular-user/vibes/{$vibe->id}/bigup", ['coins' => 60])
        ->assertUnprocessable()->assertJsonPath('message', 'Insufficient coins');

    expect($sender->fresh()->coins)->toBe(40)
        ->and($creator->fresh()->coins)->toBe(60)
        ->and(DB::table('gift_coins')->count())->toBe(1);
});

test('Big Up on a post always pays the post creator, not a recipient_id from the request', function () {
    $sender = apiUser(['coins' => 50]);
    $creator = apiUser();
    $other = apiUser();
    $vibe = Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id]);
    Sanctum::actingAs($sender);

    $this->postJson("/api/regular-user/vibes/{$vibe->id}/bigup", ['coins' => 10, 'recipient_id' => $other->id])->assertOk();

    expect($creator->fresh()->coins)->toBe(10)->and($other->fresh()->coins)->toBe(0);
});

test('Big Up is refused on your own post and when gifts are turned off', function () {
    $user = apiUser(['coins' => 50]);
    $own = Vibe::factory()->create(['created_by' => $user->id, 'publisher_id' => $user->id]);
    $creator = apiUser();
    $giftsOff = Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id, 'allow_coin_gifts' => false]);
    Sanctum::actingAs($user);

    $this->postJson("/api/regular-user/vibes/{$own->id}/bigup", ['coins' => 10])->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$giftsOff->id}/bigup", ['coins' => 10])->assertUnprocessable();

    expect($user->fresh()->coins)->toBe(50);
});

test('reel gifts and Big Ups can’t overspend', function () {
    $sender = apiUser(['coins' => 30]);
    $reel = makeReel(apiUser());
    Sanctum::actingAs($sender);

    $this->postJson("/api/regular-user/reels/{$reel->id}/gift", ['coins' => 25, 'gift_name' => 'Rose', 'emoji' => '🌹'])->assertOk()->assertJsonPath('user_coins', 5);
    $this->postJson("/api/regular-user/reels/{$reel->id}/bigup", ['coins' => 25])->assertUnprocessable();

    expect($sender->fresh()->coins)->toBe(5);
});

// ------------------------------------------------------------- 2. earnings

test('commissions are only released after the hold period, never order commissions', function () {
    $user = apiUser();
    $fresh = MarketplaceAffiliateEarning::create(['affiliate_user_id' => $user->id, 'commission_amount' => 5, 'status' => 'pending']);
    $old = MarketplaceAffiliateEarning::create(['affiliate_user_id' => $user->id, 'commission_amount' => 7, 'status' => 'pending']);
    $old->forceFill(['created_at' => now()->subDays(8)])->save();
    // A marketplace order commission (the order row itself isn't needed for this check).
    Schema::disableForeignKeyConstraints();
    $order = MarketplaceAffiliateEarning::create(['affiliate_user_id' => $user->id, 'commission_amount' => 9, 'status' => 'pending', 'order_id' => 123]);
    $order->forceFill(['created_at' => now()->subDays(30)])->save();
    Schema::enableForeignKeyConstraints();
    Sanctum::actingAs($user);

    $this->postJson('/api/regular-user/vibes/earnings/release')->assertOk()->assertJsonPath('count', 1);

    expect($fresh->fresh()->status)->toBe('pending')
        ->and($old->fresh()->status)->toBe('released')
        ->and($order->fresh()->status)->toBe('pending');
});

test('summary counts marketplace "available" commissions as available', function () {
    $user = apiUser();
    MarketplaceAffiliateEarning::create(['affiliate_user_id' => $user->id, 'commission_amount' => 4, 'status' => 'available']);
    MarketplaceAffiliateEarning::create(['affiliate_user_id' => $user->id, 'commission_amount' => 6, 'status' => 'released']);
    Sanctum::actingAs($user);

    $this->getJson('/api/regular-user/vibes/earnings')->assertOk()->assertJsonPath('earnings.available', 10);
});

// ----------------------------------------------------------------- 3. feed

test('feed hides private, friends-only and blocked posts from the wrong people', function () {
    $viewer = apiUser();
    $friend = apiUser();
    $stranger = apiUser();
    $blocker = apiUser();
    makeFriends($friend, $viewer);
    BlockedUser::create(['user_id' => $blocker->id, 'blocked_user_id' => $viewer->id, 'is_blocked' => true]);

    $public = Vibe::factory()->create(['created_by' => $stranger->id, 'publisher_id' => $stranger->id]);
    $strangerPrivate = Vibe::factory()->create(['created_by' => $stranger->id, 'publisher_id' => $stranger->id, 'visibility' => 'private']);
    $strangerFriends = Vibe::factory()->create(['created_by' => $stranger->id, 'publisher_id' => $stranger->id, 'visibility' => 'friends']);
    $friendFriends = Vibe::factory()->create(['created_by' => $friend->id, 'publisher_id' => $friend->id, 'visibility' => 'friends']);
    $blockerPublic = Vibe::factory()->create(['created_by' => $blocker->id, 'publisher_id' => $blocker->id]);
    $ownPrivate = Vibe::factory()->create(['created_by' => $viewer->id, 'publisher_id' => $viewer->id, 'visibility' => 'private']);
    Sanctum::actingAs($viewer);

    $ids = feedIds();

    expect($ids)->toContain($public->id, $friendFriends->id, $ownPrivate->id)
        ->not->toContain($strangerPrivate->id)
        ->not->toContain($strangerFriends->id)
        ->not->toContain($blockerPublic->id);

    $this->getJson("/api/regular-user/vibes/{$strangerPrivate->id}/comments")->assertNotFound();
});

test('feed pages through posts', function () {
    $viewer = apiUser();
    Vibe::factory()->count(25)->create();
    Sanctum::actingAs($viewer);

    $this->getJson('/api/regular-user/vibes/posts')->assertOk()->assertJsonCount(20, 'posts')->assertJsonPath('has_more', true);
    $this->getJson('/api/regular-user/vibes/posts?page=2')->assertOk()->assertJsonCount(5, 'posts')->assertJsonPath('has_more', false);
});

// ------------------------------------------------------- 4 + 5. creators / follow

test('suggested creators loads and shows who you follow', function () {
    $viewer = apiUser();
    $creator = apiUser();
    Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id]);
    CreatorFollower::create(['follower_id' => $viewer->id, 'creator_id' => $creator->id]);
    Sanctum::actingAs($viewer);

    $this->getJson('/api/regular-user/vibes/suggested-creators')->assertOk()
        ->assertJsonPath('suggested_creators.0.user_id', $creator->id)
        ->assertJsonPath('suggested_creators.0.is_following', true);
});

test('following a creator does not make you friends, and toggles off', function () {
    $viewer = apiUser();
    $creator = apiUser();
    Sanctum::actingAs($viewer);

    $this->postJson("/api/regular-user/creators/{$creator->id}/toggle-follow")->assertOk()
        ->assertJsonPath('is_following', true)->assertJsonPath('followers_count', 1);

    expect(DB::table('friend_requests')->count())->toBe(0)
        ->and(CreatorFollower::where('follower_id', $viewer->id)->where('creator_id', $creator->id)->exists())->toBeTrue();

    $this->postJson("/api/regular-user/creators/{$creator->id}/toggle-follow")->assertOk()->assertJsonPath('is_following', false);
    expect(CreatorFollower::count())->toBe(0);
});

test('followers-only posts show to followers', function () {
    $viewer = apiUser();
    $creator = apiUser();
    $post = Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id, 'visibility' => 'followers']);
    Sanctum::actingAs($viewer);

    expect(feedIds())->not->toContain($post->id);
    CreatorFollower::create(['follower_id' => $viewer->id, 'creator_id' => $creator->id]);
    expect(feedIds())->toContain($post->id);
});

// ----------------------------------------------------------- 6. reel comments

test('liking a reel comment likes that reel comment', function () {
    $viewer = apiUser();
    $reel = makeReel(apiUser());
    $comment = ReelComment::create(['user_reel_id' => $reel->id, 'user_id' => $viewer->id, 'comment' => 'Nice']);
    Sanctum::actingAs($viewer);

    $this->postJson("/api/regular-user/reel-comments/{$comment->id}/like")->assertOk()
        ->assertJsonPath('is_liked', true)->assertJsonPath('likes_count', 1);

    $this->getJson("/api/regular-user/reels/{$reel->id}/comments")->assertOk()
        ->assertJsonPath('comments.0.likes_count', 1)->assertJsonPath('comments.0.is_liked', true);
});

// ---------------------------------------------------------------- 7. repost

test('repost credits the original and can’t be repeated or used on your own post', function () {
    $viewer = apiUser();
    $creator = apiUser(['first_name' => 'Island', 'last_name' => 'Vibez']);
    $post = Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id]);
    $own = Vibe::factory()->create(['created_by' => $viewer->id, 'publisher_id' => $viewer->id]);
    $private = Vibe::factory()->create(['created_by' => $creator->id, 'publisher_id' => $creator->id, 'visibility' => 'private']);
    Sanctum::actingAs($viewer);

    $this->postJson("/api/regular-user/vibes/{$post->id}/repost")->assertOk();
    $this->assertDatabaseHas('vibes', ['created_by' => $viewer->id, 'reposted_from_id' => $post->id, 'reposted_from_name' => 'Island Vibez']);

    $this->postJson("/api/regular-user/vibes/{$post->id}/repost")->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$own->id}/repost")->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$private->id}/repost")->assertNotFound();
});

// ------------------------------------------------------------ reel uploads

test('reel upload accepts a real photo and refuses other file types', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $user = apiUser();
    Sanctum::actingAs($user);

    $this->post('/api/regular-user/reels/store', [
        'type' => 'image',
        'file' => Illuminate\Http\UploadedFile::fake()->image('photo.jpg', 400, 600),
    ], ['Accept' => 'application/json'])->assertCreated();

    $this->post('/api/regular-user/reels/store', [
        'type' => 'gallery',
        'file' => Illuminate\Http\UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hi";'),
    ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('file');

    expect(UserReel::count())->toBe(1);
});

test('deleting a reel deletes its file', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $user = apiUser();
    Illuminate\Support\Facades\Storage::disk('public')->put('reels/images/mine.jpg', 'x');
    $reel = UserReel::create(['uid' => (string) Str::uuid(), 'user_id' => $user->id, 'type' => 'image', 'file_path' => 'reels/images/mine.jpg', 'status' => 'active']);
    Sanctum::actingAs($user);

    $this->deleteJson("/api/regular-user/reels/{$reel->id}")->assertOk();

    Illuminate\Support\Facades\Storage::disk('public')->assertMissing('reels/images/mine.jpg');
});
