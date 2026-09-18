<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReelController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:video,image,gallery',
            'file' => 'required|file|max:51200', // 50MB max
            'caption' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        // Handle file upload
        $file = $request->file('file');
        $type = $request->type;

        if ($type === 'video') {
            $path = $file->store('reels/videos', 'public');
            $thumbnailPath = null;

            // Generate thumbnail for video (if FFmpeg is available)
            // For now, we'll use the first frame or a placeholder
            // $thumbnailPath = $this->generateVideoThumbnail($file);

        } elseif ($type === 'image') {
            $path = $file->store('reels/images', 'public');
            $thumbnailPath = null;
        } else {
            $path = $file->store('reels/gallery', 'public');
            $thumbnailPath = null;
        }

        $reel = UserReel::create([
            'uid' => Str::uuid(),
            'user_id' => $user->id,
            'type' => $type,
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'caption' => $request->caption,
            'location' => $request->location,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'reel' => $reel->load('user:id,name,avatar,linkup_id,city,country'),
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
            ->map(function ($reel) {
                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'user_id' => $reel->user_id,
                    'handle' => $reel->user->linkup_id,
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
                    'created_at' => $reel->created_at,
                ];
            });

        return response()->json($reels);
    }

    public function getUserReels(User $user): JsonResponse
    {
        $reels = UserReel::active()
            ->where('user_id', $user->id)
            ->with('user:id,name,avatar,linkup_id,city,country')
            ->latest()
            ->get()
            ->map(function ($reel) {
                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'user_id' => $reel->user_id,
                    'handle' => $reel->user->linkup_id,
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
                    'created_at' => $reel->created_at,
                ];
            });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'linkup_id' => $user->linkup_id,
                'city' => $user->city,
                'country' => $user->country,
            ],
            'reels' => $reels,
        ]);
    }

    public function show(UserReel $reel): JsonResponse
    {
        $reel->load('user:id,name,avatar,linkup_id,city,country');

        return response()->json([
            'id' => $reel->id,
            'uid' => $reel->uid,
            'user_id' => $reel->user_id,
            'handle' => $reel->user->linkup_id,
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
            'created_at' => $reel->created_at,
        ]);
    }

    public function destroy(UserReel $reel): JsonResponse
    {
        abort_unless($reel->user_id === auth()->id(), 403, 'Unauthorized');

        // Delete file from storage
        if ($reel->file_path) {
            Storage::disk('public')->delete(str_replace('storage/', '', $reel->file_path));
        }

        if ($reel->thumbnail_path) {
            Storage::disk('public')->delete(str_replace('storage/', '', $reel->thumbnail_path));
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
