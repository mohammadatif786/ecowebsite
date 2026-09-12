<?php

namespace App\Actions\Chat;

use App\Models\Message;
use App\Models\TicketSale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetConversationAction
{
    public function getConversaction(User $receiver, string $isFriend, string $isMutualLike, Request $request, string $user)
    {
        if (! $isFriend && ! $isMutualLike) {
            abort(403, 'You can only chat with users who are your friends or mutual matches.');
        }

        $query = $this->messageQuery($receiver);

        if ($search = $request->query('search')) {
            $query->filter(['search' => $search]);
        }

        $messages = $query->latest()->take(50)->get()->reverse()->toArray();
        $receiver = User::where('uid', $user)->first();

        $this->messageRead($receiver);
        $tickets = $this->getTicketSaleData();

        $conversation = [
            'messages' => $messages,
            'receiver' => $receiver,
            'search' => $search,
            'tickets' => $tickets
        ];

        return $conversation;
    }

    private function messageQuery(User $receiver)
    {
        try {
            $query = Message::where(function ($query) use ($receiver) {
                $query->where('from_user_id', Auth::id())
                    ->where('to_user_id', $receiver->id);
            })->orWhere(function ($query) use ($receiver) {
                $query->where('from_user_id', $receiver->id)
                    ->where('to_user_id', Auth::id());
            })->with(['sender', 'receiver']);

            return $query;
        } catch (\Throwable $th) {
            Log::error('get message query issue!', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
        }
    }

    private function messageRead(User $receiver)
    {
        try {
            Message::where('from_user_id', $receiver->id)
                ->where('to_user_id', Auth::id())
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
        } catch (\Throwable $th) {
            Log::error('get issue while make message as read!', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
        }
    }

    private function getTicketSaleData()
    {
        try {
            $tickets = TicketSale::query()
                ->where('user_id', Auth::id())
                ->where('ticket_status', 'confirmed')
                ->with(['event', 'event.eventDetails', 'ticket'])
                ->orderByDesc('created_at')
                ->get();

            return $tickets;
        } catch (\Throwable $th) {
            Log::error('get ticket sale data issue!', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
        }
    }
}
