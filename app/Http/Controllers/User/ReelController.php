<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReel;
use App\Support\ReelPresenter;
use App\Domain\Reels\Actions\CreateReelAction;
use App\Domain\Reels\DTOs\CreateReelData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReelController extends Controller
{
    /** How many reels one list returns at most. */
    private const LIST_LIMIT = 100;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $reels = $this->listQuery($user)->take(50)->get();

        return response()->json($reels->map(fn (UserReel $reel) => $this->present($reel, $user)));
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
        app(\App\Services\VibeActivityNotifier::class)->mentioned(auth()->user(), $reel, $reel->caption);

        return response()->json([
            'success' => true,
            'reel' => $reel,
        ], 201);
    }

    public function getFollowedReels(Request $request): JsonResponse
    {
        $user = $request->user();
        $reels = $this->listQuery($user)->fromFollowedUsers($user->id)->take(self::LIST_LIMIT)->get();

        return response()->json($reels->map(fn (UserReel $reel) => $this->present($reel, $user)));
    }

    public function getUserReels(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Nobody sees the reels of someone they blocked, or who blocked them.
        abort_if((int) $user->id !== (int) $currentUser->id && $this->blockedBetween($currentUser->id, $user->id), 404, 'Profile not found.');

        $reels = $this->listQuery($currentUser)->where('user_id', $user->id)->take(self::LIST_LIMIT)->get();

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
            'reels' => $reels->map(fn (UserReel $reel) => $this->present($reel, $currentUser)),
        ]);
    }

    public function show(Request $request, UserReel $reel): JsonResponse
    {
        $currentUser = $request->user();
        abort_unless($reel->isVisibleTo($currentUser), 404, 'Reel not found.');

        $reel = UserReel::query()->whereKey($reel->id)->with('user:id,name,avatar,linkup_id,city,country')
            ->withViewerFlags($currentUser)->firstOrFail();

        return response()->json($this->present($reel, $currentUser));
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

    /** Active reels the viewer may see, newest first, with the creator and the viewer's like/save flags. */
    private function listQuery(User $viewer)
    {
        return ReelPresenter::query($viewer);
    }

    private function blockedBetween(int $a, int $b): bool
    {
        return \App\Models\BlockedUser::query()->where('is_blocked', true)
            ->where(fn ($q) => $q->where(fn ($x) => $x->where('user_id', $a)->where('blocked_user_id', $b))
                ->orWhere(fn ($x) => $x->where('user_id', $b)->where('blocked_user_id', $a)))
            ->exists();
    }

    /** One reel as every list and the viewer show it. */
    private function present(UserReel $reel, User $viewer): array
    {
        return ReelPresenter::present($reel, $viewer);
    }

    private function generateVideoThumbnail($file): ?string
    {
        // This would require FFmpeg to be installed on the server
        // For now, return null - this can be implemented later
        return null;
    }
}
