<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReel;
use App\Domain\Reels\Actions\CreateReelAction;
use App\Domain\Reels\DTOs\CreateReelData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $reels = UserReel::active()
            ->with('user:id,name,avatar,linkup_id,city,country')
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($reel) use ($user) {
                $isLiked = $reel->likes()->where('user_id', $user->id)->exists();
                $isSaved = $reel->saves()->where('user_id', $user->id)->exists();

                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'user_id' => $reel->user_id,
                    'handle' => $reel->user_id === $user->id ? 'Your Reel' : ($reel->user?->linkup_id ?? $reel->user?->name ?? 'User'),
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
                    'is_liked' => $isLiked,
                    'is_saved' => $isSaved,
                    'created_at' => $reel->created_at,
                ];
            });

        return response()->json($reels);
    }

    public function store(Request $request, CreateReelAction $action): JsonResponse
    {
        // Only real photos and videos (checked from the file's content, not its name).
        // Without this, any file type — including scripts — could be stored in public storage.
        $images = 'image/jpeg,image/png,image/webp,image/gif';
        $videos = 'video/mp4,video/quicktime,video/webm';
        $request->validate([
            'type' => 'required|in:video,image,gallery',
            'file' => [
                'required',
                'file',
                'max:51200', // 50MB max
                'mimetypes:'.match ($request->input('type')) {
                    'video' => $videos,
                    'image' => $images,
                    default => $images.','.$videos,
                },
            ],
            'caption' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        $reel = $action->execute(auth()->user(), CreateReelData::fromRequest($request));

        return response()->json([
            'success' => true,
            'reel' => $reel,
        ], 201);
    }

    public function getFollowedReels(Request $request): JsonResponse
    {
        $user = auth()->user();
        $reels = UserReel::active()
            ->fromFollowedUsers($user->id)
            ->with('user:id,name,avatar,linkup_id,city,country')
            ->latest()
            ->get()
            ->map(function ($reel) use ($user) {
                $isLiked = $reel->likes()->where('user_id', $user->id)->exists();
                $isSaved = $reel->saves()->where('user_id', $user->id)->exists();

                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'user_id' => $reel->user_id,
                    'handle' => $reel->user_id === $user->id ? 'Your Reel' : $reel->user->linkup_id,
                    'avatar' => $reel->user->avatar,
                    'name' => $reel->user->name,
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
                    'is_liked' => $isLiked,
                    'is_saved' => $isSaved,
                    'created_at' => $reel->created_at,
                ];
            });

        return response()->json($reels);
    }

    public function getUserReels(User $user): JsonResponse
    {
        $currentUser = auth()->user();

        $reels = UserReel::active()
            ->where('user_id', $user->id)
            ->with('user:id,name,avatar,linkup_id,city,country')
            ->latest()
            ->get()
            ->map(function ($reel) use ($currentUser) {
                $isLiked = $reel->likes()->where('user_id', $currentUser->id)->exists();
                $isSaved = $reel->saves()->where('user_id', $currentUser->id)->exists();

                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'user_id' => $reel->user_id,
                    'handle' => $reel->user_id === $currentUser->id ? 'Your Reel' : $reel->user->linkup_id,
                    'avatar' => $reel->user->avatar,
                    'name' => $reel->user->name,
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
                    'is_liked' => $isLiked,
                    'is_saved' => $isSaved,
                    'allow_coin_gifts' => (bool) ($reel->allow_coin_gifts ?? true),
                    'created_at' => $reel->created_at,
                ];
            });

        $isFollowing = \App\Models\CreatorFollower::where('follower_id', $currentUser->id)->where('creator_id', $user->id)->exists();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'linkup_id' => $user->linkup_id,
                'city' => $user->city,
                'country' => $user->country,
                'is_following' => $isFollowing,
            ],
            'reels' => $reels,
        ]);
    }

    public function show(UserReel $reel): JsonResponse
    {
        $reel->load('user:id,name,avatar,linkup_id,city,country');
        $currentUser = auth()->user();

        // Check if current user has liked this reel
        $isLiked = $reel->likes()->where('user_id', $currentUser->id)->exists();
        $isSaved = $reel->saves()->where('user_id', $currentUser->id)->exists();

        return response()->json([
            'id' => $reel->id,
            'uid' => $reel->uid,
            'user_id' => $reel->user_id,
            'handle' => $reel->user_id === $currentUser->id ? 'Your Reel' : $reel->user->linkup_id,
            'avatar' => $reel->user->avatar,
            'name' => $reel->user->name,
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
            'is_liked' => $isLiked,
            'is_saved' => $isSaved,
            'created_at' => $reel->created_at,
        ]);
    }

    public function destroy(UserReel $reel): JsonResponse
    {
        abort_unless($reel->user_id === auth()->id(), 403, 'Unauthorized');

        // Delete the files. The model turns paths into full URLs, so use the stored values.
        foreach (['file_path', 'thumbnail_path'] as $column) {
            $path = $reel->getRawOriginal($column);
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $reel->delete();

        return response()->json(['success' => true]);
    }

    private function generateVideoThumbnail($file): ?string
    {
        // This would require FFmpeg to be installed on the server
        // For now, return null - this can be implemented later
        return null;
    }
}
