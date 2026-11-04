<?php

use App\Http\Middleware\CheckWizardCompleted;
use App\Http\Middleware\EnsureOtpVerified;
use App\Models\BlockedUser;
use App\Models\CreatorFollower;
use App\Models\LinkUpEvent;
use App\Models\MarketplaceAffiliateEarning;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vibe;
use App\Models\VibeMessage;
use App\Services\VibeTicketCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use O21\LaravelWallet\Models\Custodian;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([EnsureOtpVerified::class, CheckWizardCompleted::class]);
});

function vcUser(array $attributes = []): User
{
    return User::factory()->create(array_merge(['status' => true, 'is_active' => true, 'coins' => 0], $attributes));
}

function vcVibe(User $owner, array $attributes = []): Vibe
{
    return Vibe::factory()->create(array_merge(['created_by' => $owner->id, 'publisher_id' => $owner->id], $attributes));
}

function vcProduct(User $seller, array $attributes = []): Product
{
    $categoryId = DB::table('product_categories')->insertGetId(['name' => 'General', 'status' => 1, 'is_featured' => 0, 'created_at' => now(), 'updated_at' => now()]);

    return Product::create(array_merge([
        'user_id' => $seller->id, 'name' => 'Carnival Earrings', 'product_category_id' => $categoryId, 'images' => [],
        'qty' => 5, 'price' => 20, 'status' => true, 'collect_tax' => false, 'commMode' => 'pct', 'commission' => 10, 'commFlat' => 0,
    ], $attributes));
}

function vcEvent(User $organizer, array $attributes = []): LinkUpEvent
{
    return LinkUpEvent::create(array_merge(['user_id' => $organizer->id, 'title' => 'Soca Fete', 'venue' => 'Queens Park', 'status' => 'active'], $attributes));
}

