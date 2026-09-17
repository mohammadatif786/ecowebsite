<?php

namespace App\Domain\Vibes\Actions;

use App\Domain\Vibes\DTOs\CreateVibeData;
use App\Domain\Vibes\Enums\VibeStatus;
use App\Jobs\ProcessVibeMediaJob;
use App\Models\LinkUpEvent;
use App\Models\Product;
use App\Models\User;
use App\Models\Vibe;
use App\Policies\VibePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CreateVibeAction
{
    public function __construct(private readonly UploadVibeMediaAction $media, private readonly VibePolicy $policy) {}

    public function execute(User $user, CreateVibeData $data): Vibe
    {
        if (! $this->policy->publishAs($user, $data->publisherType, $data->publisherId)) {
            throw new AuthorizationException('You are not allowed to publish as this account.');
        }

        if (Product::query()->whereIn('id', $data->productIds)->where('status', true)->count() !== count(array_unique($data->productIds))) {
            throw ValidationException::withMessages(['product_ids' => 'One or more products cannot be tagged.']);
        }

        if (LinkUpEvent::query()->whereIn('id', $data->eventIds)->count() !== count(array_unique($data->eventIds))) {
            throw ValidationException::withMessages(['event_ids' => 'One or more events cannot be tagged.']);
        }

        $stored = [];
        try {
            $vibe = DB::transaction(function () use ($user, $data, &$stored) {
                $vibe = Vibe::create([
                    'created_by' => $user->id,
                    'publisher_type' => $data->publisherType->value,
                    'publisher_id' => $data->publisherId,
                    'caption' => $data->caption,
                    'location_name' => $data->locationName,
                    'location_place_id' => $data->locationPlaceId,
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                    'allow_coin_gifts' => $data->allowCoinGifts,
                    'visibility' => $data->visibility,
                    'status' => VibeStatus::Published,
                    'published_at' => now(),
                ]);

                foreach ($data->media as $order => $file) {
                    $record = $this->media->execute($vibe, $file, $order, $data->mediaSources[$order] ?? null);
                    $stored[] = [$record->disk, $record->path];
                }

                $vibe->products()->sync($data->productIds);
                $vibe->events()->sync($data->eventIds);

                // Extract and save hashtags
                if ($data->caption) {
                    preg_match_all('/#(\w+)/', $data->caption, $matches);
                    if (!empty($matches[1])) {
                        $tags = collect($matches[1])->map(fn($tag) => ['tag' => strtolower($tag), 'created_at' => now(), 'updated_at' => now()])->toArray();
                        DB::table('vibe_hashtags')->insert(
                            collect($tags)->map(fn($t) => array_merge($t, ['vibe_id' => $vibe->id]))->toArray()
                        );
                    }
                }

                return $vibe;
            });
        } catch (\Throwable $exception) {
            foreach ($stored as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }
            throw $exception;
        }

        $vibe->media()->each(fn ($media) => ProcessVibeMediaJob::dispatch($media->id)->afterCommit());

        return $vibe->load(['creator', 'publisher', 'media', 'products', 'events']);
    }
}
