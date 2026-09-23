<?php

namespace App\Http\Controllers\NewFrontend;

use App\Events\MessageSent;
use App\Domain\Linkup\Services\LinkupAccessService;
use App\Http\Controllers\Controller;
use App\Jobs\SendChatMessageEmailJob;
use App\Models\Message;
use App\Models\ConversationPreference;
use App\Models\Notification;
use App\Models\TicketSale;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Chat endpoints used exclusively by the New Frontend.
 */
class ChatController extends Controller
{
    public function messages(User $recipient): JsonResponse
    {
        $this->ensureCanChat($recipient);

        Message::where('from_user_id', $recipient->id)
            ->where('to_user_id', Auth::id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        $messages = Message::query()
            ->where(function ($query) use ($recipient) {
                $query->where('from_user_id', Auth::id())
                    ->where('to_user_id', $recipient->id);
            })
            ->orWhere(function ($query) use ($recipient) {
                $query->where('from_user_id', $recipient->id)
                    ->where('to_user_id', Auth::id());
            })
            ->latest()
            ->take(50)
            ->get(['id', 'from_user_id', 'to_user_id', 'content', 'type', 'meta', 'created_at', 'is_read'])
            ->reverse()
            ->values();

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, User $recipient): JsonResponse
    {
        $this->ensureCanChat($recipient);

        $validated = $request->validate([
            'content' => 'required_without_all:gif_url,attachments,ticket_sale_id|string|nullable|max:500',
            'gif_url' => 'nullable|url',
            'ticket_sale_id' => 'nullable|integer|exists:ticket_sales,id',
            'ticket_qty' => 'nullable|integer|min:1|max:50',
            'attachments' => 'nullable|array',
            'attachments.*.file' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp,application/pdf|max:10240',
            'attachments.*.type' => 'nullable|string|in:photo,pdf',
            'attachments.*.name' => 'nullable|string|max:255',
        ]);

        $created = collect();

        if (! empty($validated['ticket_sale_id'])) {
            $sale = TicketSale::whereKey($validated['ticket_sale_id'])
                ->where('user_id', Auth::id())
                ->where('ticket_status', 'confirmed')
                ->with(['event', 'event.eventDetails', 'ticket', 'checkins'])
                ->firstOrFail();
            $date = $sale->event?->eventDetails?->single_event_date
                ?? $sale->event?->single_event_date;
            $created->push($this->createMessage($recipient, [
                'content' => null,
                'type' => 'ticket',
                'meta' => [
                    'ticket_sale_id' => $sale->id,
                    'ticket_id' => $sale->ticket_id,
                    'ticket_name' => $sale->ticket_name,
                    'ticket_type' => $sale->ticket_type,
                    'event' => $sale->event?->title,
                    'date_label' => $date,
                    'venue' => $sale->event?->location,
                    'city' => $sale->event?->city,
                    'token' => $sale->ticket_qrcode_id,
                    // Store the complete ticket snapshot so the recipient can
                    // open the same detail modal used in the Events bookings page.
                    'ticket_details' => $this->ticketDetailPayload($sale),
                ],
            ]));
        }

        if (! empty($validated['content'])) {
            $created->push($this->createMessage($recipient, [
                'content' => $validated['content'],
                'type' => 'text',
                'meta' => null,
            ]));
        }

        if (! empty($validated['gif_url'])) {
            $created->push($this->createMessage($recipient, [
                'content' => null,
                'type' => 'gif',
                'meta' => ['url' => $validated['gif_url']],
            ]));
        }

        foreach ($request->file('attachments', []) as $index => $attachment) {
            $file = is_array($attachment) ? ($attachment['file'] ?? null) : $attachment;
            if (! $file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }

            $kind = $request->input("attachments.{$index}.type");
            $storedPath = $file->store('linkup-chat', 'local');
            $created->push($this->createMessage($recipient, [
                'content' => null,
                'type' => $kind === 'photo' ? 'image' : 'pdf',
                'meta' => [
                    'name' => $request->input("attachments.{$index}.name") ?: $file->getClientOriginalName(),
                    'disk' => 'local',
                    'path' => $storedPath,
                ],
            ]));
        }

        return response()->json([
            'success' => true,
            'messages' => $created->map(fn (Message $message) => $message->load(['sender', 'receiver']))->values(),
        ]);
    }

    public function togglePin(User $recipient): JsonResponse
    {
        $this->ensureCanChat($recipient);
        abort_unless(Schema::hasTable('conversation_preferences'), 503, 'Conversation preferences are being upgraded. Please try again shortly.');
        $preference = ConversationPreference::firstOrCreate([
            'user_id' => Auth::id(), 'other_user_id' => $recipient->id,
        ]);
        $preference->update(['is_pinned' => ! $preference->is_pinned]);

        return response()->json(['pinned' => (bool) $preference->fresh()->is_pinned]);
    }

    private function createMessage(User $recipient, array $attributes): Message
    {
        $message = Message::create(array_merge($attributes, [
            'from_user_id' => Auth::id(),
            'to_user_id' => $recipient->id,
        ]));

        if (($message->meta['disk'] ?? null) === 'local' && ! empty($message->meta['path'])) {
            $meta = $message->meta;
            $meta['url'] = route('new_frontend.dating.chats.attachments.show', $message);
            $message->update(['meta' => $meta]);
        }

        $this->createMessageNotification($message);
        try {
            broadcast(new MessageSent($message));
        } catch (\Throwable $exception) {
            Log::warning('Broadcast MessageSent failed: ' . $exception->getMessage());
        }
        SendChatMessageEmailJob::dispatch($message->id);

        return $message;
    }

    private function ticketDetailPayload(TicketSale $sale): array
    {
        $event = $sale->event;
        $eventDetails = $event?->eventDetails;
        $startTime = $event?->start_time ?: $eventDetails?->single_event_date;
        $endTime = $event?->end_time ?: $eventDetails?->single_event_date ?: $startTime;
        $owner = Auth::user();

        return [
            'id' => $sale->id,
            'ticket_qrcode' => $sale->ticket_qrcode ?: $sale->ticket_qrcode_id ?: $sale->id,
            'ticket_qrcode_id' => $sale->ticket_qrcode_id,
            'ticket_name' => $sale->ticket_name ?: $sale->ticket?->name ?: 'Ticket',
            'ticket_type' => $sale->ticket_type ?: $sale->ticket?->ticket_type ?: $sale->ticket?->type ?: 'Ticket',
            'ticket_status' => $sale->ticket_status,
            'quantity' => max(1, (int) ($sale->no_of_tickets ?? 1)),
            'no_of_tickets' => max(1, (int) ($sale->no_of_tickets ?? 1)),
            'created_at' => $sale->created_at,
            'sub_total' => (float) ($sale->sub_total ?? 0),
            'total' => (float) ($sale->total ?? $sale->stripe_price ?? 0),
            'fee' => (float) ($sale->fee ?? 0),
            'event_tax' => (float) ($sale->event_tax ?? 0),
            'discount' => (float) ($sale->discount ?? 0),
            'coupan_amount' => (float) ($sale->coupan_amount ?? 0),
            'drink_addons' => $sale->drink_addons ?: [],
            'table_addons' => $sale->table_addons ?: [],
            'wellness_addons' => $sale->wellness_addons ?: [],
            'cookout_included_protein' => $sale->cookout_included_protein,
            'cookout_included_sides' => $sale->cookout_included_sides ?: [],
            'cookout_addons' => $sale->cookout_addons ?: [],
            'package_data' => $sale->package_data,
            'fee_breakdown' => $sale->fee_breakdown ?: [],
            'checkins' => $sale->checkins->map(fn ($checkin) => ['id' => $checkin->id, 'created_at' => $checkin->created_at])->values(),
            'event' => [
                'id' => $event?->id,
                'title' => $event?->title ?? $sale->ticket_name ?? 'Event',
                'venue' => $event?->venue ?? $event?->location,
                'city' => $event?->city,
                'country' => $event?->country,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'currency_symbol' => $event?->currency_symbol ?? '$',
            ],
            'user' => [
                'name' => $owner?->name,
                'email' => $owner?->email,
                'phone_number' => $owner?->phone_number,
            ],
        ];
    }

    private function createMessageNotification(Message $message): void
    {
        try {
            $sender = Auth::user();
            Notification::create([
                'user_id' => $message->to_user_id,
                'send_by' => (string) $message->from_user_id,
                'type' => 'message',
                'context' => 'message',
                'title' => $sender?->name ? 'New message from ' . $sender->name : 'New message',
                'message' => $message->type === 'text' ? (string) $message->content : 'You received a new message',
                'unread' => true,
                'priority' => true,
                'icon' => 'message',
                'metadata' => [
                    'from_user_id' => $message->from_user_id,
                    'to_user_id' => $message->to_user_id,
                    'message_id' => $message->id,
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::warning('New Frontend message notification failed.', ['error' => $exception->getMessage()]);
        }
    }

    private function ensureCanChat(User $recipient): void
    {
        $user = Auth::user();

        abort_unless(
            $user && app(LinkupAccessService::class)->canChat($user, $recipient),
            403,
            'You can only chat with users who are your friends or mutual matches.'
        );
    }
}