function vcTag(Vibe $vibe, Product|LinkUpEvent $item): void
{
    DB::table('vibe_attachments')->insert([
        'vibe_id' => $vibe->id, 'attachable_type' => $item instanceof Product ? 'product' : 'event', 'attachable_id' => $item->id,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function vcFund(User $user, float $amount): void
{
    deposit($amount, 'USD')->from(Custodian::of('e_money'))->to($user)->overcharge()->commit();
}

function vcBlock(User $a, User $b): void
{
    BlockedUser::create(['user_id' => $a->id, 'blocked_user_id' => $b->id, 'is_blocked' => true]);
}

// ---------------------------------------------------------------- single post, hide, report

test('a single post loads in the feed shape, and not when it is private or hidden', function () {
    $viewer = vcUser();
    $author = vcUser();
    $post = vcVibe($author, ['caption' => 'Fete tonight #soca']);
    $private = vcVibe($author, ['visibility' => 'private']);
    Sanctum::actingAs($viewer);

    $this->getJson("/api/regular-user/vibes/{$post->id}")->assertOk()
        ->assertJsonPath('post.id', $post->id)->assertJsonPath('post.caption', 'Fete tonight #soca')->assertJsonPath('post.is_liked', false);
    $this->getJson("/api/regular-user/vibes/{$private->id}")->assertNotFound();

    $this->postJson("/api/regular-user/vibes/{$post->id}/hide")->assertOk();
    $this->getJson("/api/regular-user/vibes/{$post->id}")->assertNotFound();
    expect(collect($this->getJson('/api/regular-user/vibes/posts')->json('posts'))->pluck('id'))->not->toContain($post->id);
});

test('you can’t hide your own post, and reporting is once per post with a known reason', function () {
    $viewer = vcUser();
    $author = vcUser();
    $own = vcVibe($viewer);
    $post = vcVibe($author);
    Sanctum::actingAs($viewer);

    $this->postJson("/api/regular-user/vibes/{$own->id}/hide")->assertUnprocessable();
    $this->getJson('/api/regular-user/vibes/report-reasons')->assertOk()->assertJsonPath('reasons.0.key', 'nudity');

    $this->postJson("/api/regular-user/vibes/{$post->id}/report", ['reason' => 'made-up'])->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$post->id}/report", ['reason' => 'other'])->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$post->id}/report", ['reason' => 'scam'])->assertOk()->assertJsonPath('already', false);
    $this->postJson("/api/regular-user/vibes/{$post->id}/report", ['reason' => 'scam'])->assertOk()->assertJsonPath('already', true);

    expect(DB::table('uvibe_post_reports')->where('vibe_id', $post->id)->count())->toBe(1);
});

// ---------------------------------------------------------------- tags, search, creator profile

test('trending tags count visible posts and the feed filters by tag', function () {
    $viewer = vcUser();
    $author = vcUser();
    $soca = vcVibe($author);
    $food = vcVibe($author);
    $private = vcVibe($author, ['visibility' => 'private']);
    foreach ([[$soca, 'soca'], [$food, 'food'], [$private, 'soca'], [$private, 'secret']] as [$v, $tag]) {
        DB::table('vibe_hashtags')->insert(['vibe_id' => $v->id, 'tag' => $tag, 'created_at' => now(), 'updated_at' => now()]);
    }
    Sanctum::actingAs($viewer);

    $tags = collect($this->getJson('/api/regular-user/vibes/trending-tags')->assertOk()->json('tags'));
    expect($tags->pluck('tag')->all())->toContain('#soca', '#food')->not->toContain('#secret')
        ->and($tags->firstWhere('tag', '#soca')['count'])->toBe(1);

    $ids = collect($this->getJson('/api/regular-user/vibes/posts?tag=%23soca')->assertOk()->json('posts'))->pluck('id')->all();
    expect($ids)->toBe([$soca->id]);
});

test('search finds people by name or handle, never blocked accounts, and suggests tags', function () {
    $viewer = vcUser();
    $gabi = vcUser(['name' => 'Gabi Island', 'linkup_id' => 'gabis']);
    $blocked = vcUser(['name' => 'Gabi Blocked', 'linkup_id' => 'gabiblock']);
    vcBlock($blocked, $viewer);
    vcVibe($gabi);
    DB::table('vibe_hashtags')->insert(['vibe_id' => vcVibe($gabi)->id, 'tag' => 'gabifete', 'created_at' => now(), 'updated_at' => now()]);
    CreatorFollower::create(['follower_id' => $viewer->id, 'creator_id' => $gabi->id]);
    Sanctum::actingAs($viewer);

    $res = $this->getJson('/api/regular-user/vibes/search?q=@gabi')->assertOk();
    $people = collect($res->json('people'));
    expect($people->pluck('user_id')->all())->toBe([$gabi->id])
        ->and($people->first()['is_following'])->toBeTrue()
        ->and($people->first()['followers'])->toBe(1)
        ->and(collect($res->json('tags'))->pluck('tag')->all())->toBe(['#gabifete']);
});

test('creator profile has stats, mutuals, top supporters, shop and posts', function () {
    $viewer = vcUser();
    $creator = vcUser(['name' => 'Island Vibez', 'linkup_id' => 'islandvibez']);
    $friend = vcUser(['first_name' => 'Paul', 'last_name' => 'Notch']);
    $fan = vcUser(['first_name' => 'Big', 'last_name' => 'Fan']);
    $post = vcVibe($creator, ['likes_count' => 7]);
    vcVibe($creator, ['visibility' => 'private']);
    CreatorFollower::create(['follower_id' => $viewer->id, 'creator_id' => $friend->id]);
    CreatorFollower::create(['follower_id' => $friend->id, 'creator_id' => $creator->id]);
    DB::table('gift_coins')->insert(['sender_id' => $fan->id, 'recieved_id' => $creator->id, 'name' => 'Big Up', 'coins' => 50, 'vibe_id' => $post->id, 'created_at' => now(), 'updated_at' => now()]);
    vcTag($post, vcProduct($creator));
    vcTag(vcVibe($creator, ['visibility' => 'private']), vcProduct($creator, ['name' => 'Hidden']));
    Sanctum::actingAs($viewer);

    $res = $this->getJson("/api/regular-user/vibes/creators/{$creator->id}")->assertOk();
    $res->assertJsonPath('creator.handle', 'islandvibez')
        ->assertJsonPath('creator.is_following', false)
        ->assertJsonPath('creator.linkup_status', 'none')
        ->assertJsonPath('stats.posts', 1)
        ->assertJsonPath('stats.followers', 1)
        ->assertJsonPath('stats.likes', 7)
        ->assertJsonPath('stats.bigup_coins', 50)
        ->assertJsonPath('mutuals.count', 1)
        ->assertJsonPath('mutuals.people.0.name', 'Paul Notch')
        ->assertJsonPath('top_supporters.0.name', 'Big Fan')
        ->assertJsonPath('top_supporters.0.coins', 50);
    expect($res->json('shop'))->toHaveCount(1)->and($res->json('shop.0.vibe_id'))->toBe($post->id)
        ->and($res->json('posts'))->toHaveCount(1);

    vcBlock($creator, $viewer);
    $this->getJson("/api/regular-user/vibes/creators/{$creator->id}")->assertNotFound();
});

// ---------------------------------------------------------------- composer: publishers and tagging

test('composer lists who you can post as, adds an organization, and lists taggable items', function () {
    $user = vcUser();
    $seller = vcUser();
    $product = vcProduct($seller, ['name' => 'Soca Tee']);
    vcProduct($seller, ['name' => 'Gone', 'qty' => 0]);
    Sanctum::actingAs($user);

    $this->postJson('/api/regular-user/vibes/custom-publishers', ['name' => 'UWI Soca Club', 'type' => 'group'])
        ->assertCreated()->assertJsonPath('publisher_type', 'custom_publisher');
    $this->postJson('/api/regular-user/vibes/custom-publishers', ['name' => 'UWI Soca Club', 'type' => 'group'])->assertOk();
    $this->getJson('/api/regular-user/vibes/publishers')->assertOk()
        ->assertJsonCount(1, 'custom')->assertJsonPath('custom.0.name', 'UWI Soca Club');

    $items = $this->getJson('/api/regular-user/vibes/taggable?kind=product&q=soca')->assertOk()->json('items');
    expect(collect($items)->pluck('id')->all())->toBe([$product->id])
        ->and($items[0]['commission'])->toBe('10% commission');
});

// ---------------------------------------------------------------- buying the tagged item

test('buying a tagged item is priced by the server, places an order and pays the poster', function () {
    $buyer = vcUser();
    $poster = vcUser();
    $seller = vcUser();
    $product = vcProduct($seller, ['price' => 20, 'qty' => 2]);
    $vibe = vcVibe($poster);
    vcTag($vibe, $product);
    vcFund($buyer, 100);
    Sanctum::actingAs($buyer);

    $quote = $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy/quote", ['product_id' => $product->id, 'shipping_method' => 'pickup'])
        ->assertOk()->assertJsonPath('can_afford', true)->json();
    expect($quote['total'])->toEqual(20.0);

    // A changed price is caught before charging.
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy", ['product_id' => $product->id, 'shipping_method' => 'pickup', 'expected_total' => 1])
        ->assertUnprocessable();
    // Delivery needs an address.
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy", ['product_id' => $product->id, 'shipping_method' => 'standard'])
        ->assertUnprocessable()->assertJsonValidationErrors('address');

    $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy", ['product_id' => $product->id, 'shipping_method' => 'pickup', 'expected_total' => 20])
        ->assertCreated()->assertJsonPath('wallet_balance', 80);

    $order = DB::table('orders')->where('user_id', $buyer->id)->first();
    $earning = MarketplaceAffiliateEarning::first();
    expect((float) $order->net_total)->toBe(20.0)
        ->and($product->fresh()->qty)->toBe(1)
        ->and($earning->affiliate_user_id)->toBe($poster->id)
        ->and((float) $earning->commission_amount)->toBe(2.0)
        ->and($earning->vibe_id)->toBe($vibe->id)
        ->and($earning->summaryRow()['source'])->toBe('vibe')
        ->and($earning->summaryRow()['title'])->toBe('Carnival Earrings');
});

test('you can only buy what the post tags, not your own item, and not without the money', function () {
    $buyer = vcUser();
    $seller = vcUser();
    $tagged = vcProduct($seller);
    $other = vcProduct($seller, ['name' => 'Not tagged']);
    $mine = vcProduct($buyer, ['name' => 'Mine']);
    $vibe = vcVibe($seller);
    vcTag($vibe, $tagged);
    vcTag($vibe, $mine);
    vcFund($buyer, 5);
    Sanctum::actingAs($buyer);

    $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy/quote", ['product_id' => $other->id])->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy/quote", ['product_id' => $mine->id])->assertUnprocessable();
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/buy", ['product_id' => $tagged->id, 'shipping_method' => 'pickup'])
        ->assertUnprocessable()->assertJsonValidationErrors('wallet');

    expect(DB::table('orders')->count())->toBe(0)->and($tagged->fresh()->qty)->toBe(5);
    // The seller posted it, so no one earns a commission on it.
    expect(MarketplaceAffiliateEarning::count())->toBe(0);
});

