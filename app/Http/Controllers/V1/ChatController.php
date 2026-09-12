<?php

namespace App\Http\Controllers\V1;

use App\Contracts\ChatServiceInterface;
use App\Events\MessageSent;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\SendChatMessageEmailJob;
use App\Models\Message;
use App\Models\Notification;
use App\Models\TicketSale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $chatService;

    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }
    public function index()
    {

        $chatUsers = $this->chatService->getChatUsers();

        return response()->json([
            'users' => $chatUsers,
        ]);
    }

    public function startChat(string $slug, string $user, Request $request)
    {
        $getConversation = $this->chatService->getConversation($slug, $user, $request);

        return response()->json([
            'chatmessages' => $getConversation['messages'],
            'receiver' => $getConversation['receiver'],
            'auth' => ['user' => Auth::user()],
            'search' => $getConversation['search'],
            'tickets' => $getConversation['tickets'],
        ]);
    }

    public function toggleUnPin(int $userId)
    {
        $this->chatService->toggleUnPin($userId);

        return response()->json(['success' => 'Pin status updated successfully.']);
    }
    public function togglePin(int $userId)
    {

        $this->chatService->togglePin($userId);

        return response()->json(['success' => 'Pin status updated successfully.']);
    }

    private function createMessageNotification(Message $message): void
    {
        try {
            $toUserId = (int) $message->to_user_id;
            $fromUserId = (int) $message->from_user_id;

            if (! $toUserId || ! $fromUserId || $toUserId === $fromUserId) {
                return;
            }

            $sender = $message->relationLoaded('sender') ? $message->sender : $message->sender()->first();

            $notification = Notification::create([
                'user_id' => $toUserId,
                'send_by' => (string) $fromUserId,
                'type' => 'message',
                'context' => 'message',
                'title' => $sender?->name ? ('New message from ' . $sender->name) : 'New message',
                'message' => $message->type === 'text'
                    ? (string) ($message->content ?? '')
                    : 'You received a new message',
                'unread' => true,
                'priority' => true,
                'icon' => 'message',
                'avatar' => null,
                'metadata' => [
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $toUserId,
                    'message_id' => $message->id,
                ],
            ]);

            Log::info('Message notification created', [
                'notification_id' => $notification->id,
                'message_id' => $message->id,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create message notification', [
                'error' => $e->getMessage(),
                'message_id' => $message->id ?? null,
                'from_user_id' => $message->from_user_id ?? null,
                'to_user_id' => $message->to_user_id ?? null,
            ]);
        }
    }

    public function store(Request $request, $toUserId)
    {
        $validated = $request->validate([
            'content' => 'required_without_all:gif_url,ticket_id,ticket_token,ticket_sale_id,attachments|string|nullable',
            'gif_url' => 'nullable|url',
            'ticket_id' => 'nullable|string',
            'ticket_token' => 'nullable|string',
            'ticket_sale_id' => 'nullable|integer|exists:ticket_sales,id',
            'ticket_qty' => 'nullable|integer|min:1',
            'ticket_event' => 'nullable|string',
            'ticket_date_label' => 'nullable|string',
            'ticket_venue' => 'nullable|string',
            'ticket_city' => 'nullable|string',
            'ticket_type' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*.file' => 'nullable|file',
            'attachments.*.type' => 'nullable|string|in:photo,pdf',
            'attachments.*.name' => 'nullable|string',
            'replied_to' => 'nullable|exists:messages,id',
            'ticket_name' => 'nullable|string',
            'ticket_price' => 'nullable|string',
            'sale_end' => 'nullable|string',
            'sale_start' => 'nullable|string',
        ]);

        $returnData = User::findOrFail($toUserId);

        $created = [];

        if (!empty($validated['ticket_sale_id'])) {
            $qtyToSend = (int) ($validated['ticket_qty'] ?? 1);
            $qtyToSend = max(1, min($qtyToSend, 50));

            $senderId = (int) Auth::id();
            $receiverId = (int) $toUserId;

            $sentSales = DB::transaction(function () use ($validated, $senderId, $receiverId, $qtyToSend) {
                /** @var TicketSale $sale */
                $sale = TicketSale::lockForUpdate()->findOrFail((int) $validated['ticket_sale_id']);

                if ((int) $sale->user_id !== $senderId) {
                    abort(403, 'You can only share tickets you own.');
                }

                if ((string) $sale->ticket_status !== 'confirmed') {
                    abort(422, 'Only confirmed tickets can be shared.');
                }

                $available = (int) ($sale->no_of_tickets ?? 0);
                if ($available < 1) {
                    abort(422, 'This ticket is not available to share.');
                }

                $sendCount = min($qtyToSend, $available);
                $sent = [];

                for ($i = 0; $i < $sendCount; $i++) {
                    // If the sale is already a single unit, transfer it directly
                    if ((int) $sale->no_of_tickets === 1) {
                        $sale->user_id = $receiverId;
                        $sale->transferred_from_user_id = $senderId;
                        $sale->transferred_at = now();
                        $sale->save();
                        $sent[] = $sale->fresh();
                        break;
                    }

                    // Otherwise, split out 1 unit into a new TicketSale for the recipient
                    $sale->no_of_tickets = max(0, (int) $sale->no_of_tickets - 1);
                    $sale->save();

                    $newCode = Str::random(20);
                    $clone = $sale->replicate([
                        'id',
                        'created_at',
                        'updated_at',
                    ]);
                    $clone->user_id = $receiverId;
                    $clone->no_of_tickets = 1;
                    $clone->ticket_qrcode_id = $newCode;
                    $clone->ticket_qrcode = $newCode;
                    $clone->web_qrcode = null;
                    $clone->transferred_from_user_id = $senderId;
                    $clone->transferred_at = now();
                    $clone->save();
                    $sent[] = $clone;
                }

                return collect($sent)->map(fn($s) => $s->loadMissing(['event', 'event.eventDetails', 'ticket']))->values();
            });

            foreach ($sentSales as $sentSale) {
                $details = $sentSale->event?->eventDetails;
                $date = $details?->single_event_date ?? $sentSale->event?->single_event_date ?? null;

                $ticketMsg = Message::create([
                    'from_user_id' => $senderId,
                    'to_user_id' => $receiverId,
                    'content' => null,
                    'replied_to' => $validated['replied_to'] ?? null,
                    'type' => 'ticket',
                    'meta' => [
                        'ticket_sale_id' => $sentSale->id,
                        'ticket_id' => $sentSale->ticket_id,
                        'ticket_name' => $sentSale->ticket_name,
                        'ticket_type' => $sentSale->ticket_type,
                        'event_id' => $sentSale->link_up_event_id,
                        'event' => $sentSale->event?->title,
                        'date_label' => $date,
                        'venue' => $sentSale->event?->location,
                        'city' => $sentSale->event?->city,
                        'token' => $sentSale->ticket_qrcode_id,
                    ],
                ]);

                $created[] = $ticketMsg;
                $this->createMessageNotification($ticketMsg->loadMissing(['sender', 'receiver']));
                broadcast(new MessageSent($ticketMsg));
                SendChatMessageEmailJob::dispatch($ticketMsg->id);
            }
        }

        if (!empty($validated['gif_url'])) {
            $gif = Message::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $toUserId,
                'content' => null,
                'replied_to' => $validated['replied_to'] ?? null,
                'type' => 'gif',
                'meta' => ['url' => $validated['gif_url']],
            ]);
            $created[] = $gif;
            $this->createMessageNotification($gif->loadMissing(['sender', 'receiver']));
            broadcast(new MessageSent($gif));
            SendChatMessageEmailJob::dispatch($gif->id);
        }

        if (!empty($validated['ticket_id']) && !empty($validated['ticket_token'])) {
            $ticketMsg = Message::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $toUserId,
                'content' => null,
                'replied_to' => $validated['replied_to'] ?? null,
                'type' => 'ticket',
                'meta' => [
                    'ticket_id' => $validated['ticket_id'],
                    'token' => $validated['ticket_token'],
                    'event' => $validated['ticket_event'] ?? null,
                    'date_label' => $validated['ticket_date_label'] ?? null,
                    'venue' => $validated['ticket_venue'] ?? null,
                    'city' => $validated['ticket_city'] ?? null,
                    'ticket_type' => $validated['ticket_type'] ?? null,
                    'sale_end' => $validated['sale_end'] ?? null,
                    'sale_start' => $validated['sale_start'] ?? null,
                    'ticket_name' => $validated['ticket_name'] ?? null,
                    'ticket_price' => $validated['ticket_price'] ?? null,
                ],
            ]);
            $created[] = $ticketMsg;
            $this->createMessageNotification($ticketMsg->loadMissing(['sender', 'receiver']));
            broadcast(new MessageSent($ticketMsg));
            SendChatMessageEmailJob::dispatch($ticketMsg->id);
        }

        $content = $validated['content'] ?? null;
        if ($content !== null && $content !== '') {
            $type = str_starts_with((string)$content, '__CALL_') ? 'signal' : 'text';
            $textMsg = Message::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $toUserId,
                'content' => $content,
                'replied_to' => $validated['replied_to'] ?? null,
                'type' => $type,
                'meta' => null,
            ]);
            $created[] = $textMsg;
            $this->createMessageNotification($textMsg->loadMissing(['sender', 'receiver']));
            broadcast(new MessageSent($textMsg));
            SendChatMessageEmailJob::dispatch($textMsg->id);
        }

        $fileEntries = [];
        $filesBag = $request->file('attachments', []);
        if (is_array($filesBag) && !empty($filesBag)) {
            foreach ($filesBag as $index => $entry) {
                $uploaded = is_array($entry) ? ($entry['file'] ?? null) : $entry;
                if ($uploaded instanceof \Illuminate\Http\UploadedFile) {
                    $fileEntries[] = [
                        'file' => $uploaded,
                        'type' => $request->input("attachments.$index.type"),
                        'name' => $request->input("attachments.$index.name"),
                    ];
                }
            }
        }

        if (empty($fileEntries) && !empty($validated['attachments']) && is_array($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                if (!empty($att['file']) && $att['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $fileEntries[] = [
                        'file' => $att['file'],
                        'type' => $att['type'] ?? null,
                        'name' => $att['name'] ?? null,
                    ];
                }
            }
        }

        foreach ($fileEntries as $att) {
            $stored = $att['file']->store('chat', 'public');
            $url = asset('storage/' . $stored);
            $kind = $att['type'];
            $name = $att['name'];
            $msgType = $kind === 'photo' ? 'image' : ($kind === 'pdf' ? 'pdf' : 'file');
            $fileMsg = Message::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $toUserId,
                'content' => null,
                'replied_to' => $validated['replied_to'] ?? null,
                'type' => $msgType,
                'meta' => [
                    'url' => $url,
                    'name' => $name,
                    'disk' => 'public',
                    'path' => $stored,
                ],
            ]);
            $created[] = $fileMsg;
            $this->createMessageNotification($fileMsg->loadMissing(['sender', 'receiver']));
            broadcast(new MessageSent($fileMsg));
            SendChatMessageEmailJob::dispatch($fileMsg->id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'messages' => collect($created)->map(fn($m) => $m->load(['sender', 'receiver']))->values(),
            ]);
        }

        return response()->json(['status' => true, 'slug' => $returnData->name, 'user' => $returnData->uid]);
    }
}
