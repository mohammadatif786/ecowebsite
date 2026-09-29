<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VibeMessage;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VibeChatController extends Controller
{
    public function getMessages(Request $request, User $recipient): JsonResponse
    {
        $user = Auth::user();

        $messages = VibeMessage::where(function ($q) use ($user, $recipient) {
                $q->where('from_user_id', $user->id)
                  ->where('to_user_id', $recipient->id);
            })->orWhere(function ($q) use ($user, $recipient) {
                $q->where('from_user_id', $recipient->id)
                  ->where('to_user_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function storeMessage(Request $request, User $recipient): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $user = Auth::user();

        if ($user->id === $recipient->id) {
            return response()->json(['message' => 'You cannot message yourself'], 422);
        }

        $message = VibeMessage::create([
            'from_user_id' => $user->id,
            'to_user_id' => $recipient->id,
            'content' => $request->input('content'),
            'type' => 'text',
        ]);

        Notification::create([
            'title' => 'New Vibe Message',
            'message' => "{$user->name} sent you a vibe message.",
            'send_by' => $user->id,
            'user_id' => $recipient->id,
            'type' => 'message',
            'context' => 'vibe_chat',
            'unread' => true,
            'avatar' => $user->avatar,
        ]);

        $allMessages = VibeMessage::where(function ($q) use ($user, $recipient) {
                $q->where('from_user_id', $user->id)
                  ->where('to_user_id', $recipient->id);
            })->orWhere(function ($q) use ($user, $recipient) {
                $q->where('from_user_id', $recipient->id)
                  ->where('to_user_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $allMessages,
        ]);
    }
}