test('the website’s old purchase call is now a priced order, and events go to the event page', function () {
    $buyer = vcUser();
    $poster = vcUser();
    $product = vcProduct(vcUser(), ['price' => 10]);
    $vibe = vcVibe($poster);
    vcTag($vibe, $product);
    vcFund($buyer, 50);
    $this->actingAs($buyer);

    // A client-sent price is ignored.
    $this->postJson("/new_frontend/vibes/{$vibe->id}/purchase", ['kind' => 'product', 'id' => $product->id, 'price' => 0.01, 'shipping_method' => 'pickup'])
        ->assertCreated()->assertJsonPath('total', 10);
    $this->postJson("/new_frontend/vibes/{$vibe->id}/purchase", ['kind' => 'event', 'id' => 1])->assertUnprocessable();
});

test('tickets bought from a Vibe pay the poster the ticket commission', function () {
    $buyer = vcUser();
    $poster = vcUser();
    $organizer = vcUser();
    $event = vcEvent($organizer);
    $ticket = Ticket::create(['event_id' => $event->id, 'link_up_event_id' => $event->id, 'name' => 'General', 'price' => 30, 'quantity' => 100, 'commMode' => 'flat', 'commission' => 0, 'commFlat' => 3]);
    $vibe = vcVibe($poster);
    vcTag($vibe, $event);
    $untagged = vcVibe($poster);

    $service = app(VibeTicketCommissionService::class);
    $cart = ['items' => [['ticket' => $ticket, 'qty' => 2, 'price' => 30.0]]];

    expect($service->record($untagged->id, $event, $buyer->id, $cart, [['id' => 1]]))->toBeNull()
        ->and($service->record($vibe->id, $event, $poster->id, $cart, [['id' => 1]]))->toBeNull();

    $earning = $service->record($vibe->id, $event, $buyer->id, $cart, [['id' => 1]]);
    expect((float) $earning->commission_amount)->toBe(6.0)
        ->and((float) $earning->sale_amount)->toBe(60.0)
        ->and($earning->affiliate_user_id)->toBe($poster->id)
        ->and($earning->summaryRow()['kind'])->toBe('ticket');
});

