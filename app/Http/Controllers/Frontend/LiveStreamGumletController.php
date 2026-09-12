<?php

namespace App\Http\Controllers\Frontend;

use App\Events\LiveCommentPosted;
use App\Events\LiveGiftSent;
use App\Events\LiveGuestRemoved;
use App\Events\LiveInviteReply;
use App\Events\LiveInviteSent;
use App\Events\LiveProductsUpdated;
use App\Events\LiveReactionSent;
use App\Events\PollCreated;
use App\Events\PollEnded;
use App\Events\PollVoted;
use App\Events\QnaAnswered;
use App\Events\QnaDeleted;
use App\Events\QnaSubmitted;
use App\Events\ViewerCountUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveStreamRequest;
use App\Jobs\SendNewLiveStreamToFollowersJob;
use App\Models\LinkupLiveGift;
use App\Models\LiveComment;
use App\Models\LiveFollower;
use App\Models\LiveGift;
use App\Models\LivePoll;
use App\Models\LivePollOption;
use App\Models\LiveQna;
use App\Models\LiveStreamCategories;
use App\Models\LiveStreamGumlet;
use App\Models\LiveViewer;
use App\Models\Notification;
use App\Models\PrivateLiveStreamSub;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LiveStreamLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use O21\LaravelWallet\Models\Custodian;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Yasser\Agora\RtcTokenBuilder;

class LiveStreamGumletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $user_Streams = LiveStreamGumlet::where('user_id', $user->id)->get();
        $all_Streams = LiveStreamGumlet::where('status', 'live')->get();

        // Check subscription status for each stream
        $all_Streams->each(function ($stream) use ($user) {
            // Add stats to each stream
            $stream->viewer_count = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('left_at', null)
                ->count();

            $stream->gift_count = LiveGift::where('live_stream_gumlet_id', $stream->id)
                ->sum('qty');

            $stream->like_count = $stream->like_count ?? 0;

            $stream->is_subscribed = false;

            // Host is always subscribed
            if ($stream->user_id == $user->id) {
                $stream->is_subscribed = true;

                return;
            }

            if ($stream->visibility === 'private') {
                $hasOneTime = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                    ->where('stream_id', $stream->id)
                    ->where('status', 'active')
                    ->exists();

                $monthly = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                    ->where('user_streamer_id', $stream->user_id)
                    ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
                    ->whereIn('status', ['active', 'Active'])
                    ->orderByDesc('created_at')
                    ->first();

                $hasMonthly = false;
                if ($monthly) {
                    $expiresAt = Carbon::parse($monthly->created_at)->addDays(30);
                    $hasMonthly = $expiresAt->isFuture();
                }

                if ($hasOneTime || $hasMonthly) {
                    $stream->is_subscribed = true;
                }
            } else {
                $stream->is_subscribed = true; // Public streams are open
            }
        });

        $balance = $user->balance('USD')->value->get();
        $users = User::where('type', 'user')->whereNotNull('linkup_id')->get();

        $gifts = LinkupLiveGift::where('active', true)->get();
        $live_stream_categories = LiveStreamCategories::query()->orderBy('category')->get();

        return Inertia::render('User/GoLive/Index', [
            'user_Streams' => $user_Streams,
            'balance' => $balance,
            'all_Streams' => $all_Streams,
            'gifts' => $gifts,
            'live_stream_categories' => $live_stream_categories,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.livestream.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LiveStreamRequest $request)
    {
        $input = $request->validated();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('GUMLET_API_TOKEN'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.gumlet.com/v1/video/live/assets', [
            'live_source_id' => env('GUMLET_SOURCE_ID'),
            'resolution' => $input['resolution'],
            'name' => $input['title'],
        ]);

        $responseOutput = json_decode($response->getBody(), true);

        if (isset($responseOutput['error'])) {
            return back()->with('message', $responseOutput['error']['message']);
        }

        $urlforkey = $responseOutput['stream_url'];
        $partsofkey = explode('app/', $urlforkey);
        $extractedString = isset($partsofkey[1]) ? $partsofkey[1] : null;

        $url = $responseOutput['stream_url'];
        $parts = explode('/app', $url);
        $cleaned_url = $parts[0].'/app';

        if ($request->hasFile('thumbnail')) {
            $input['thumbnail'] = $request->file('thumbnail')->store('streams_thumbnail', 'public');
        }

        $stream = LiveStreamGumlet::create([
            'title' => $input['title'],
            'user_id' => Auth::user()->id,
            'status' => 'created',
            'stream_key' => $extractedString,
            'live_asset_id' => $responseOutput['live_asset_id'],
            'live_video_source_id' => $responseOutput['live_video_source_id'],
            'resolution' => $input['resolution'],
            'stream_url' => $cleaned_url,
            'playback_url' => $responseOutput['output']['playback_url'],
            'thumbnail' => $input['thumbnail'],
            'visibility' => $input['visibility'],
            'broadcast_type' => $input['broadcast_type'],
            'start_time' => $input['start_time'] ?? now(),
        ]);

        if ($stream?->id) {
            SendNewLiveStreamToFollowersJob::dispatch((int) $stream->id);
        }

        return back()->with('message', 'Stream is created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->index();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LiveStreamRequest $request, LiveStreamGumlet $stream)
    {
        $input = $request->validated();
        $input['thumbnail'] = $stream->thumbnail;
        if ($request->hasFile('thumbnail')) {
            if ($stream->thumbnail && Storage::disk('public')->exists($stream->thumbnail)) {
                Storage::disk('public')->delete($stream->thumbnail);
            }
            $input['thumbnail'] = $request->file('thumbnail')->store('streams_thumbnail', 'public');
        }
        $stream->update($input);

        return back()->with('message', 'Stream is updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stream = LiveStreamGumlet::find($id);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('GUMLET_API_TOKEN'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->delete('https://api.gumlet.com/v1/video/live/assets/'.$stream->live_asset_id);

        $responseOutput = json_decode($response->getBody(), true);
        if (isset($responseOutput['error'])) {
            return back()->with('message', $responseOutput['error']['message']);
        }

        DB::transaction(function () use ($stream) {
            $stream->delete();
        }, 2);

        if ($stream->thumbnail && Storage::disk('public')->exists($stream->thumbnail)) {
            Storage::disk('public')->delete($stream->thumbnail);
        }

        return back()->with('message', 'Stream is Deleted successfully!');
    }

    public function status(string $id)
    {
        $stream = LiveStreamGumlet::find($id);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('GUMLET_API_TOKEN'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.gumlet.com/v1/video/live/assets/'.$stream->live_asset_id.'/complete');
        $stream->update(['status' => 'completed']);

        return back()->with('message', 'Livestream completed successfully.');
    }

    public function startStream(Request $request)
    {
        $Id = $request->input('id');
        // Example logic: Start livestream or mark as active
        $livestream = LiveStreamGumlet::where('id', $Id)->first();
        if ($livestream) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('GUMLET_API_TOKEN'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.gumlet.com/v1/video/live/assets/'.$livestream->live_asset_id.'/start');
            $livestream->status = 'live';
            $livestream->save();

            return back()->with('success', 'Livestream started successfully.');
        }
    }

    public function joinLiveStreams($id)
    {
        $streamId = null;

        if (is_numeric($id)) {
            $streamId = (int) $id;
        } else {
            try {
                $decrypted = Crypt::decryptString((string) $id);
                if (is_numeric($decrypted)) {
                    $streamId = (int) $decrypted;
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid stream token',
                ], 400);
            }
        }

        $join_live_stream = $streamId ? LiveStreamGumlet::find($streamId) : null;
        if (! $join_live_stream) {
            return response()->json([
                'status' => false,
                'message' => 'Stream not found',
            ], 404);
        }
        $user = User::find($join_live_stream->user_id);
        $playbackKey = null;
        $playbackUrl = trim((string) $join_live_stream->playback_url);

        if ($playbackUrl !== '' && $playbackUrl !== 'N/A') {
            $playbackPath = parse_url($playbackUrl, PHP_URL_PATH) ?: $playbackUrl;
            $playbackUrlParts = array_values(array_filter(explode('/', trim($playbackPath, '/'))));
            $assetKeyIndex = count($playbackUrlParts) - 2;

            if ($assetKeyIndex >= 0 && isset($playbackUrlParts[$assetKeyIndex])) {
                $playbackKey = 'https://play.gumlet.io/embed/live/'.$playbackUrlParts[$assetKeyIndex];
            }
        }

        // Check if stream is private and user is not host
        $currentUser = Auth::user();
        $isHost = $currentUser->id === $join_live_stream->user_id;
        $isSubscribed = false;

        if ($join_live_stream->visibility === 'private' && ! $isHost) {
            $oneTime = PrivateLiveStreamSub::where('pay_user_id', $currentUser->id)
                ->where('stream_id', $join_live_stream->id)
                ->whereIn('status', ['active', 'Active'])
                ->first();

            $monthly = PrivateLiveStreamSub::where('pay_user_id', $currentUser->id)
                ->where('user_streamer_id', $join_live_stream->user_id)
                ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
                ->whereIn('status', ['active', 'Active'])
                ->orderByDesc('created_at')
                ->first();

            $hasMonthly = false;
            if ($monthly) {
                $expiresAt = Carbon::parse($monthly->created_at)->addDays(30);
                $hasMonthly = $expiresAt->isFuture();
            }

            if ($oneTime || $hasMonthly) {
                $isSubscribed = true;
            } else {
                $playbackKey = null;
            }
        } else {
            // Public stream or host
            $isSubscribed = true;
        }

        return response()->json([
            'status' => true,
            'liveStream' => $join_live_stream,
            'playBackKey' => $playbackKey,
            'data' => $user,
            'is_subscribed' => $isSubscribed,
        ]);
    }

    public function inviteGuest(Request $request, LiveStreamGumlet $stream)
    {
        $this->authorize('update', $stream);
        abort_unless($stream->status === 'live', 409, 'Only a live stream can invite guests.');

        $validated = $request->validate([
            'username' => 'required|string',
        ]);

        $invitee = User::where('name', $validated['username'])
            ->orWhere('linkup_id', $validated['username'])
            ->first();

        if (! $invitee) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($invitee->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot invite yourself'], 400);
        }

        // Create notification
        Notification::create([
            'user_id' => $invitee->id,
            'send_by' => Auth::id(),
            'title' => 'Live Stream Invitation',
            'message' => Auth::user()->name.' invited you to join their live stream.',
            'type' => 'live_invite',
            'context' => 'live_stream',
            'metadata' => [
                'stream_id' => $stream->id,
                'host_name' => Auth::user()->name,
                'host_avatar' => Auth::user()->avatar,
            ],
        ]);

        broadcast(new LiveInviteSent([
            'stream_id' => $stream->id,
            'host' => [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'avatar' => Auth::user()->avatar,
            ],
            'invitee_id' => $invitee->id,
            'ts' => now()->toISOString(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent to '.$invitee->name,
            'guest' => [
                'id' => $invitee->id,
                'name' => $invitee->name,
                'avatar' => $invitee->avatar,
                'status' => 'pending',
            ],
        ]);
    }

    public function replyInvite(Request $request, LiveStreamGumlet $stream)
    {
        $validated = $request->validate([
            'accept' => 'required|boolean',
        ]);

        $user = Auth::user();

        $invitation = Notification::query()
            ->where('user_id', $user->id)
            ->where('send_by', $stream->user_id)
            ->where('type', 'live_invite')
            ->where('unread', true)
            ->latest('id')
            ->get()
            ->first(fn (Notification $notification) => (string) ($notification->metadata['stream_id'] ?? '') === (string) $stream->id);

        abort_unless($invitation, 403, 'A valid invitation is required.');

        if ($stream->status !== 'live') {
            return response()->json(['message' => 'Stream has ended.'], 400);
        }

        if ($validated['accept']) {
            $guests = $stream->guests ?? [];
            // Add if not already exists
            $exists = false;
            foreach ($guests as $g) {
                if ($g['id'] == $user->id) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $guests[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'status' => 'joined',
                ];
                $stream->guests = $guests;
                $stream->save();
            }
        }

        broadcast(new LiveInviteReply([
            'stream_id' => $stream->id,
            'public_id' => $stream->public_id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ],
            'accepted' => $validated['accept'],
            'ts' => now()->toISOString(),
        ]));

        $invitation->update(['unread' => false]);

        if ($validated['accept']) {
            return response()->json([
                'success' => true,
                'join_token' => Crypt::encryptString((string) $stream->id),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function removeGuest(Request $request, LiveStreamGumlet $stream)
    {
        $validated = $request->validate([
            'guest_id' => 'required',
        ]);

        if ((string) $request->user()->id !== (string) $validated['guest_id']) {
            $this->authorize('update', $stream);
        }

        $guests = $stream->guests ?? [];
        $newGuests = array_filter($guests, function ($g) use ($validated) {
            return $g['id'] != $validated['guest_id'];
        });

        $stream->guests = array_values($newGuests);
        $stream->save();

        broadcast(new LiveGuestRemoved([
            'stream_id' => $stream->id,
            'public_id' => $stream->public_id,
            'guest_id' => $validated['guest_id'],
            'ts' => now()->toISOString(),
        ]));

        return response()->json(['success' => true]);
    }

    public function subscribe(Request $request, LiveStreamGumlet $stream)
    {
        $user = Auth::user();

        $plan = $request->input('plan', 'monthly');

        // Check if already subscribed
        if ($plan === 'monthly') {
            $existing = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                ->where('user_streamer_id', $stream->user_id)
                ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
                ->whereIn('status', ['active', 'Active'])
                ->orderByDesc('created_at')
                ->first();

            if ($existing) {
                $expiresAt = Carbon::parse($existing->created_at)->addDays(30);
                if ($expiresAt->isFuture()) {
                    return response()->json(['url' => route('frontend.go-live.index')]);
                }
            }
        } else {
            $existing = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                ->where('stream_id', $stream->id)
                ->where('status', 'active')
                ->first();

            if ($existing) {
                return response()->json(['url' => route('frontend.go-live.index')]);
            }
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $price = $stream->subscription_rate;
        // $plan: 'monthly' or 'one-time'

        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Subscription to '.($stream->title ?? 'Private Stream'),
                        'description' => 'Access to private stream by '.$stream->user->name,
                    ],
                    'unit_amount' => (int) ($price * 100),
                ],
                'quantity' => 1,
            ],
        ];

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment', // use 'subscription' if recurring, but for now 'payment' (one-time logic for simplicity as per request "go to stripe and checkout")
                'success_url' => route('frontend.live.subscribe.success').'?session_id={CHECKOUT_SESSION_ID}&stream_id='.$stream->id.'&plan='.$plan,
                'cancel_url' => route('new_frontend.live'),
                'customer_email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'stream_id' => $stream->id,
                    'plan' => $plan,
                ],
            ]);

            return response()->json(['url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unsubscribe user from a creator's monthly private streams
     */
    public function unsubscribe(Request $request)
    {
        $user = Auth::user();
        $userStreamerId = $request->input('user_streamer_id');

        if (! $userStreamerId) {
            return response()->json(['message' => 'Creator ID required'], 400);
        }

        // Delete or deactivate monthly subscription for this creator
        $deleted = PrivateLiveStreamSub::where('pay_user_id', $user->id)
            ->where('user_streamer_id', $userStreamerId)
            ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
            ->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Unsubscribed successfully']);
        }

        return response()->json(['message' => 'No active monthly subscription found'], 404);
    }

    public function subscriptionSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');
        $streamId = $request->get('stream_id');
        $plan = $request->get('plan');
        $user = Auth::user();

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $stream = LiveStreamGumlet::find($streamId);
                if (! $stream) {
                    return redirect()->route('new_frontend.live')->with('error', 'Stream not found.');
                }

                // Record subscription
                if ($plan === 'monthly') {
                    PrivateLiveStreamSub::updateOrCreate(
                        [
                            'pay_user_id' => $user->id,
                            'user_streamer_id' => $stream->user_id,
                            'payment_type' => 'monthly',
                        ],
                        [
                            'stream_id' => $stream->id,
                            'pay_amount' => $session->amount_total / 100,
                            'status' => 'active',
                            'stripe_session_id' => $sessionId,
                        ]
                    );
                } else {
                    PrivateLiveStreamSub::updateOrCreate(
                        [
                            'pay_user_id' => $user->id,
                            'stream_id' => $stream->id,
                        ],
                        [
                            'user_streamer_id' => $stream->user_id,
                            'payment_type' => 'one-time',
                            'pay_amount' => $session->amount_total / 100,
                            'status' => 'active',
                            'stripe_session_id' => $sessionId,
                        ]
                    );
                }

                return redirect()->route('new_frontend.live')->with('success', 'Subscription successful! You can now join the stream.');
            }
        } catch (\Exception $e) {
            return redirect()->route('new_frontend.live')->with('error', 'Payment verification failed.');
        }

        return redirect()->route('new_frontend.live')->with('error', 'Payment incomplete.');
    }

    // ===== Live realtime API =====
    public function sendGift(Request $request, LiveStreamGumlet $stream)
    {
        $this->authorize('joinLiveSession', $stream);
        $validated = $request->validate([
            'gift' => 'required|array',
            'gift.id' => 'required|integer',
            'qty' => 'required|integer|min:1|max:100',
            'idempotency_key' => 'required|uuid',
        ]);

        $sender = Auth::user();
        abort_if((string) $sender->id === (string) $stream->user_id, 422, 'You cannot send a gift to yourself.');
        $gift = LinkupLiveGift::query()->whereKey($validated['gift']['id'])->where('active', true)->firstOrFail();
        $totalCoins = $gift->coins * $validated['qty'];

        try {
            $payload = DB::transaction(function () use ($sender, $gift, $totalCoins, $validated, $stream) {
                $users = User::query()->whereIn('id', [$sender->id, $stream->user_id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $lockedSender = $users->get($sender->id);
                $receiver = $users->get($stream->user_id);

                if (! $lockedSender || ! $receiver || (int) $lockedSender->coins < $totalCoins) {
                    abort(422, 'You do not have enough coins.');
                }

                if (LiveGift::query()->where('idempotency_key', $validated['idempotency_key'])->exists()) {
                    return null;
                }

                $lockedSender->decrement('coins', $totalCoins);

                // The host's revenue is the immutable LiveGift record below.
                // LiveStreamService calculates and releases the host's share;
                // adding spendable viewer coins here would pay the gift twice.

                $liveGift = LiveGift::create([
                    'live_stream_gumlet_id' => $stream->id,
                    'sender_id' => $lockedSender->id,
                    'receiver_id' => $receiver->id,
                    'gift_id' => $gift->id,
                    'linkup_live_gift_id' => $gift->id,
                    'qty' => $validated['qty'],
                    'coins' => $totalCoins,
                    'host_share_cents' => 0,
                    'idempotency_key' => $validated['idempotency_key'],
                ]);

                // If a stream was previously marked paid, new gifts should make it eligible again.
                if ((bool) $stream->is_paid === true) {
                    $stream->is_paid = false;
                    $stream->save();
                }

                $giftCount = LiveGift::where('live_stream_gumlet_id', $stream->id)->sum('qty');

                return [
                    'id' => (string) $liveGift->id,
                    'stream_id' => $stream->id,
                    'public_id' => $stream->public_id,
                    'sender' => [
                        'id' => $lockedSender->id,
                        'name' => $lockedSender->name,
                        'avatar' => $lockedSender->avatar,
                    ],
                    'gift' => [
                        'id' => $gift->id,
                        'name' => $gift->name,
                        'emoji' => $gift->emoji ?? '🎁',
                        'coins' => (int) $gift->coins,
                    ],
                    'qty' => (int) $validated['qty'],
                    'gift_count' => $giftCount,
                    'ts' => now()->toISOString(),
                ];
            });

            if ($payload !== null) {
                broadcast(new LiveGiftSent($payload));
            }

            return response()->json([
                'status' => true,
                'message' => 'Gift sent successfully.',
                'remaining_coins' => $sender->fresh()->coins,
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send gift.',
            ], 500);
        }
    }

    /**
     * Send reaction to live stream
     */
    public function sendReaction(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $this->authorize('joinLiveSession', $stream);
        $validated = $request->validate([
            'type' => 'required|in:heart,bottle',
        ]);

        $user = Auth::user();

        // Prevent host from reacting to their own stream
        if ($stream->user_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot react to your own stream',
            ], 403);
        }

        try {
            // Update like count in stream
            $currentLikeCount = $stream->like_count ?? 0;
            $stream->like_count = $currentLikeCount + 1;
            $stream->save();

            // Broadcast reaction to all viewers
            broadcast(new LiveReactionSent([
                'stream_id' => $stream->id,
                'public_id' => $stream->public_id,
                'type' => $validated['type'],
                'emoji' => $validated['type'] === 'heart' ? '❤️' : '🍾',
                'sender' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ],
                'like_count' => $stream->like_count,
                'ts' => now()->toISOString(),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Reaction sent successfully',
                'like_count' => $stream->like_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reaction: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get stream statistics
     */
    public function getStreamStats(LiveStreamGumlet $stream): JsonResponse
    {
        try {
            // Get current stats from database
            $viewerCount = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('left_at', null)
                ->count();

            $giftCount = LiveGift::where('live_stream_gumlet_id', $stream->id)
                ->sum('qty');

            $likeCount = $stream->like_count ?? 0;

            // Session earnings (unpaid) for host.
            // NOTE: Stream-level `is_paid` is not sufficient because new gifts can arrive after a payout.
            // We use `paid_coins` to compute remaining unpaid coins per stream.
            $totalCoins = 0;

            $streams = LiveStreamGumlet::where('user_id', $stream->user_id)->get();
            foreach ($streams as $s) {
                $streamCoins = (int) LiveGift::where('live_stream_gumlet_id', $s->id)->sum('coins');
                $paidCoins = (int) ($s->paid_coins ?? 0);
                $unpaid = max(0, $streamCoins - $paidCoins);
                $totalCoins += $unpaid;
            }

            $totalCash = $totalCoins * 0.01; // 0.01 conversion rate (100 coins = $1.00)

            return response()->json([
                'ok' => true,
                'viewer_count' => $viewerCount,
                'gift_count' => $giftCount,
                'like_count' => $likeCount,
                'session_coins' => $totalCoins,
                'session_cash' => $totalCash,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Failed to get stream stats: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer session earnings to user wallet
     */
    public function transferSessionEarnings(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $this->authorize('update', $stream);
        $this->authorize('transferEarnings', LiveStreamGumlet::class);
        $validatedPayout = $request->validate(['idempotency_key' => ['required', 'uuid']]);
        $securePayout = app(\App\Services\LiveStreamService::class)
            ->transferEarnings($request->user(), $validatedPayout['idempotency_key']);

        return response()->json($securePayout, $securePayout['success'] ? 200 : 422);

        /* Legacy implementation retained temporarily below for audit history; unreachable. */
        try {
            $user = Auth::user();

            // Verify user is the stream owner
            if ((int) $stream->user_id !== (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            $minCashOut = 50.00;

            $grossAmount = (float) $validated['amount'];
            if ($grossAmount < $minCashOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough — minimum cash-out is $'.number_format($minCashOut, 2),
                ], 422);
            }

            // Calculate total available (unpaid) from all streams for this host.
            $totalAvailableCoins = 0;
            $streams = LiveStreamGumlet::where('user_id', $user->id)->get();

            foreach ($streams as $s) {
                $streamCoins = (int) LiveGift::where('live_stream_gumlet_id', $s->id)->sum('coins');
                $paidCoins = (int) ($s->paid_coins ?? 0);
                $unpaid = max(0, $streamCoins - $paidCoins);
                $totalAvailableCoins += $unpaid;
            }

            $totalAvailableCash = $totalAvailableCoins * 0.01;

            if ($grossAmount > $totalAvailableCash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested amount exceeds available earnings',
                ], 422);
            }

            $userShare = round($grossAmount * 0.5, 2);
            $platformShare = round($grossAmount * 0.5, 2);

            // Convert requested cash to coins to mark as paid.
            // Example: $50.00 => 5000 coins.
            $coinsToPay = (int) round($grossAmount / 0.01);

            // Add amount to user's wallet using O21 wallet system
            deposit($userShare, 'USD')
                ->from(Custodian::of('e_money'))
                ->to($user)
                ->overcharge()
                ->processor('live deposit')
                ->meta([
                    'description' => 'Live stream earnings transfer (50/50 split)',
                    'stream_id' => $stream->id,
                    'gross_amount' => $grossAmount,
                    'user_share' => $userShare,
                    'platform_share' => $platformShare,
                    'split' => '50/50',
                ])
                ->commit();

            // Allocate paid coins across streams (oldest first), without closing the stream permanently.
            // This ensures new gifts after payout remain as unpaid earnings.
            $remainingCoinsToPay = $coinsToPay;
            $streamsForPayout = LiveStreamGumlet::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->get();

            DB::transaction(function () use ($streamsForPayout, &$remainingCoinsToPay) {
                foreach ($streamsForPayout as $s) {
                    if ($remainingCoinsToPay <= 0) {
                        break;
                    }

                    $streamCoins = (int) LiveGift::where('live_stream_gumlet_id', $s->id)->sum('coins');
                    $paidCoins = (int) ($s->paid_coins ?? 0);
                    $unpaid = max(0, $streamCoins - $paidCoins);
                    if ($unpaid <= 0) {
                        continue;
                    }

                    $payHere = min($unpaid, $remainingCoinsToPay);
                    $s->paid_coins = $paidCoins + $payHere;
                    $s->is_paid = ($s->paid_coins >= $streamCoins);
                    $s->save();

                    $remainingCoinsToPay -= $payHere;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Transfer successful!',
                'new_balance' => $user->balance('USD')->value->get(),
                'gross_amount' => $grossAmount,
                'user_share' => $userShare,
                'platform_share' => $platformShare,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user analytics for live streaming
     */
    public function getUserAnalytics(Request $request): JsonResponse
    {
        $this->authorize('viewAnalytics', LiveStreamGumlet::class);
        $request->validate(['range' => ['sometimes', 'in:today,7d,30d,90d']]);

        try {
            $user = Auth::user();
            $range = $request->get('range', '7d');

            $dateLimit = now();
            if ($range === '7d') {
                $dateLimit = now()->subDays(7);
            } elseif ($range === '30d') {
                $dateLimit = now()->subDays(30);
            } elseif ($range === '90d') {
                $dateLimit = now()->subDays(90);
            } elseif ($range === 'today') {
                $dateLimit = now()->startOfDay();
            }

            // 1. Fetch raw sessions
            $rawSessions = LiveStreamGumlet::where('user_id', $user->id)
                ->where('created_at', '>=', $dateLimit)
                ->orderBy('created_at', 'desc')
                ->get();

            $sessionIds = $rawSessions->pluck('id');

            // 2. Pre-fetch Aggregates for Sessions
            // A. Gift Coins per Session
            $sessionCoinsMap = LiveGift::whereIn('live_stream_gumlet_id', $sessionIds)
                ->select('live_stream_gumlet_id', DB::raw('SUM(coins) as total_coins'))
                ->groupBy('live_stream_gumlet_id')
                ->pluck('total_coins', 'live_stream_gumlet_id');

            // B. Subscriptions per Session
            $sessionSubsMap = PrivateLiveStreamSub::whereIn('stream_id', $sessionIds)
                ->select('stream_id', DB::raw('SUM(pay_amount) as total_subs'))
                ->groupBy('stream_id')
                ->pluck('total_subs', 'stream_id');

            // C. Viewers per Session (for Watch Time and Unique Viewers)
            $allViewers = LiveViewer::whereIn('live_stream_gumlet_id', $sessionIds)
                ->get()
                ->groupBy('live_stream_gumlet_id');

            // 3. Build optimized sessions array
            $sessions = $rawSessions->map(function ($s) use ($sessionCoinsMap, $sessionSubsMap, $allViewers) {
                $streamCoins = (int) ($sessionCoinsMap[$s->id] ?? 0);

                $viewers = $allViewers->get($s->id, collect());
                $uniqueViewers = $viewers->unique('user_id')->count();

                $totalWatchSeconds = 0;
                $streamEnd = $s->updated_at ?? now();
                foreach ($viewers as $v) {
                    if ($v->joined_at) {
                        $end = $v->left_at ?? $streamEnd;
                        if ($end > now()) {
                            $end = now();
                        }

                        $seconds = $v->joined_at->diffInSeconds($end, false);
                        if ($seconds > 0) {
                            // Cap at 12 hours (43200 seconds) to prevent runaway watch time if left_at is missing
                            if ($seconds > 43200) {
                                $seconds = 43200;
                            }
                            $totalWatchSeconds += $seconds;
                        }
                    }
                }

                return [
                    'id' => $s->id,
                    'date' => $s->created_at->format('Y-m-d'),
                    'title' => $s->title,
                    'category' => $s->broadcast_type ?? 'General',
                    'pcu' => (int) ($s->max_viewers ?? 0),
                    'watchHours' => round($totalWatchSeconds / 3600, 1),
                    'uniqueViewers' => $uniqueViewers,
                    'coins' => $streamCoins,
                    'cash' => $streamCoins * 0.01,
                    'subs' => (float) ($sessionSubsMap[$s->id] ?? 0),
                ];
            });

            // 4. Gifts (optimized gift metadata fetch)
            $giftAggregates = LiveGift::whereIn('live_stream_gumlet_id', $sessionIds)
                ->whereNotNull('linkup_live_gift_id')
                ->select('linkup_live_gift_id as gift_id', DB::raw('SUM(qty) as count'), DB::raw('SUM(coins) as coins'))
                ->groupBy('linkup_live_gift_id')
                ->get();

            $giftIds = $giftAggregates->pluck('gift_id');
            $giftMetas = LinkupLiveGift::whereIn('id', $giftIds)->get()->keyBy('id');

            $gifts = $giftAggregates->map(function ($g) use ($giftMetas) {
                $giftMeta = $giftMetas->get($g->gift_id);

                return [
                    'gift' => $giftMeta ? $giftMeta->name : '_',
                    'count' => (int) $g->count,
                    'coins' => (int) $g->coins,
                ];
            });

            // 5. Subscriptions (General Stats)
            $subsData = PrivateLiveStreamSub::where('user_streamer_id', $user->id)
                ->where('created_at', '>=', $dateLimit)
                ->get();

            $subscriptions = [
                'active' => $subsData->where('status', 'active')->count(),
                'revenueGross' => (float) $subsData->sum('pay_amount'),
            ];

            // 6. Countries (Already efficient join)
            $countries = DB::table('live_viewers')
                ->join('users', 'live_viewers.user_id', '=', 'users.id')
                ->whereIn('live_viewers.live_stream_gumlet_id', $sessionIds)
                ->select('users.new_country as code', 'users.new_country as name', DB::raw('COUNT(DISTINCT users.id) as viewers'))
                ->groupBy('users.new_country')
                ->get()
                ->map(function ($c) {
                    return [
                        'code' => $c->code ?? '_',
                        'name' => $c->name ?? '_',
                        'viewers' => (int) $c->viewers,
                        'watchHours' => 0,
                    ];
                });

            // 7. Transfers (Audit Trail)
            $transfers = Transaction::where('to_id', $user->id)
                ->where('processor_id', 'live deposit')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($t) {
                    return [
                        'date' => $t->created_at->format('Y-m-d H:i'),
                        'type' => 'Transfer',
                        'ref' => 'WALLET-TRX-'.$t->id,
                        'amount' => (float) $t->amount,
                        'status' => 'Completed',
                    ];
                });

            // 8. Settings
            $settings = [
                'coinToUsd' => 0.01,
                'platformFeeRate' => 0.20,
                'subscriptionSplitHost' => 0.50,
                'subscriptionSplitPartner' => 0.50,
            ];

            // 9. Buckets (for chart - can be further optimized if needed, currently dummy)
            $buckets = [];
            for ($i = 0; $i < 24; $i++) {
                $buckets[] = [
                    't' => sprintf('%02d:00', $i),
                    'concurrent' => rand(10, 100),
                    'watchSeconds' => rand(300, 1000),
                    'chats' => rand(5, 50),
                    'reacts' => rand(10, 100),
                ];
            }

            return response()->json([
                'ok' => true,
                'settings' => $settings,
                'sessions' => $sessions,
                'buckets' => $buckets,
                'gifts' => $gifts,
                'subscriptions' => $subscriptions,
                'countries' => $countries,
                'transfers' => $transfers,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Analytics Error: '.$e->getMessage()."\n".$e->getTraceAsString());

            return response()->json([
                'ok' => false,
                'error' => 'Failed to fetch analytics: '.$e->getMessage(),
            ], 500);
        }
    }

    public function sendComment(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $this->authorize('joinLiveSession', $stream);
        $validated = Validator::make($request->all(), [
            'text' => 'required|string|max:500',
        ])->validate();

        $user = Auth::user();

        $comment = LiveComment::create([
            'live_stream_gumlet_id' => $stream->id,
            'user_id' => $user->id,
            'text' => $validated['text'],
        ]);

        $payload = [
            'id' => (string) $comment->id,
            'stream_id' => $stream->id,
            'public_id' => $stream->public_id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ],
            'text' => $validated['text'],
            'ts' => $comment->created_at->toISOString(),
        ];

        broadcast(new LiveCommentPosted($payload));

        return response()->json([
            'success' => true,
            'ok' => true,
            'id' => (string) $comment->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_avatar' => $user->avatar,
            'timestamp' => $payload['ts'],
        ]);
    }

    public function createPoll(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $this->authorize('update', $stream);
        abort_unless($stream->status === 'live', 409, 'The stream is not live.');
        $validated = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2|max:4',
            'options.*' => 'required|string|max:100',
        ])->validate();

        $poll = LivePoll::create([
            'live_stream_gumlet_id' => $stream->id,
            'question' => $validated['question'],
            'active' => true,
            'active_marker' => 1,
        ]);

        $options = [];
        foreach ($validated['options'] as $i => $text) {
            LivePollOption::create([
                'live_poll_id' => $poll->id,
                'text' => $text,
                'votes' => 0,
            ]);
            $options[] = [
                'id' => $i + 1,
                'text' => $text,
                'votes' => 0,
            ];
        }

        broadcast(new PollCreated([
            'id' => $poll->id,
            'stream_id' => $stream->id,
            'public_id' => $stream->public_id,
            'question' => $validated['question'],
            'options' => $options,
            'ts' => now()->toISOString(),
        ]));

        return response()->json([
            'ok' => true,
            'poll' => [
                'id' => $poll->id,
                'question' => $validated['question'],
                'options' => $options,
                'active' => true,
            ],
        ]);
    }

    public function votePoll(Request $request, LiveStreamGumlet $stream, string $poll): JsonResponse
    {
        $this->authorize('joinLiveSession', $stream);
        $validated = Validator::make($request->all(), [
            'option_id' => 'required|integer|min:1|max:4',
        ])->validate();

        $livePoll = LivePoll::where('id', $poll)
            ->where('live_stream_gumlet_id', $stream->id)
            ->where('active', true)
            ->first();

        if (! $livePoll) {
            return response()->json(['ok' => false, 'message' => 'Poll not found or inactive'], 404);
        }

        $option = LivePollOption::where('live_poll_id', $poll)
            ->orderBy('id')
            ->skip($validated['option_id'] - 1)
            ->first();

        if (! $option) {
            return response()->json(['ok' => false, 'message' => 'Invalid option'], 400);
        }

        try {
            DB::transaction(function () use ($livePoll, $option, $request) {
                DB::table('live_poll_votes')->insert([
                    'live_poll_id' => $livePoll->id,
                    'user_id' => $request->user()->id,
                    'live_poll_option_id' => $option->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                LivePollOption::query()->whereKey($option->id)->lockForUpdate()->increment('votes');
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            if ((string) $exception->getCode() === '23000' || str_contains(strtolower($exception->getMessage()), 'unique')) {
                return response()->json(['ok' => false, 'message' => 'You have already voted.'], 409);
            }
            throw $exception;
        }

        $allOptions = LivePollOption::where('live_poll_id', $poll)
            ->orderBy('id')
            ->get()
            ->map(function ($opt, $index) {
                return [
                    'id' => $index + 1,
                    'text' => $opt->text,
                    'votes' => $opt->votes,
                ];
            })
            ->values()
            ->all();

        broadcast(new PollVoted([
            'poll_id' => (string) $poll,
            'stream_id' => $stream->id,
            'public_id' => $stream->public_id,
            'option_id' => (int) $validated['option_id'],
            'votes' => $allOptions,
            'ts' => now()->toISOString(),
        ]));

        return response()->json(['ok' => true, 'votes' => $allOptions]);
    }

    public function endPoll(Request $request, LiveStreamGumlet $stream, string $poll): JsonResponse
    {
        $this->authorize('update', $stream);
        $livePoll = LivePoll::whereKey($poll)->where('live_stream_gumlet_id', $stream->id)->first();
        $options = LivePollOption::where('live_poll_id', $poll)->get();
        if ($livePoll) {
            foreach ($options as $option) {
                $option->delete();
            }
            $livePoll->delete();
        }

        broadcast(new PollEnded([
            'poll_id' => (string) $poll,
            'stream_id' => (string) $stream->id,
            'public_id' => $stream->public_id,
        ]));

        return response()->json(['ok' => true]);
    }

    public function submitQna(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $this->authorize('joinLiveSession', $stream);
        $validated = Validator::make($request->all(), [
            'text' => 'required|string|max:500',
        ])->validate();
        $user = Auth::user();

        $qna = LiveQna::create([
            'live_stream_gumlet_id' => $stream->id,
            'user_id' => $user->id,
            'text' => $validated['text'],
            'answered' => false,
        ]);

        broadcast(new QnaSubmitted([
            'id' => (string) $qna->id,
            'stream_id' => (string) $stream->id,
            'public_id' => $stream->public_id,
            'from' => ['id' => $user->id, 'name' => $user->name],
            'text' => $validated['text'],
            'timestamp' => $qna->created_at->timestamp,
        ]));

        return response()->json([
            'ok' => true,
            'qna' => [
                'id' => $qna->id,
                'from' => ['id' => $user->id, 'name' => $user->name],
                'text' => $validated['text'],
                'answered' => false,
            ],
        ]);
    }

    public function answerQna(Request $request, LiveStreamGumlet $stream, string $qna): JsonResponse
    {
        $this->authorize('update', $stream);
        $qnaRecord = LiveQna::where('id', $qna)
            ->where('live_stream_gumlet_id', $stream->id)
            ->first();

        if (! $qnaRecord) {
            return response()->json(['ok' => false, 'message' => 'Question not found'], 404);
        }

        $qnaRecord->answered = true;
        $qnaRecord->save();

        broadcast(new QnaAnswered([
            'id' => (string) $qna,
            'stream_id' => (string) $stream->id,
            'public_id' => $stream->public_id,
        ]));

        return response()->json(['ok' => true]);
    }

    public function deleteQna(Request $request, LiveStreamGumlet $stream, string $qna): JsonResponse
    {
        $this->authorize('update', $stream);
        $qnaRecord = LiveQna::where('id', $qna)
            ->where('live_stream_gumlet_id', $stream->id)
            ->first();

        if (! $qnaRecord) {
            return response()->json(['ok' => false, 'message' => 'Question not found'], 404);
        }

        $qnaRecord->delete();

        broadcast(new QnaDeleted([
            'id' => (string) $qna,
            'stream_id' => (string) $stream->id,
            'public_id' => $stream->public_id,
        ]));

        return response()->json(['ok' => true]);
    }

    public function getStreamState(LiveStreamGumlet $stream): JsonResponse
    {
        $this->authorize('joinLiveSession', $stream);
        $activePoll = LivePoll::where('live_stream_gumlet_id', $stream->id)
            ->where('active', true)
            ->with('options')
            ->first();

        $pollData = null;
        if ($activePoll) {
            $votedOptionId = DB::table('live_poll_votes')
                ->where('live_poll_id', $activePoll->id)
                ->where('user_id', request()->user()->id)
                ->value('live_poll_option_id');
            $optionIds = $activePoll->options->pluck('id')->values();
            $pollData = [
                'id' => $activePoll->id,
                'question' => $activePoll->question,
                'options' => $activePoll->options->map(function ($option, $index) {
                    return [
                        'id' => $index + 1,
                        'text' => $option->text,
                        'votes' => $option->votes,
                    ];
                })->values()->all(),
                'active' => true,
                'user_voted_option' => $votedOptionId ? $optionIds->search($votedOptionId) : null,
            ];
        }

        $qnaQuestions = LiveQna::where('live_stream_gumlet_id', $stream->id)
            ->where('answered', false)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($qna) {
                return [
                    'id' => (string) $qna->id,
                    'text' => $qna->text,
                    'from' => [
                        'id' => $qna->user->id,
                        'name' => $qna->user->name,
                    ],
                    'answered' => $qna->answered,
                    'timestamp' => $qna->created_at->timestamp,
                ];
            })
            ->values()
            ->all();

        // Get actual viewer count from LiveViewer table
        $viewerCount = LiveViewer::where('live_stream_gumlet_id', $stream->id)
            ->where('is_active', true)
            ->count();

        // Get actual gift count from LiveGift table
        $giftCount = LiveGift::where('live_stream_gumlet_id', $stream->id)->sum('qty');

        return response()->json([
            'ok' => true,
            'session' => [
                'status' => $stream->status,
                'viewer_count' => $viewerCount,
                'like_count' => 0,
                'gift_count' => $giftCount,
                'earnings_cents' => 0,
                'guests' => $stream->guests ?? [],
            ],
            'active_poll' => $pollData,
            'qna' => $qnaQuestions,
        ]);
    }

    public function getLiveConfig(): JsonResponse
    {
        $user = Auth::user();
        $balance = $user->balance('USD')->value->get();

        return response()->json([
            'ok' => true,
            'broadcast_types' => ['Entertainment', 'Politics', 'Current Events', 'News'],
            'tags_by_type' => [
                'Entertainment' => ['Carnival Vibes', 'Reggae Session', 'Dancehall Party', 'Soca Fever'],
                'Politics' => ['Election Watch', 'Policy Debate'],
                'Current Events' => ['Community Spotlight', 'Breaking Story'],
                'News' => ['Caribbean News', 'Latin American News', 'Regional Update', 'Global Headlines'],
            ],
            'visibility_options' => ['public', 'followers', 'private'],
            'user' => [
                'coins' => (int) $balance,
                'balance' => (int) $balance,
            ],
            'geo' => 'Bahamas',
        ]);
    }

    /**
     * Generate a token for the user.
     *
     * @param  string  $channelName
     * @param  string  $role
     * @return JsonResponse
     */
    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:host,guest,audience'],
        ]);

        $channel = $validated['channel'];
        $user = $request->user();
        $stream = LiveStreamGumlet::query()->where('stream_url', $channel)->firstOrFail();
        $this->authorize('joinLiveSession', $stream);

        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');
        $uid = $user->id;

        $roleName = (string) $validated['role'];
        if (in_array($roleName, ['host', 'guest'], true)) {
            $this->authorize('broadcast', $stream);
        }
        $role = ($roleName === 'host' || $roleName === 'guest') ? 1 : 2;

        $expireTime = time() + 3600;

        $token = RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channel,
            $uid,
            $role,
            $expireTime
        );

        return response()->json([
            'appId' => $appId,
            'token' => $token,
            'uid' => $uid,
        ]);
    }

    public function startAgoraStream(Request $request)
    {
        $user = Auth::user();
        $channelName = 'live_'.$user->id;

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'broadcast_type' => 'nullable|string|max:255',
            'visibility' => 'nullable|in:public,private',
            'location' => 'nullable|string|max:255',
            'subscription_rate' => 'nullable|numeric|min:0|max:9999.99',
            'base_resolution' => 'nullable|string|in:1920x1080,1280x720',
            'output_resolution' => 'nullable|string|in:1920x1080,1280x720,854x480',
            'downscale_filter' => 'nullable|string|in:bicubic,lanczos,bilinear',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp',
            'products' => 'nullable|json',
        ]);

        $products = json_decode($validated['products'] ?? '[]', true);
        $products = is_array($products) ? array_values(array_slice($products, 0, 20)) : [];

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('streams_covers', 'public');
        }

        $streamData = [
            'title' => $validated['title'] ?? 'Live Stream',
            'broadcast_type' => $validated['broadcast_type'] ?? 'Entertainment',
            'visibility' => $validated['visibility'] ?? 'public',
            'subscription_rate' => $validated['subscription_rate'] ?? null,
            'status' => 'live',
            'stream_url' => $channelName,
            'stream_key' => $channelName,
            'start_time' => now(),
            'host_heartbeat_at' => now(),
            'live_asset_id' => $request->live_asset_id ?? 'default_asset_id',
            'live_video_source_id' => $request->live_video_source_id ?? 'default_source_id',
            'resolution' => $validated['output_resolution'] ?? $request->resolution ?? '1080p',
            'playback_url' => $request->playback_url ?? 'N/A',
            'location' => $validated['location'] ?? null,
            'base_resolution' => $validated['base_resolution'] ?? '1920x1080',
            'output_resolution' => $validated['output_resolution'] ?? '1280x720',
            'downscale_filter' => $validated['downscale_filter'] ?? 'bicubic',
            'products' => $products,
        ];

        if ($coverImagePath) {
            $streamData['cover_image'] = $coverImagePath;
        }

        // Always create a new stream (don't update existing ones)
        $streamData['user_id'] = $user->id;
        $stream = LiveStreamGumlet::create($streamData);

        if ($stream?->id) {
            SendNewLiveStreamToFollowersJob::dispatch((int) $stream->id);
        }

        $stream->load('user');

        // Generate Token
        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $uid = $user->id;
        $role = 1; // 1 = Publisher, 2 = Subscriber
        $expireTime = time() + 3600;

        $token = RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $expireTime
        );

        $streamDataForBroadcast = $stream->toArray();
        if ($stream->relationLoaded('user') && $stream->user) {
            $streamDataForBroadcast['user'] = [
                'id' => $stream->user->id,
                'name' => $stream->user->name,
                'avatar' => $stream->user->avatar,
            ];
        }

        // Add per-user subscription flag for the current user (if private)
        if ($stream->visibility === 'private') {
            $currentUser = Auth::user();
            if ($currentUser && $currentUser->id != $stream->user_id) {
                $hasOneTime = PrivateLiveStreamSub::where('pay_user_id', $currentUser->id)
                    ->where('stream_id', $stream->id)
                    ->where('status', 'active')
                    ->exists();

                $monthly = PrivateLiveStreamSub::where('pay_user_id', $currentUser->id)
                    ->where('user_streamer_id', $stream->user_id)
                    ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
                    ->whereIn('status', ['active', 'Active'])
                    ->orderByDesc('created_at')
                    ->first();

                $hasMonthly = false;
                if ($monthly) {
                    $expiresAt = Carbon::parse($monthly->created_at)->addDays(30);
                    $hasMonthly = $expiresAt->isFuture();
                }

                $streamDataForBroadcast['is_subscribed'] = $hasOneTime || $hasMonthly;
            } else {
                $streamDataForBroadcast['is_subscribed'] = true; // host or not logged in
            }
        } else {
            $streamDataForBroadcast['is_subscribed'] = true; // public
        }

        broadcast(new \App\Events\StreamStarted($streamDataForBroadcast));

        return response()->json([
            'appId' => $appId,
            'token' => $token,
            'uid' => $uid,
            'channel' => $channelName,
            'stream_id' => $stream->id,
        ]);
    }

    /**
     * Update stream settings (cover image, video settings, etc.)
     */
    public function updateStreamSettings(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $user = Auth::user();

        if ($stream->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'broadcast_type' => 'nullable|string|max:255',
            'visibility' => 'nullable|in:public,private',
            'location' => 'nullable|string|max:255',
            'subscription_rate' => 'nullable|numeric|min:0|max:9999.99',
            'base_resolution' => 'nullable|string|in:1920x1080,1280x720',
            'output_resolution' => 'nullable|string|in:1920x1080,1280x720,854x480',
            'downscale_filter' => 'nullable|string|in:bicubic,lanczos,bilinear',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp',
            'products' => 'sometimes|array|max:20',
            'products.*.id' => 'required_with:products',
            'products.*.kind' => 'nullable|in:product,event',
            'products.*.title' => 'required_with:products|string|max:255',
            'products.*.price' => 'nullable|numeric|min:0',
            'products.*.featured' => 'nullable|boolean',
            'products.*.image' => 'nullable|string|max:2048',
            'products.*.cover_image' => 'nullable|string|max:2048',
            'products.*.seller' => 'nullable|string|max:255',
            'products.*.organizer_name' => 'nullable|string|max:255',
            'products.*.commission' => 'nullable|numeric|min:0|max:100',
            'products.*.sold' => 'nullable|integer|min:0',
            'products.*.left' => 'nullable|integer|min:0',
        ]);

        $updateData = [];

        if ($request->has('title')) {
            $updateData['title'] = $validated['title'];
        }
        if ($request->has('broadcast_type')) {
            $updateData['broadcast_type'] = $validated['broadcast_type'];
        }
        if ($request->has('visibility')) {
            $updateData['visibility'] = $validated['visibility'];
        }
        if ($request->has('location')) {
            $updateData['location'] = $validated['location'];
        }
        if ($request->has('subscription_rate')) {
            $updateData['subscription_rate'] = $validated['subscription_rate'];
        }

        if ($request->has('base_resolution')) {
            $updateData['base_resolution'] = $validated['base_resolution'];
        }
        if ($request->has('output_resolution')) {
            $updateData['output_resolution'] = $validated['output_resolution'];
            $updateData['resolution'] = $validated['output_resolution'];
        }
        if ($request->has('downscale_filter')) {
            $updateData['downscale_filter'] = $validated['downscale_filter'];
        }
        if ($request->has('products')) {
            $updateData['products'] = array_values($validated['products']);
        }

        if ($request->hasFile('cover_image')) {
            if ($stream->cover_image && Storage::disk('public')->exists($stream->cover_image)) {
                Storage::disk('public')->delete($stream->cover_image);
            }
            $updateData['cover_image'] = $request->file('cover_image')->store('streams_covers', 'public');
        }

        $stream->update($updateData);
        $freshStream = $stream->fresh();

        if (array_key_exists('products', $updateData)) {
            broadcast(new LiveProductsUpdated([
                'public_id' => $freshStream->public_id,
                'products' => $freshStream->products ?? [],
            ]))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'stream' => $freshStream,
        ]);
    }

    public function endAgoraStream(Request $request)
    {
        $user = Auth::user();

        $streamId = $request->input('stream_id');

        if (! $streamId) {
            $rawData = $request->getContent();
            $data = json_decode($rawData, true);
            $streamId = $data['stream_id'] ?? null;
        }

        if (! $streamId) {
            return response()->json([
                'success' => false,
                'message' => 'Stream ID is required',
            ], 400);
        }

        $stream = LiveStreamGumlet::find($streamId);

        if (! $stream) {
            return response()->json([
                'success' => false,
                'message' => 'Stream not found',
            ], 404);
        }

        if ($stream->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        app(LiveStreamLifecycleService::class)->complete($stream);

        return response()->json([
            'success' => true,
            'message' => 'Stream ended successfully',
        ]);
    }

    public function hostHeartbeat(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        $user = Auth::user();

        if ($stream->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($stream->status !== 'live') {
            return response()->json([
                'success' => false,
                'message' => 'Stream is not live',
            ], 409);
        }

        $stream->forceFill([
            'host_heartbeat_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'host_heartbeat_at' => $stream->host_heartbeat_at?->toISOString(),
        ]);
    }

    /**
     * Clean up stale viewers (those who haven't sent heartbeat recently)
     * This can be called by a scheduled job
     */
    public function cleanupStaleViewers(): JsonResponse
    {
        try {

            $staleViewers = LiveViewer::stale(2) // 2 minutes threshold
                ->where('is_active', true)
                ->get();

            $count = 0;
            foreach ($staleViewers as $viewer) {
                $viewer->update([
                    'is_active' => false,
                    'left_at' => now(),
                ]);
                $count++;
            }
            if ($count > 0) {
                $activeStreams = LiveStreamGumlet::where('status', 'live')->get();
                foreach ($activeStreams as $stream) {
                    $streamViewerCount = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                        ->where('is_active', true)
                        ->count();

                    broadcast(new ViewerCountUpdated([
                        'stream_id' => $stream->id,
                        'public_id' => $stream->public_id,
                        'viewer_count' => $streamViewerCount,
                        'action' => 'stale_viewers_cleaned',
                        'cleaned_count' => $count,
                        'timestamp' => now()->toISOString(),
                    ]));
                }
            }

            return response()->json([
                'success' => true,
                'cleaned_count' => $count,
                'message' => "Cleaned up {$count} stale viewers",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup stale viewers',
            ], 500);
        }
    }

    public function toggleFollowStream(Request $request, $streamId)
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $streamHost = User::find($streamId);
            if (! $streamHost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream host not found',
                ], 404);
            }

            $follow = LiveFollower::where('user_id', $user->id)
                ->where('stream_id', $streamId)
                ->first();

            if ($follow) {
                $follow->delete();
                $isFollowing = false;
            } else {
                try {
                    LiveFollower::create([
                        'user_id' => $user->id,
                        'stream_id' => $streamId,
                    ]);
                    $isFollowing = true;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to follow stream: '.$e->getMessage(),
                    ], 500);
                }
            }

            $count = LiveFollower::where('stream_id', $streamId)->count();

            return response()->json([
                'success' => true,
                'is_following' => $isFollowing,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not process follow request: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getStreamFollowers($streamId)
    {
        try {
            $user = Auth::user();

            $isFollowing = LiveFollower::where('user_id', $user->id)
                ->where('stream_id', $streamId)
                ->exists();

            $count = LiveFollower::where('stream_id', $streamId)->count();

            return response()->json([
                'success' => true,
                'is_following' => $isFollowing,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not fetch follower data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle viewer joining a stream
     */
    public function joinViewer(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        try {

            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            if ($stream->user_id == $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Host is not counted as viewer',
                ]);
            }
            $existingViewer = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
            if ($existingViewer) {
                $existingViewer->update([
                    'last_heartbeat' => now(),
                ]);
                $viewerCount = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                    ->where('is_active', true)
                    ->count();
                broadcast(new ViewerCountUpdated([
                    'stream_id' => $stream->id,
                    'public_id' => $stream->public_id,
                    'viewer_count' => $viewerCount,
                    'action' => 'viewer_heartbeat',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                ]));
            } else {

                LiveViewer::create([
                    'live_stream_gumlet_id' => $stream->id,
                    'user_id' => $user->id,
                    'joined_at' => now(),
                    'last_heartbeat' => now(),
                    'is_active' => true,
                ]);
                $viewerCount = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                    ->where('is_active', true)
                    ->count();
                broadcast(new ViewerCountUpdated([
                    'stream_id' => $stream->id,
                    'public_id' => $stream->public_id,
                    'viewer_count' => $viewerCount,
                    'action' => 'viewer_joined',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Viewer joined successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join stream',
            ], 500);
        }
    }

    /**
     * Get active viewers for a stream
     */
    public function getViewers(LiveStreamGumlet $stream): JsonResponse
    {
        try {
            $viewers = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('is_active', true)
                ->with('user:id,name,avatar,linkup_id')
                ->get()
                ->map(function ($viewer) {
                    return $viewer->user;
                });

            return response()->json([
                'success' => true,
                'viewers' => $viewers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch viewers',
            ], 500);
        }
    }

    /**
     * Handle viewer heartbeat to keep them active
     */
    public function viewerHeartbeat(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        try {

            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            if ($stream->user_id == $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Host heartbeat not tracked',
                ]);
            }
            $viewer = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($viewer) {
                $viewer->update([
                    'last_heartbeat' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat received',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process heartbeat',
            ], 500);
        }
    }

    /**
     * Handle viewer leaving a stream
     */
    public function leaveViewer(Request $request, LiveStreamGumlet $stream): JsonResponse
    {
        try {

            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            if ($stream->user_id == $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Host leaving not tracked',
                ]);
            }
            $viewer = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($viewer) {
                $viewer->update([
                    'is_active' => false,
                    'left_at' => now(),
                ]);
                $viewerCount = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                    ->where('is_active', true)
                    ->count();

                broadcast(new ViewerCountUpdated([
                    'stream_id' => $stream->id,
                    'public_id' => $stream->public_id,
                    'viewer_count' => $viewerCount,
                    'action' => 'viewer_left',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Viewer left successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave stream',
            ], 500);
        }
    }

    public function goLiveNetwork()
    {
        $user = Auth::user();
        $all_Streams = LiveStreamGumlet::where('status', 'live')->get();

        // Check subscription status for each stream
        $all_Streams->each(function ($stream) use ($user) {
            // Add stats to each stream
            $stream->viewer_count = LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('left_at', null)
                ->count();

            $stream->gift_count = LiveGift::where('live_stream_gumlet_id', $stream->id)
                ->sum('qty');

            $stream->like_count = $stream->like_count ?? 0;

            $stream->is_subscribed = false;

            // Host is always subscribed
            if ($stream->user_id == $user->id) {
                $stream->is_subscribed = true;

                return;
            }

            if ($stream->visibility === 'private') {
                $hasOneTime = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                    ->where('stream_id', $stream->id)
                    ->where('status', 'active')
                    ->exists();

                $monthly = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                    ->where('user_streamer_id', $stream->user_id)
                    ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
                    ->whereIn('status', ['active', 'Active'])
                    ->orderByDesc('created_at')
                    ->first();

                $hasMonthly = false;
                if ($monthly) {
                    $expiresAt = Carbon::parse($monthly->created_at)->addDays(30);
                    $hasMonthly = $expiresAt->isFuture();
                }

                if ($hasOneTime || $hasMonthly) {
                    $stream->is_subscribed = true;
                }
            } else {
                $stream->is_subscribed = true; // Public streams are open
            }
        });

        $live_stream_categories = LiveStreamCategories::query()->orderBy('category')->get();

        return Inertia::render('User/GoLive/GoLiveNetwork', [
            'all_Streams' => $all_Streams,
            'live_stream_categories' => $live_stream_categories,
        ]);
    }
}
