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