// ---------------------------------------------------------------- send a Vibe (gift)

test('send-gift moves coins once, allows free gifts, and refuses self or blocked', function () {
    $sender = vcUser(['coins' => 100]);
    $friend = vcUser();
    $blocked = vcUser();
    vcBlock($blocked, $sender);
    Sanctum::actingAs($sender);

    $this->postJson('/api/regular-user/send-gift', ['receiver_id' => $friend->id, 'gift_name' => 'Bless Up', 'coins' => 60])
        ->assertOk()->assertJsonPath('user_coins', 40);
    Sanctum::actingAs($sender->fresh());
    $this->postJson('/api/regular-user/send-gift', ['receiver_id' => $friend->id, 'gift_name' => 'Bless Up', 'coins' => 60])
        ->assertStatus(402);
    $this->postJson('/api/regular-user/send-gift', ['receiver_id' => $friend->id, 'gift_name' => 'Wave', 'coins' => 0])->assertOk();
    $this->postJson('/api/regular-user/send-gift', ['receiver_id' => $sender->id, 'gift_name' => 'Me', 'coins' => 1])->assertUnprocessable();
    $this->postJson('/api/regular-user/send-gift', ['receiver_id' => $blocked->id, 'gift_name' => 'Hi', 'coins' => 1])->assertUnprocessable();

    expect($sender->fresh()->coins)->toBe(40)->and($friend->fresh()->coins)->toBe(60)
        ->and(DB::table('gift_coins')->count())->toBe(2);
});

// ---------------------------------------------------------------- messages

