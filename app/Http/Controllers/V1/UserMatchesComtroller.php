<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ConversationUser;
use App\Models\Frontend\FriendRequest;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMatchesComtroller extends Controller
{
    // list of users 
    public function findmatches()
    {
        $alluser = User::eligibleForLinkup()->where('status', true)->where('type', '!=', 'admin')
            ->where('id', '!=', Auth::user()->id)->get();
        return response()->json([
            'status' => true,
            'data' => UserResource::collection($alluser)
        ], 200);
    }

    // marking like or dislike the user 
    public function likeOrDislikeUser(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|in:like,dislike,loveit'
        ]);

        $userLoggedin = Auth::user();
        UserMatch::updateOrCreate(
            [
                'user_id' => $userLoggedin->id,
                'target_user_id' => $request->id,
            ],
            [
                'status' => $request->status,
            ]
        );

        // Handle Friend Request logic
        if (in_array($request->status, ['like', 'loveit'])) {
            // Create friend request (if not already)
            $friendRequest = FriendRequest::firstOrCreate([
                'user_id' => $userLoggedin->id,
                'receiver_id' => $request->id
            ], [
                'status' => 0,
                'type' => 0,
            ]);

            // Check if mutual like exists
            $hasMutualLike = UserMatch::where('user_id', $request->id)
                ->where('target_user_id', $userLoggedin->id)
                ->whereIn('status', ['like', 'loveit'])
                ->exists();

            if ($hasMutualLike) {
                // Approve friend request if pending
                $friendRequest->update(['status' => 1]);

                // Also auto-accept reverse request if it exists
                FriendRequest::where('user_id', $request->id)
                    ->where('receiver_id', $userLoggedin->id)
                    ->update(['status' => 1]);
            }
        } elseif ($request->status === 'dislike') {
            $receiver = $request->id;
            // Clean up any pending friend requests (either direction)
            FriendRequest::where(function ($query) use ($userLoggedin, $receiver) {
                $query->where('user_id', $userLoggedin->id)
                    ->where('receiver_id', $receiver);
            })->orWhere(function ($query) use ($userLoggedin, $receiver) {
                $query->where('user_id', $receiver)
                    ->where('receiver_id', $userLoggedin->id);
            })->delete();
        }

        return response()->json([
            'status' => true,
            'message' => in_array($request->status, ['like', 'loveit']) ? 'User liked successfully!' : 'User disliked successfully!',
        ], 200);
    }

    // the all matches of the logedin user 
    public function getMatches()
    {
        $userLoggedin = Auth::user();
        $liked = $userLoggedin->likedUsers;
        return response()->json([
            'status' => true,
            'data' => $liked
        ], 200);
    }

    // get the link up user 
    public function getLinkupUser($id)
    {
        $user = User::eligibleForLinkup()->where('id', $id)->where('status', true)->first();
        if ($user) {
            return response()->json([
                'status' => true,
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    public function getMutualMatches()
    {
        $userId = Auth::user()->id;
        // Get all users this user liked
        $likedUserIds = UserMatch::where('user_id', $userId)
            ->where('status', 'like')
            ->pluck('target_user_id');

        // Get only those who liked the user back
        $mutualUserIds = UserMatch::whereIn('user_id', $likedUserIds)
            ->where('target_user_id', $userId)
            ->where('status', 'like')
            ->pluck('user_id');

        // Load full User models
        $users = User::eligibleForLinkup()->whereIn('id', $mutualUserIds)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No mutual match found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => UserResource::collection($users)
        ]);
    }
}
