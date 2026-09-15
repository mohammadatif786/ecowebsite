<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Vibes\Actions\PostVibeCommentAction;
use App\Domain\Vibes\Actions\ToggleVibeLikeAction;
use App\Http\Controllers\Controller;
use App\Models\Vibe;
use App\Models\VibeComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VibeInteractionController extends Controller
{
    public function toggleLike(Vibe $vibe, ToggleVibeLikeAction $action): JsonResponse
    {
        $result = $action->execute(auth()->user(), $vibe);

        return response()->json($result);
    }

    public function indexComments(Vibe $vibe): JsonResponse
    {
        $comments = $vibe->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
            ->withCount(['likes', 'replies'])
            ->latest()
            ->paginate(20);

        // Add is_liked status for auth user
        if (auth()->check()) {
            $comments->getCollection()->each(function ($comment) {
                $comment->is_liked = $comment->likes()->where('user_id', auth()->id())->exists();
                $comment->replies->each(function ($reply) {
                    $reply->is_liked = $reply->likes()->where('user_id', auth()->id())->exists();
                    $reply->likes_count = $reply->likes()->count();
                });
            });
        }

        return response()->json($comments);
    }

    public function topComments(Vibe $vibe): JsonResponse
    {
        $comments = $vibe->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar'])
            ->withCount('likes')
            ->latest()
            ->take(3)
            ->get();

        if (auth()->check()) {
            $comments->each(function ($comment) {
                $comment->is_liked = $comment->likes()->where('user_id', auth()->id())->exists();
            });
        }

        return response()->json($comments);
    }

    public function storeComment(Request $request, Vibe $vibe, PostVibeCommentAction $action): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:vibe_comments,id',
        ]);

        $comment = $vibe->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $vibe->increment('comments_count');

        return response()->json($comment->load('user:id,name,avatar')->loadCount('likes'), 201);
    }

    public function share(Vibe $vibe): JsonResponse
    {
        $vibe->increment('shares_count');

        return response()->json([
            'shares_count' => $vibe->shares_count,
        ]);
    }

    public function toggleCommentLike(VibeComment $comment): JsonResponse
    {
        $user = auth()->user();
        $like = $comment->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            $comment->likes()->create([
                'user_id' => $user->id,
                'type' => 'like',
            ]);
            $isLiked = true;
        }

        return response()->json([
            'is_liked' => $isLiked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }
}
