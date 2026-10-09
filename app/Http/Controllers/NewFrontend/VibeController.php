<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Vibes\Actions\CreateVibeAction;
use App\Domain\Vibes\DTOs\CreateVibeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vibes\StoreVibeRequest;
use App\Http\Resources\VibeResource;
use App\Models\Vibe;
use Illuminate\Http\JsonResponse;

class VibeController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();

        $vibes = Vibe::with(['creator', 'publisher', 'media', 'products.user', 'events.user', 'events.tickets'])
            ->where('status', \App\Domain\Vibes\Enums\VibeStatus::Published)
            ->latest()
            ->take(20)
            ->get();

        $formattedVibes = $vibes->map(function ($vibe) use ($user) {
            $tag = null;

            if ($vibe->products->isNotEmpty()) {
                $p = $vibe->products->first();
                $tag = [
                    'kind' => 'product',
                    'vibe_id' => $vibe->id,
                    'affiliate_user_id' => $vibe->created_by,
                    'id' => $p->id,
                    'title' => $p->name,
                    'price' => $p->price,
                    'seller' => $p->user?->name ?? $p->seller?->name,
                    'image' => $p->image_url,
                ];
            } elseif ($vibe->events->isNotEmpty()) {
                $e = $vibe->events->first();
                $tag = [
                    'kind' => 'event',
                    'vibe_id' => $vibe->id,
                    'affiliate_user_id' => $vibe->created_by,
                    'id' => $e->id,
                    'title' => $e->title,
                    'price' => $e->tickets->min('price') ?? 0,
                    'date' => $e->start_date?->format('Y-m-d'),
                    'location' => $e->venue,
                    'seller' => $e->user?->name,
                    'image' => $e->image_url,
                ];
            }

            $mediaItems = $vibe->media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'type' => $m->media_type->value,
                    'url' => $m->disk === 'public' ? asset('storage/' . $m->path) : \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->path),
                    'thumbnail' => $m->thumbnail_path ? ($m->disk === 'public' ? asset('storage/' . $m->thumbnail_path) : \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->thumbnail_path)) : null,
                ];
            });

            $isReel = $vibe->media->contains(fn ($m) => $m->media_type->value === 'video');

            $publisher = $vibe->publisher;
            $publisherName = 'User';
            $publisherType = 'user';
            $publisherAvatar = $vibe->creator?->avatar ?? 'https://i.pravatar.cc/120?img=1';

            if ($publisher instanceof \App\Models\User) {
                $publisherName = $publisher->name;
                $publisherType = 'user';
                $publisherAvatar = $publisher->avatar;
            } elseif ($publisher instanceof \App\Models\OrganizerProfile) {
                $publisherName = $publisher->organizer_name;
                $publisherType = 'organization';
            } elseif ($publisher instanceof \App\Models\ClubFete) {
                $publisherName = $publisher->name;
                $publisherType = 'group';
            } elseif ($publisher instanceof \App\Models\UserCustomPublisher) {
                $publisherName = $publisher->name;
                $publisherType = $publisher->type;
            }

            return [
                'id' => $vibe->id,
                'created_by' => $vibe->created_by,
                'handle' => $publisherName,
                'publisher_type' => $publisherType,
                'avatar' => $publisherAvatar,
                'location' => $vibe->location_name,
                'media' => $mediaItems,
                'kind' => $isReel ? 'reel' : (!empty($vibe->text_bg) && $mediaItems->isEmpty() ? 'text' : 'photo'),
                'caption' => $vibe->caption,
                'text_bg' => $vibe->text_bg,
                'text_font' => $vibe->text_font,
                'likes_count' => $vibe->likes_count,
                'comments_count' => $vibe->comments_count,
                'shares_count' => $vibe->shares_count,
                'bigups_count' => $vibe->bigups_count,
                'is_liked' => $user ? $vibe->likes()->where('user_id', $user->id)->exists() : false,
                'allow_coin_gifts' => (bool) $vibe->allow_coin_gifts,
                'bigup' => $vibe->bigups_count,
                'shoppable' => $tag !== null,
                'tag' => $tag,
            ];
        });

        return response()->json([
            'posts' => $formattedVibes,
        ]);
    }

    public function store(StoreVibeRequest $request, CreateVibeAction $action): JsonResponse
    {
        $vibe = $action->execute($request->user(), CreateVibeData::fromRequest($request));

        return (new VibeResource($vibe))->response()->setStatusCode(201);
    }

    public function destroy(Vibe $vibe): JsonResponse
    {
        abort_unless($vibe->created_by === auth()->id(), 403, 'Unauthorized');

        $vibe->delete();

        return response()->json(['success' => true]);
    }

    public function toggleFollowCreator(\App\Models\User $user): JsonResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        $friendRequest = \App\Models\Frontend\FriendRequest::where(function ($q) use ($currentUser, $user) {
            $q->where('user_id', $currentUser->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($currentUser, $user) {
            $q->where('user_id', $user->id)->where('receiver_id', $currentUser->id);
        })->first();

        if ($friendRequest) {
            if ($friendRequest->status == 1) {
                $friendRequest->delete();
                $isFollowing = false;
            } else {
                $friendRequest->update(['status' => 1]);
                $isFollowing = true;
            }
        } else {
            \App\Models\Frontend\FriendRequest::create([
                'uid' => \Illuminate\Support\Str::uuid(),
                'user_id' => $currentUser->id,
                'receiver_id' => $user->id,
                'status' => 1,
                'type' => 0,
            ]);
            $isFollowing = true;
        }

        $userReels = [];
        if ($isFollowing) {
            $userReels = \App\Models\UserReel::active()
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(function ($reel) use ($currentUser) {
                    return [
                        'id' => $reel->id,
                        'uid' => $reel->uid,
                        'user_id' => $reel->user_id,
                        'handle' => $reel->user_id === $currentUser->id ? 'Your Reel' : ($reel->user?->linkup_id ?? $reel->user?->name ?? 'User'),
                        'avatar' => $reel->user?->avatar,
                        'name' => $reel->user?->name ?? 'User',
                        'type' => $reel->type,
                        'file_path' => $reel->file_path,
                        'thumbnail_path' => $reel->thumbnail_path,
                        'caption' => $reel->caption,
                        'location' => $reel->location,
                        'likes_count' => $reel->likes_count,
                        'comments_count' => $reel->comments_count,
                        'shares_count' => $reel->shares_count,
                        'gifts_count' => $reel->gifts_count,
                        'bigups_count' => $reel->bigups_count,
                        'is_liked' => $reel->likes()->where('user_id', $currentUser->id)->exists(),
                        'created_at' => $reel->created_at?->toISOString(),
                    ];
                });
        }

        return response()->json([
            'is_following' => $isFollowing,
            'message' => $isFollowing ? 'Creator followed' : 'Creator unfollowed',
            'reels' => $userReels,
        ]);
    }
}
