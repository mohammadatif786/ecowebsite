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
    public function getConversations(Request $request): JsonResponse
    {
        $user = Auth::user();

        $messages = VibeMessage::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->latest()
            ->get();

        $partnerIds = [];
        foreach ($messages as $msg) {
            $partnerId = $msg->from_user_id === $user->id ? $msg->to_user_id : $msg->from_user_id;
            if (!in_array($partnerId, $partnerIds, true)) {
                $partnerIds[] = $partnerId;
            }
        }

        $partners = User::whereIn('id', $partnerIds)
            ->get(['id', 'name', 'linkup_id', 'avatar'])
            ->map(function ($p) use ($messages, $user) {
                $lastMsg = $messages->first(function ($m) use ($user, $p) {
                    return ($m->from_user_id === $user->id && $m->to_user_id === $p->id) ||
                           ($m->from_user_id === $p->id && $m->to_user_id === $user->id);
                });

                return [
                    'user_id' => $p->id,
                    'name' => $p->name,
                    'handle' => '@' . ($p->linkup_id ?? $p->name),
                    'avatar' => $p->avatar,
                    'last_message' => $lastMsg?->content,
                    'last_message_at' => $lastMsg?->created_at?->toISOString(),
                ];
            });

        return response()->json([
            'conversations' => $partners,
        ]);
    }

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