test('vibe messages respect blocks and track unread', function () {
    $me = vcUser();
    $friend = vcUser();
    $blocked = vcUser();
    VibeMessage::create(['from_user_id' => $friend->id, 'to_user_id' => $me->id, 'content' => 'Hey!', 'type' => 'text']);
    VibeMessage::create(['from_user_id' => $blocked->id, 'to_user_id' => $me->id, 'content' => 'Hi', 'type' => 'text']);
    vcBlock($me, $blocked);
    Sanctum::actingAs($me);

    $convos = $this->getJson('/api/regular-user/vibes/chats/conversations')->assertOk()->json('conversations');
    expect(collect($convos)->pluck('user_id')->all())->toBe([$friend->id])->and($convos[0]['unread'])->toBe(1);

    $this->getJson("/api/regular-user/vibes/chats/{$friend->id}/messages")->assertOk();
    expect($this->getJson('/api/regular-user/vibes/chats/conversations')->json('conversations.0.unread'))->toBe(0);

    $this->postJson("/api/regular-user/vibes/chats/{$blocked->id}/messages", ['content' => 'yo'])->assertForbidden();
    $this->getJson("/api/regular-user/vibes/chats/{$blocked->id}/messages")->assertForbidden();
});

// ---------------------------------------------------------------- activity

test('likes, comments, replies, mentions and follows show in activity — once, and never your own', function () {
    $owner = vcUser(['linkup_id' => 'owner']);
    $fan = vcUser(['name' => 'Fan']);
    $friend = vcUser(['linkup_id' => 'buddy']);
    $vibe = vcVibe($owner);
    $other = vcVibe($friend);

    Sanctum::actingAs($owner);
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/like")->assertOk(); // own like: nothing
    $ownerComment = $this->postJson("/api/regular-user/vibes/{$vibe->id}/comments", ['comment' => 'Thanks all'])->assertCreated()->json('id');

    Sanctum::actingAs($fan);
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/like")->assertOk();
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/like")->assertOk();
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/like")->assertOk(); // re-like: no second notification
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/comments", ['comment' => 'Love it @buddy', 'parent_id' => $ownerComment])->assertCreated();
    $otherComment = $this->postJson("/api/regular-user/vibes/{$other->id}/comments", ['comment' => 'x'])->json('id');
    $this->postJson("/api/regular-user/vibes/{$vibe->id}/comments", ['comment' => 'wrong post', 'parent_id' => $otherComment])->assertUnprocessable();
    $this->postJson("/api/regular-user/creators/{$owner->id}/toggle-follow")->assertOk();

    expect(Notification::where('user_id', $owner->id)->where('context', 'vibe_like')->count())->toBe(1)
        ->and(Notification::where('user_id', $owner->id)->where('context', 'vibe_comment')->count())->toBe(1)
        ->and(Notification::where('user_id', $owner->id)->where('context', 'vibe_reply')->count())->toBe(0)
        ->and(Notification::where('user_id', $friend->id)->where('context', 'vibe_mention')->count())->toBe(1)
        ->and(Notification::where('user_id', $owner->id)->where('context', 'vibe_follow')->count())->toBe(1);

    Notification::create(['title' => 'Item sold', 'message' => 'Order', 'user_id' => $owner->id, 'send_by' => $fan->id, 'type' => 'marketplace_order', 'context' => 'marketplace_orders', 'unread' => true]);

    Sanctum::actingAs($owner);
    $activity = collect($this->getJson('/api/regular-user/vibes/activity')->assertOk()->json('activity'));
    expect($activity->pluck('type')->sort()->values()->all())->toBe(['comment', 'follow', 'like'])
        ->and($activity->firstWhere('type', 'like')['vibe_id'])->toBe($vibe->id)
        ->and($activity->firstWhere('type', 'comment')['comment'])->toBe('Love it @buddy');
});

// ---------------------------------------------------------------- website feed

test('the website feed hides posts you hid and opens a shared post first', function () {
    $viewer = vcUser();
    $author = vcUser();
    $older = vcVibe($author, ['created_at' => now()->subDay(), 'published_at' => now()->subDay()]);
    $hidden = vcVibe($author);
    vcVibe($author);
    DB::table('uvibe_hidden_posts')->insert(['user_id' => $viewer->id, 'vibe_id' => $hidden->id, 'created_at' => now(), 'updated_at' => now()]);
    $this->withoutVite();
    $this->actingAs($viewer);

    $this->get("/new_frontend/vibes?id={$older->id}")->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('vibes.0.id', $older->id)
            ->where('vibes', fn ($vibes) => collect($vibes)->pluck('id')->doesntContain($hidden->id)));
});
