<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
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
            ->orderByDesc('id') // two messages in the same second: the later one is the newer
            ->get();

        $partnerIds = [];
        foreach ($messages as $msg) {
            $partnerId = $msg->from_user_id === $user->id ? $msg->to_user_id : $msg->from_user_id;
            if (!in_array($partnerId, $partnerIds, true)) {
                $partnerIds[] = $partnerId;
            }
        }

        // People you blocked, or who blocked you, drop out of the inbox.
        $blocked = $this->blockedIds($user->id);
        $partnerIds = array_values(array_filter($partnerIds, fn ($id) => ! in_array((int) $id, $blocked, true)));

        $partners = User::whereIn('id', $partnerIds)
            ->get(['id', 'name', 'linkup_id', 'avatar'])
            // Newest conversation first (the query above returns them in id order).
            ->sortBy(fn ($p) => array_search($p->id, $partnerIds, true))
            ->values()
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
                    'unread' => $messages->filter(fn ($m) => $m->from_user_id === $p->id && $m->to_user_id === $user->id && $m->read_at === null)->count(),
                ];
            });

        return response()->json([
            'conversations' => $partners,
        ]);
    }

    public function getMessages(Request $request, User $recipient): JsonResponse
    {
        $user = Auth::user();

        if (in_array((int) $recipient->id, $this->blockedIds($user->id), true)) {
            return response()->json(['message' => 'You can’t message this person.'], 403);
        }

        // Opening the chat reads what they sent you.
        VibeMessage::where('from_user_id', $recipient->id)->where('to_user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);

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

        if (in_array((int) $recipient->id, $this->blockedIds($user->id), true)) {
            return response()->json(['message' => 'You can’t message this person.'], 403);
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

    /** Users this user blocked, and users who blocked this user. */
    private function blockedIds(int $userId): array
    {
        return BlockedUser::query()->where('is_blocked', true)
            ->where(fn ($q) => $q->where('user_id', $userId)->orWhere('blocked_user_id', $userId))
            ->get(['user_id', 'blocked_user_id'])
            ->map(fn ($b) => (int) ((int) $b->user_id === $userId ? $b->blocked_user_id : $b->user_id))
            ->unique()->values()->all();
    }
}
