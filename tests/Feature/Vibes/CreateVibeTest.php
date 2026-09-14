<?php

use App\Domain\Vibes\Actions\CreateVibeAction;
use App\Domain\Vibes\Actions\UploadVibeMediaAction;
use App\Domain\Vibes\DTOs\CreateVibeData;
use App\Domain\Vibes\Enums\VibeMediaSource;
use App\Domain\Vibes\Enums\VibePublisherType;
use App\Domain\Vibes\Enums\VibeVisibility;
use App\Http\Middleware\CheckWizardCompleted;
use App\Http\Middleware\EnsureOtpVerified;
use App\Jobs\ProcessVibeMediaJob;
use App\Models\ClubFete;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Policies\VibePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([EnsureOtpVerified::class, CheckWizardCompleted::class]);
    Storage::fake('public');
    Queue::fake();
});

function vibePayload(User $user, array $overrides = []): array
{
    return array_merge([
        'publisher_type' => 'user',
        'publisher_id' => $user->id,
        'caption' => 'Island energy #linkup',
        'visibility' => 'public',
        'allow_coin_gifts' => true,
    ], $overrides);
}

test('guest cannot create a vibe', function () {
    $this->postJson(route('new_frontend.vibes.store'), [])->assertUnauthorized();
});

test('authenticated user can publish a caption as themself', function () {
    $user = User::factory()->create(['status' => true, 'is_active' => true]);

    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user))
        ->assertCreated()->assertJsonPath('data.publisher.type', 'user')
        ->assertJsonPath('data.status', 'published')->assertJsonPath('data.allow_coin_gifts', true);

    $this->assertDatabaseHas('vibes', ['created_by' => $user->id, 'publisher_type' => 'user', 'publisher_id' => $user->id]);
});

test('empty vibe is rejected', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user, ['caption' => '']))
        ->assertUnprocessable()->assertJsonValidationErrors('caption');
});

test('user can publish as their organization but not another organization', function () {
    $user = User::factory()->create();
    $own = OrganizerProfile::create(['user_id' => $user->id, 'organizer_name' => 'Own Org']);
    $other = OrganizerProfile::create(['user_id' => User::factory()->create()->id, 'organizer_name' => 'Other Org']);

    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user, ['publisher_type' => 'organization', 'publisher_id' => $own->id]))->assertCreated();
    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user, ['publisher_type' => 'organization', 'publisher_id' => $other->id]))->assertForbidden();
});

test('only active group owners and admins can publish as a group', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = ClubFete::create(['name' => 'Soca Crew', 'status' => true]);
    $group->members()->attach($owner, ['role' => 'owner', 'is_active' => true]);
    $group->members()->attach($member, ['role' => 'member', 'is_active' => true]);

    $payload = vibePayload($owner, ['publisher_type' => 'group', 'publisher_id' => $group->id]);
    $this->actingAs($owner)->postJson(route('new_frontend.vibes.store'), $payload)->assertCreated();
    $this->actingAs($member)->postJson(route('new_frontend.vibes.store'), $payload)->assertForbidden();
});

test('multiple images are stored in order and processing is queued', function () {
    $user = User::factory()->create();
    $payload = vibePayload($user, ['media' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.png')], 'media_sources' => ['camera', 'gallery']]);

    $response = $this->actingAs($user)->post(route('new_frontend.vibes.store'), $payload, ['Accept' => 'application/json'])->assertCreated();
    expect($response->json('data.media'))->toHaveCount(2)
        ->and($response->json('data.media.0.sort_order'))->toBe(0)
        ->and($response->json('data.media.1.sort_order'))->toBe(1);
    Queue::assertPushed(ProcessVibeMediaJob::class, 2);
});

test('video uploads and location and gift preference are persisted', function () {
    $user = User::factory()->create();
    $video = UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4');

    $this->actingAs($user)->post(route('new_frontend.vibes.store'), vibePayload($user, [
        'media' => [$video], 'media_sources' => ['video_recording'], 'location_name' => 'Port of Spain',
        'latitude' => 10.6596, 'longitude' => -61.5086, 'allow_coin_gifts' => false,
    ]), ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.media.0.type', 'video');

    $this->assertDatabaseHas('vibes', ['location_name' => 'Port of Spain', 'allow_coin_gifts' => false]);
});

test('unsafe and oversized media are rejected', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('new_frontend.vibes.store'), vibePayload($user, ['media' => [UploadedFile::fake()->create('payload.php', 1, 'application/x-php')]]), ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('media.0');
    $this->actingAs($user)->post(route('new_frontend.vibes.store'), vibePayload($user, ['media' => [UploadedFile::fake()->create('huge.mp4', config('vibes.max_video_kb') + 1, 'video/mp4')]]), ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('media.0');
});

test('duplicate attachment identifiers are rejected', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user, ['product_ids' => [1, 1]]))
        ->assertUnprocessable()->assertJsonValidationErrors(['product_ids.0', 'product_ids.1']);
});

test('suspended user cannot publish and no vibe is created', function () {
    $user = User::factory()->create(['status' => false]);
    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user))->assertForbidden();
    $this->assertDatabaseCount('vibes', 0);
});

test('active products and events can be tagged', function () {
    $user = User::factory()->create();
    $category = ProductCategory::create(['name' => 'Costumes', 'status' => true]);
    $product = Product::create(['user_id' => $user->id, 'name' => 'Feather Crown', 'product_category_id' => $category->id, 'images' => [], 'status' => true]);
    $event = LinkUpEvent::create(['title' => 'Carnival Friday', 'user_id' => $user->id]);

    $this->actingAs($user)->postJson(route('new_frontend.vibes.store'), vibePayload($user, [
        'product_ids' => [$product->id], 'event_ids' => [$event->id],
    ]))->assertCreated()->assertJsonPath('data.products.0.id', $product->id)
        ->assertJsonPath('data.events.0.id', $event->id);
});

test('database and files roll back when media creation fails', function () {
    $user = User::factory()->create();
    $uploader = new class extends UploadVibeMediaAction
    {
        private int $calls = 0;

        public function execute(\App\Models\Vibe $vibe, UploadedFile $file, int $order, ?string $source = null): \App\Models\VibeMedia
        {
            if (++$this->calls === 2) {
                throw new RuntimeException('Simulated failure');
            }

            return parent::execute($vibe, $file, $order, $source);
        }
    };
    $data = new CreateVibeData(VibePublisherType::User, $user->id, 'Rollback', null, null, null, null, true, VibeVisibility::Public,
        [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')], [VibeMediaSource::Gallery->value, VibeMediaSource::Gallery->value], [], []);

    expect(fn () => (new CreateVibeAction($uploader, new VibePolicy))->execute($user, $data))->toThrow(RuntimeException::class);
    $this->assertDatabaseCount('vibes', 0);
    expect(Storage::disk('public')->allFiles('vibes'))->toBeEmpty();
});
