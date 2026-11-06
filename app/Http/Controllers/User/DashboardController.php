<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\LinkUpEvent;
use App\Models\Asue;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceAffiliatePromotion;
use App\Models\Notification;
use App\Models\GiftCoins;
use App\Models\GiftPurchase;
use App\Models\News;
use App\Models\Order;
use App\Models\SubscribedPlan;
use App\Models\TicketSale;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserMoneyRequest;
use App\Models\UserCustomPublisher;
use App\Models\UserMatch;
use App\Models\UserReel;
use App\Models\Vibe;
use App\Domain\Vibes\Enums\VibeStatus;
use App\Domain\Vibes\Enums\VibeMediaType;
use Illuminate\Support\Facades\Storage;
use App\Repositories\NewsRepository;
use App\Support\CountryFlag;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use O21\LaravelWallet\Models\Custodian;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function home()
    {
        $user = Auth::user();
        $upcomingEvents = $this->homeUpcomingEvents();
        $liveSessions = $this->homeLiveSessions();

        return Inertia::render('User/Home/Index', [
            'user' => $this->homeUserData($user),
            'wallet' => $this->homeWalletData($user),
            'featEvent' => $upcomingEvents->first(),
            'upcomingEvents' => $upcomingEvents->slice(1, 3)->values(),
            'featLive' => $liveSessions->first(),
            'activeLives' => $liveSessions,
            'featProduct' => $this->homeFeaturedProduct(),
            'weather' => $this->homeWeather($user),
        ]);
    }

    protected function homeUserData(?\App\Models\User $user): array
    {
        if (! $user) {
            return [
                'name' => 'Guest',
                'avatar' => null,
                'city' => '',
                'country' => '',
                'flag' => '',
            ];
        }

        $country = $user->new_country ?? $user->country;

        return [
            'name' => $user->name,
            'avatar' => $user->avatar,
            'city' => $user->new_city ?? $user->city,
            'country' => $country,
            'flag' => CountryFlag::emoji($country),
        ];
    }

    protected function homeWalletData(?\App\Models\User $user): array
    {
        if (! $user) {
            return ['balance' => 0, 'coins' => 0];
        }

        return [
            'balance' => (float) ($user->balance('USD')->value->get() ?? 0),
            'coins' => (int) ($user->coins ?? 0),
        ];
    }

    protected function homeWeather(?\App\Models\User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $lat = $user->latitude;
        $lon = $user->longitude;

        if (! $lat || ! $lon) {
            $city = $user->new_city ?? $user->city;

            if (! $city) {
                return null;
            }

            $geo = Cache::remember('geocode:' . strtolower($city), now()->addDay(), function () use ($city) {
                try {
                    $response = Http::timeout(5)->connectTimeout(3)->get('https://geocoding-api.open-meteo.com/v1/search', [
                        'name' => $city,
                        'count' => 1,
                    ]);

                    return $response->successful() ? $response->json('results.0') : null;
                } catch (\Throwable $e) {
                    return null;
                }
            });

            if (! $geo) {
                return null;
            }

            $lat = $geo['latitude'];
            $lon = $geo['longitude'];
        }

        $current = Cache::remember("weather:{$lat}:{$lon}", now()->addMinutes(30), function () use ($lat, $lon) {
            try {
                $response = Http::timeout(5)->connectTimeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'temperature_2m,weather_code',
                    'temperature_unit' => 'celsius',
                ]);

                return $response->successful() ? $response->json('current') : null;
            } catch (\Throwable $e) {
                return null;
            }
        });

        if (! $current) {
            return null;
        }

        return [
            'temp' => (int) round($current['temperature_2m']),
            'icon' => $this->weatherIcon($current['weather_code'] ?? null),
        ];
    }

    protected function weatherIcon(?int $code): string
    {
        return match (true) {
            $code === 0 => '☀️',
            in_array($code, [1, 2], true) => '🌤️',
            $code === 3 => '☁️',
            in_array($code, [45, 48], true) => '🌫️',
            in_array($code, [51, 53, 55, 56, 57], true) => '🌦️',
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82], true) => '🌧️',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => '🌨️',
            in_array($code, [95, 96, 99], true) => '⛈️',
            default => '⛅',
        };
    }

    protected function homeUpcomingEvents()
    {
        return LinkUpEvent::with(['eventDetails', 'tickets'])
            ->activeTodayAndFuture()
            ->get()
            ->sortBy(fn (LinkUpEvent $event) => $this->eventDate($event) ?? now()->addCentury())
            ->take(4)
            ->map(function (LinkUpEvent $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'location' => collect([$event->venue, $event->city])->filter()->implode(', '),
                    'image' => $event->image_url,
                    'date' => optional($this->eventDate($event))->format('M d, Y'),
                    'price' => $event->is_free ? 0 : (float) ($event->tickets->min('price') ?? 0),
                ];
            })
            ->values();
    }

    protected function eventDate(LinkUpEvent $event): ?\Illuminate\Support\Carbon
    {
        $details = $event->eventDetails;

        if (! $details) {
            return null;
        }

        return $details->event_type === 'recurring' ? $details->recurr_start_date : $details->single_event_date;
    }

    protected function homeLiveSessions()
    {
        return \App\Models\LiveStreamGumlet::with('user')
            ->where('status', 'live')
            ->latest()
            ->get()
            ->sortByDesc(fn ($live) => filled($live->cover_image))
            ->take(3)
            ->map(function ($live) {
                return [
                    'id' => $live->id,
                    'title' => $live->title,
                    'host' => optional($live->user)->name,
                    'thumb' => $live->image_url,
                    'status' => $live->status,
                    'viewers' => $live->viewer_count,
                ];
            })
            ->values();
    }

    protected function homeFeaturedProduct(): ?array
    {
        $product = MarketplaceProduct::where('status', true)->latest()->first();

        if (! $product) {
            return null;
        }

        return [
            'image' => $this->resolveStorageImage($product->cover_image) ?? $product->image_url,
        ];
    }

    protected function resolveStorageImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        while (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
            $path = ltrim($path, '/');
        }

        return asset('storage/' . $path);
    }

    public function notifications()
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', Auth::id())->latest()->get();

        $senderIds = $notifications->pluck('send_by')->filter(fn ($id) => is_numeric($id))->unique()->values();
        $senders = \App\Models\User::whereIn('id', $senderIds)->get(['id', 'avatar'])->keyBy('id');

        $mutedCategories = $this->mutedCategories($user);

        $notifications = $notifications
            ->map(function (Notification $n) use ($senders) {
                $sender = is_numeric($n->send_by) ? $senders->get((int) $n->send_by) : null;
                $metadata = is_array($n->metadata) ? $n->metadata : json_decode($n->metadata ?? '{}', true);
                $origin = null;
                if ($n->type === 'gift' || $this->notificationCategory($n->type, $n->context) === 'Gifts') {
                    if (!empty($metadata['vibe_id']) || in_array($n->context, ['vibe_bigup', 'say_thank_you'], true)) {
                        $origin = 'From Vibes';
                    } elseif (!empty($metadata['stream_id']) || str_contains(strtolower($n->message), 'live')) {
                        $origin = 'From Live';
                    } else {
                        $origin = 'Direct';
                    }
                }

                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'category' => $this->notificationCategory($n->type, $n->context),
                    'origin' => $origin,
                    'unread' => (bool) $n->unread,
                    'priority' => (bool) $n->priority,
                    'avatar' => $sender?->avatar ?? $n->avatar,
                    'timeAgo' => optional($n->created_at)->diffForHumans(null, true) . ' ago',
                ];
            })
            ->reject(fn (array $n) => in_array($n['category'], $mutedCategories, true))
            ->values();

        return Inertia::render('User/Notifications/Index', [
            'notifications' => $notifications,
            'settings' => $this->notificationSettings($user),
        ]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        Notification::where('user_id', Auth::id())->where('unread', true)->update(['unread' => false]);

        return back();
    }

    public function markNotificationRead(Request $request, Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['unread' => false]);
        }

        return back();
    }

    public function saveNotificationSettings(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return back();
        }

        $data = $request->validate([
            'quiet_hours_from' => ['nullable', 'date_format:H:i'],
            'quiet_hours_to' => ['nullable', 'date_format:H:i'],
            'allow_priority' => ['boolean'],
            'mute_messages' => ['boolean'],
            'mute_matches' => ['boolean'],
            'mute_payments' => ['boolean'],
            'mute_gifts' => ['boolean'],
            'mute_system' => ['boolean'],
        ]);

        $user->update([
            'quiet_hours_from' => $data['quiet_hours_from'] ?? null,
            'quiet_hours_to' => $data['quiet_hours_to'] ?? null,
            'allow_priority_notification' => $data['allow_priority'] ?? true,
            'new_message_notification' => ! ($data['mute_messages'] ?? false),
            'new_match_notification' => ! ($data['mute_matches'] ?? false),
            'mute_payment_notification' => $data['mute_payments'] ?? false,
            'mute_gift_notification' => $data['mute_gifts'] ?? false,
            'mute_system_notification' => $data['mute_system'] ?? false,
        ]);

        return back();
    }

    protected function notificationSettings(?\App\Models\User $user): array
    {
        if (! $user) {
            return [
                'quiet_hours_from' => '22:00',
                'quiet_hours_to' => '07:00',
                'allow_priority' => true,
                'mute_messages' => false,
                'mute_matches' => false,
                'mute_payments' => false,
                'mute_gifts' => false,
                'mute_system' => false,
            ];
        }

        return [
            'quiet_hours_from' => optional($user->quiet_hours_from)->format('H:i') ?? '22:00',
            'quiet_hours_to' => optional($user->quiet_hours_to)->format('H:i') ?? '07:00',
            'allow_priority' => (bool) $user->allow_priority_notification,
            'mute_messages' => ! $user->new_message_notification,
            'mute_matches' => ! $user->new_match_notification,
            'mute_payments' => (bool) $user->mute_payment_notification,
            'mute_gifts' => (bool) $user->mute_gift_notification,
            'mute_system' => (bool) $user->mute_system_notification,
        ];
    }

    protected function mutedCategories(?\App\Models\User $user): array
    {
        if (! $user) {
            return [];
        }

        $muted = [];
        if (! $user->new_message_notification) $muted[] = 'Messages';
        if (! $user->new_match_notification) $muted[] = 'Matches';
        if ($user->mute_payment_notification) $muted[] = 'Payments';
        if ($user->mute_gift_notification) $muted[] = 'Gifts';
        if ($user->mute_system_notification) $muted[] = 'System';

        return $muted;
    }

    protected function notificationCategory(?string $type, ?string $context = null): string
    {
        if (in_array($context, ['vibe_bigup', 'reel_bigup', 'say_thank_you', 'vibe_comment', 'vibe_like'], true) || str_starts_with((string) $context, 'vibe_')) {
            return 'Vibes';
        }

        return match (strtolower((string) $type)) {
            'message', 'quick_reply' => 'Messages',
            'match', 'like' => 'Matches',
            'payment', 'payemnt', 'asue invitation', 'money request' => 'Payments',
            'marketplace_order' => 'Marketplace Orders',
            'gift' => 'Gifts',
            'friend_request', 'friend_request_accepted' => 'Requests',
            'live_invite' => 'Events',
            default => 'System',
        };
    }

    public function vibes()
    {
        $user = request()->user();

        // Same posts and shape as the app's feed (GET vibes/posts): visibility, blocked users and
        // posts this member hid are filtered out. `?tag=fete` shows one hashtag.
        $tag = strtolower(ltrim(trim((string) request()->query('tag', '')), '#'));
        $formattedVibes = \App\Support\VibeFeedPresenter::query($user)
            ->when($tag !== '', fn ($q) => $q->whereIn('id', DB::table('vibe_hashtags')->where('tag', $tag)->select('vibe_id')))
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($vibe) {
                $row = \App\Support\VibeFeedPresenter::present($vibe);
                $row['avatar'] = $row['avatar'] ?: 'https://i.pravatar.cc/120?u='.$vibe->created_by;

                return $row;
            });

        // A shared link (`/user/vibes?id=12`, from "Copy link" on the web or the app)
        // puts that post first, when this member may see it.
        $sharedId = (int) request()->query('id', 0);
        if ($sharedId > 0) {
            $shared = \App\Support\VibeFeedPresenter::query($user)->whereKey($sharedId)->first();
            if ($shared) {
                $row = \App\Support\VibeFeedPresenter::present($shared);
                $row['avatar'] = $row['avatar'] ?: 'https://i.pravatar.cc/120?u='.$shared->created_by;
                $formattedVibes = $formattedVibes->reject(fn ($v) => $v['id'] === $sharedId)->prepend($row)->values();
            }
        }

        $customPublishers = UserCustomPublisher::where('user_id', $user->id)->get();

        // Trending tags: what people actually tag on posts this member can see, last 30 days, with counts.
        $trendingTags = DB::table('vibe_hashtags')
            ->whereIn('vibe_id', \App\Support\VibeFeedPresenter::base($user)->where('vibes.created_at', '>=', now()->subDays(30))->select('vibes.id'))
            ->select('tag', DB::raw('count(*) as count'))
            ->groupBy('tag')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['tag' => '#'.$row->tag, 'count' => (int) $row->count])
            ->values()
            ->all();

        $totalBigUpCoins = \App\Models\GiftCoins::where('recieved_id', $user->id)
            ->whereNotNull('vibe_id')
            ->sum('coins');

        $bigUpValue = (float) ($totalBigUpCoins * 0.01);

        // Fetch Real Affiliate Earnings
        $affEarnings = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->latest()
            ->get();

        $pendingAff = (float) $affEarnings->where('status', 'pending')->sum('commission_amount');
        $availableAff = (float) $affEarnings->whereIn('status', ['released', 'available'])->sum('commission_amount');
        $paidAff = (float) $affEarnings->where('status', 'paid')->sum('commission_amount');

        $earningsStats = [
            'sales_driven' => $affEarnings->count(),
            'total_commission' => (float) $affEarnings->sum('commission_amount'),
            'pending' => $pendingAff,
            'available' => $availableAff,
            'paid' => $paidAff,
            'recent' => $affEarnings->take(20)->map(fn ($e) => $e->summaryRow())
        ];

        $affiliateProducts = MarketplaceProduct::with(['seller:id,name,avatar'])
            ->where('status', true)
            ->where('user_id', '!=', $user->id)
            ->where(function ($query) {
                $query->where('commMode', 'flat')->where('commFlat', '>', 0)
                    ->orWhere('commMode', 'pct')->where('commission', '>', 0);
            })
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'kind' => 'product',
                'title' => $p->name,
                'price' => (float) $p->price,
                'image' => $this->resolveStorageImage($p->cover_image) ?? $p->image_url,
                'seller' => $p->seller?->name ?? 'Store',
                'commMode' => $p->commMode,
                'commission' => (float) $p->commission,
                'commFlat' => (float) $p->commFlat,
            ]);

        $affiliateEvents = LinkUpEvent::with(['eventDetails', 'user', 'tickets'])
            ->activeTodayAndFuture()
            ->where('user_id', '!=', $user->id)
            ->whereHas('tickets', function ($query) {
                $query->where(function ($q) {
                    $q->where('commMode', 'flat')->where('commFlat', '>', 0)
                        ->orWhere('commMode', 'pct')->where('commission', '>', 0);
                });
            })
            ->get()
            ->map(function ($e) {
                $ticket = $e->tickets->first(fn ($t) =>
                    ($t->commMode === 'flat' && $t->commFlat > 0) ||
                    ($t->commMode === 'pct' && $t->commission > 0)
                );

                return [
                    'id' => $e->id,
                    'kind' => 'event',
                    'title' => $e->title,
                    'price' => $e->is_free ? 0 : (float) ($e->tickets->min('price') ?? 0),
                    'date' => $this->eventDate($e)?->toISOString(),
                    'location' => $e->venue,
                    'organizer' => $e->user?->name ?? 'Organizer',
                    'commMode' => $ticket?->commMode ?? 'none',
                    'commission' => (float) ($ticket?->commission ?? 0),
                    'commFlat' => (float) ($ticket?->commFlat ?? 0),
                    'image' => $e->image_url,
                ];
            });

        // Followed users plus current user for stories
        $followedUserIds = \App\Models\Frontend\FriendRequest::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
            ->orWhere('receiver_id', $user->id);
        })
            ->where('status', 1)
            ->get()
            ->map(fn ($r) => (int) (
                $r->user_id == $user->id
                    ? $r->receiver_id
                    : $r->user_id
            ))
            ->push((int) $user->id)
            ->toBase()
            ->merge(CreatorFollower::followedIds($user->id))
            ->unique()
            ->values()
            ->all();

        // Stories bar: one circle per followed creator with a reel in the last 24 hours.
        $stories = \App\Support\ReelPresenter::stories($user, $followedUserIds);
        $myReelsCount = UserReel::active()->where('user_id', $user->id)->count();

        // Reels for the reels viewer: newest the member may see (no blocked creators), capped.
        $allReels = \App\Support\ReelPresenter::query($user)
            ->take(60)
            ->get()
            ->map(fn ($reel) => \App\Support\ReelPresenter::present($reel, $user))
            ->values();

        $followingUserIds = CreatorFollower::followedIds($user->id);

        $suggestedCreators = User::where(function ($query) {
                $query->whereHas('vibes')
                      ->orWhereHas('reels');
            })
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', \App\Models\BlockedUser::query()->where('user_id', $user->id)->where('is_blocked', true)->select('blocked_user_id'))
            ->whereNotIn('id', \App\Models\BlockedUser::query()->where('blocked_user_id', $user->id)->where('is_blocked', true)->select('user_id'))
            ->latest()
            ->limit(60)
            ->get(['id', 'name', 'linkup_id', 'avatar'])
            ->map(function ($u) use ($followingUserIds) {
                return [
                    'user_id' => $u->id,
                    'handle' => $u->linkup_id ?? $u->name ?? 'User',
                    'name' => $u->name,
                    'avatar' => $u->avatar ?: ('https://i.pravatar.cc/150?u=' . $u->id),
                    'is_following' => in_array((int) $u->id, $followingUserIds, true),
                ];
            })
            ->values();

        $giftsReceived = $this->getGiftsReceived($user);

        return Inertia::render('User/Vibes/Index', [
            'shopFee' => app(\App\Repositories\ShopFeeRepository::class)->getFee(),
            'vibes' => $formattedVibes,
            'stories' => $stories,
            'myReelsCount' => $myReelsCount,
            'allReels' => $allReels,
            'activeTag' => $tag !== '' ? '#'.$tag : null,
            'suggestedCreators' => $suggestedCreators,
            'vibePublishers' => [
                'organizations' => $user->organizerProfile()->get(['id', 'organizer_name'])
                    ->map(fn ($organization) => ['id' => $organization->id, 'name' => $organization->organizer_name, 'type' => 'organization']),
                'groups' => $user->clubFetes()->wherePivot('is_active', true)
                    ->wherePivotIn('role', ['owner', 'admin'])->where('club_fetes.status', true)
                    ->get(['club_fetes.id', 'club_fetes.name'])
                    ->map(fn ($group) => ['id' => $group->id, 'name' => $group->name, 'type' => 'group']),
                'custom' => $customPublishers->map(fn ($cp) => ['id' => $cp->id, 'name' => $cp->name, 'type' => $cp->type]),
            ],
            'bigUpEarnings' => 0,
            'affiliateItems' => $affiliateProducts->concat($affiliateEvents),
            'earningsStats' => $earningsStats,
            'trendingTags' => $trendingTags,
            'gifts_received' => $giftsReceived,
        ]);
    }

    public function storeCustomPublisher(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:organization,group',
        ]);

        $publisher = UserCustomPublisher::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'type' => $data['type'],
        ]);

        return response()->json([
            'id' => $publisher->id,
            'name' => $publisher->name,
            'type' => $publisher->type,
        ]);
    }

    public function uvibe()
    {
        $user = Auth::user();
        $affiliateItems = $this->getAffiliateItems($user);
        $earningsStats = $this->getEarningsStats($user);

        $vibes = Vibe::with(\App\Services\UVibeFeedService::RELATIONS)
            ->where('is_uvibe', true)
            ->where('status', VibeStatus::Published)
            // Posts this member chose to hide ("⋯ → Hide this post") stay out of their feed.
            ->whereNotIn('id', app(\App\Services\UVibeFeedService::class)->hiddenBy($user))
            ->latest()
            ->get();

        // Same post shape as the mobile API's U Vibe feed (verified badge, alumni badge, organization badge…).
        $formattedVibes = app(\App\Services\UVibeFeedService::class)->presentMany($vibes, $user);

        // Organizations the member has added (name, org type, emoji) — same list the mobile API returns.
        $customPublishers = app(\App\Services\UVibeOrganizationService::class)->list($user);

        $organizerProfiles = \App\Models\OrganizerProfile::where('user_id', $user->id)->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->organizer_name,
            'type' => 'organization',
        ]);

        $userEvents = \App\Models\LinkUpEvent::where('user_id', $user->id)->get()->map(fn($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'price' => $e->tickets->min('price') ?? 0,
            'date' => \App\Support\VibeFeedPresenter::eventDate($e)?->format('Y-m-d'),
            'venue' => $e->venue,
            'image' => $e->image_url,
        ]);

        $followedUserIds = \App\Models\Frontend\FriendRequest::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
            ->orWhere('receiver_id', $user->id);
        })
        ->where('status', 1)
        ->get()
        ->map(fn ($r) => (int) (
            $r->user_id == $user->id
                ? $r->receiver_id
                : $r->user_id
        ))
        ->push((int) $user->id)
        ->toBase()
        ->merge(\App\Models\CreatorFollower::followedIds($user->id))
        ->unique()
        ->values()
        ->all();

        $stories = \App\Support\ReelPresenter::stories($user, $followedUserIds);
        $myReelsCount = \App\Models\UserReel::active()->where('user_id', $user->id)->count();
        $allReels = \App\Support\ReelPresenter::query($user)
            ->take(60)
            ->get()
            ->map(fn ($reel) => \App\Support\ReelPresenter::present($reel, $user))
            ->values();

        return Inertia::render('User/UVibe/Index', [
            'affiliateItems' => $affiliateItems,
            'earningsStats' => $earningsStats,
            'stats' => $earningsStats,
            'uvibes' => $formattedVibes,
            'stories' => $stories,
            'myReelsCount' => $myReelsCount,
            'allReels' => $allReels,
            'campusState' => app(\App\Services\UniversityService::class)->state($user),
            'publishers' => [
                'organizations' => $organizerProfiles,
                'custom' => $customPublishers,
            ],
            'userEvents' => $userEvents,
            'composeOptions' => \App\Support\UVibeOptions::all(),
        ]);
    }

    public function eats()
    {
        return Inertia::render('User/Eats/Index');
    }

    public function dating()
    {
        return Inertia::render('User/Linkup/Index');
    }

    public function events()
    {
        return Inertia::render('User/Events/Index');
    }

    public function nightlife()
    {
        return Inertia::render('User/NightLife/Index');
    }

    public function news(Request $request, NewsRepository $repository)
    {
        $data = $repository->getAll($request);

        return Inertia::render('User/Caribbean360/Index', [
            'newsItems' => $data['news'],
            'newsSources' => $data['news_source'],
            'filters' => $data['filters'],
        ]);
    }

    public function marketplace()
    {
        $user = Auth::user();

        $products = MarketplaceProduct::with(['seller:id,name,avatar', 'category:id,name'])
            ->where('status', true)
            ->latest()
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->name,
                    'price' => (float) $p->price,
                    'image' => $p->cover_image ?? $p->image_url ?? null,
                    'seller' => $p->seller?->name ?? 'Seller',
                    'seller_id' => $p->seller?->id ?? null,
                    'seller_obj' => $p->seller ? [
                        'id' => $p->seller->id,
                        'name' => $p->seller->name,
                        'avatar' => $p->seller->avatar ?? null,
                        'city' => $p->seller->new_city ?? $p->seller->city ?? null,
                        'country' => $p->seller->new_country ?? $p->seller->country ?? null,
                        'pickup_locations' => $this->getPickupLocations($p),
                    ] : null,
                    'seller_type' => $p->listing_type ?? $p->seller_owner ?? 'Store',
                    'category' => $p->category?->name ?? null,
                    'stock' => $p->qty ?? 0,
                    'commMode' => $p->commMode,
                    'commission' => (float) $p->commission,
                    'commFlat' => (float) $p->commFlat,
                    'fulfil' => $p->listing_type ? ($p->listing_type === 'Store' ? 'Pickup available' : 'Delivery only') : 'Pickup available',
                    'desc' => $p->description ?? null,
                    'pickup_locations' => $this->getPickupLocations($p),
                ];
            });

        // Admin-managed categories: active only, featured first (Commerce → Marketplace → Categories).
        $categories = \App\Models\ProductCategory::where('status', 1)
            ->orderByDesc('is_featured')->orderBy('name')
            ->get()->map(function ($c) {
                return ['id' => $c->id, 'name' => $c->name, 'is_featured' => (bool) $c->is_featured];
            });

        $favoriteSellerIds = $user->favoriteSellers()->pluck('seller_id')->toArray();
        $feeRepo = app(\App\Repositories\ShopFeeRepository::class);
        $promotionProductIds = MarketplaceAffiliatePromotion::query()
            ->where('user_id', $user->id)->pluck('product_id')->all();
        $affiliatePromotions = $products->whereIn('id', $promotionProductIds)->values();

        $merchants = \App\Models\Merchants::where('user_id', $user->id)->where('is_active', 1)->get();
        $userProducts = \App\Models\Product::withCount('orderItems')->where('user_id', $user->id)->get();

        // Seller Hub Data
        $analyticsRepo = app(\App\Repositories\SellerAnalyticsRepository::class);
        $earningRepo = app(\App\Repositories\SellerEarningRepository::class);

        $summary = $analyticsRepo->getSummaryStats($user->id);
        $available = $earningRepo->getAvailableEarnings($user->id);
        $pending = $earningRepo->getPendingEarnings($user->id);

        // Fetch real orders for the seller hub
        $sellerOrders = \App\Models\Order::whereHas('items', function ($q) use ($user) {
                $q->whereHas('product', function ($pq) use ($user) {
                    $pq->where('user_id', $user->id);
                });
            })
            ->with(['items' => function($q) use ($user) {
                $q->whereHas('product', function($pq) use ($user) {
                    $pq->where('user_id', $user->id);
                })->with('product');
            }, 'customer'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function($o) {
                $firstItem = $o->items->first();
                return [
                    'id' => $o->number ?? ('ORD-' . $o->id),
                    'item' => $firstItem && $firstItem->product ? $firstItem->product->name : 'Multiple Items',
                    'buyer' => $o->customer?->name ?? 'Unknown Buyer',
                    'units' => $o->items->sum('qty'),
                    'total' => (float) $o->net_total,
                    'status' => ucfirst($o->status),
                ];
            });

        return Inertia::render('User/Marketplace/Index', [
            'products' => $products,
            'categories' => $categories,
            'favoriteSellerIds' => $favoriteSellerIds,
            'shopFee' => $feeRepo->getFee(),
            'promotionProductIds' => $promotionProductIds,
            'affiliatePromotions' => $affiliatePromotions,
            'merchants' => $merchants,
            'userProducts' => $userProducts,
            'sellerHub' => [
                'revenue' => $summary['total_revenue'],
                'unitsSold' => $summary['units_sold'],
                'orderCount' => $summary['total_orders'],
                'inventoryCount' => $userProducts->sum('qty'),
                'earnings' => [
                    'available' => $available,
                    'pending' => $pending,
                    'paidOut' => (float) $userProducts->sum('paid_coins') * 0.01,
                ],
                'orders' => $sellerOrders,
            ]
        ]);
    }

    protected function getPickupLocations($product): array
    {
        $locations = [];
        if ($product->listing_type === 'Store' || $product->listing_type === 'Carnival Group') {
            $merchant = \App\Models\Merchants::find($product->seller_owner);
            if ($merchant && $merchant->pickup_locations) {
                if (is_array($merchant->pickup_locations)) {
                    $locations = $merchant->pickup_locations;
                } else {
                    try {
                        $locations = json_decode($merchant->pickup_locations, true) ?: [$merchant->pickup_locations];
                    } catch (\Exception $e) {
                        $locations = [$merchant->pickup_locations];
                    }
                }
            }
        } elseif ($product->listing_type === 'Administrative') {
            $locations = ['LinkUp HQ Pickup'];
        }

        return array_values(array_filter((array)$locations));
    }

    public function wallet()
    {
        $user = Auth::user();

        return Inertia::render('User/Wallet/Index', [
            'wallet' => $this->homeWalletData($user),
            'contacts' => $this->walletContacts($user),
            'users' => $this->walletContactUsers(),
            'moneyRequests' => $this->walletMoneyRequests($user),
            'activity' => $this->walletActivity($user),
            'subscriptions' => $this->walletSubscriptions($user),
            'bankWithdrawals' => $this->walletBankWithdrawals($user),
            'activeAsue' => $this->walletActiveAsue($user),
            // Saved banks (masked), withdrawal fee, KYC status, PIN set?
            ...($user ? \App\Domain\Wallet\WalletPageData::forUser($user) : []),
        ]);
    }

    /**
     * Return the current user's in-progress ASUE with the same member and
     * invitation information used by the original wallet experience.
     */
    protected function walletActiveAsue(?User $user): ?Asue
    {
        if (! $user) {
            return null;
        }

        return Asue::whereIn('status', ['active', 'ACTIVE', 'inviting', 'INVITING'])
            ->whereNotNull('asue_unique_code')
            ->whereHas('invitedUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['invitedUsers' => function ($query) {
                $query->orderBy('asue_invites.position', 'asc');
            }])
            ->latest()
            ->first();
    }

    protected function walletContacts(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return UserContact::where('user_id', $user->id)
            ->with('contactUser:id,name,linkup_id,type,avatar,email,country,new_country,country_code')
            ->get();
    }

    protected function walletContactUsers()
    {
        return User::contactForWallet()
            ->select('id', 'name', 'linkup_id', 'type', 'avatar', 'email', 'country', 'new_country', 'country_code')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'linkup_id' => $user->linkup_id,
                    'type' => $user->type ?? 'user',
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'country' => $user->new_country ?? $user->country,
                    'country_code' => $user->country_code,
                    'display_tag' => $user->linkup_id,
                ];
            });
    }

    protected function walletMoneyRequests(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return UserMoneyRequest::with([
            'requester:id,name,linkup_id,avatar',
            'recipient:id,name,linkup_id,avatar',
        ])
            ->where(function ($query) use ($user) {
                $query->where('requester_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->latest()
            ->get()
            ->map(function (UserMoneyRequest $request) use ($user) {
                $isIncoming = (int) $request->recipient_id === (int) $user->id;
                $person = $isIncoming ? $request->requester : $request->recipient;

                return [
                    'id' => $request->id,
                    'requester_id' => $request->requester_id,
                    'recipient_id' => $request->recipient_id,
                    'amount' => (float) $request->amount,
                    'note' => $request->note,
                    'status' => $request->status,
                    'direction' => $isIncoming ? 'incoming' : 'outgoing',
                    'created_at' => optional($request->created_at)->toISOString(),
                    'person' => [
                        'id' => $person?->id,
                        'name' => $person?->name ?? 'LinkUp User',
                        'tag' => $person?->linkup_id ? $person->linkup_id : '',
                        'img' => $person?->avatar,
                    ],
                ];
            })
            ->values();
    }

    protected function walletActivity(?User $user)
    {
        if (! $user) {
            return collect();
        }

        $balance = (float) ($user->balance('USD')->value->get() ?? 0);
        $runningBalance = $balance;

        return $user->transactions()
            ->with(['to', 'from'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($transaction) use (&$runningBalance, $user) {
                $isPositive = (int) $transaction->to_id === (int) $user->id
                    && in_array($transaction->to_type, [$user->getMorphClass(), User::class], true);

                $amount = (float) $transaction->amount;
                $currentRunningBalance = $runningBalance;

                if ($isPositive) {
                    $runningBalance -= $amount;
                } else {
                    $runningBalance += $amount;
                }

                $meta = is_array($transaction->meta) ? $transaction->meta : [];
                $type = $meta['type'] ?? ($transaction->processor_id === 'live deposit'
                    ? 'live_earnings_transfer'
                    : ($transaction->processor_id === 'vibe commission'
                        ? 'vibe_commission_transfer'
                        : ($transaction->type ?? $transaction->processor_id ?? 'wallet')));

                $counterpartyName = $isPositive
                    ? ($meta['sender_name'] ?? $transaction->from?->name ?? 'System')
                    : ($meta['recipient_name'] ?? $meta['receiver_name'] ?? $transaction->to?->name ?? 'System');

                $counterpartyTag = $isPositive
                    ? ($meta['sender_linkup_id'] ?? $transaction->from?->linkup_id ?? '')
                    : ($meta['recipient_linkup_id'] ?? $meta['receiver_linkup_id'] ?? $transaction->to?->linkup_id ?? '');

                if ($type === 'live_earnings_transfer') {
                    $counterpartyName = 'Live Dashboard';
                    $counterpartyTag = '';
                } elseif ($type === 'vibe_commission_transfer') {
                    $counterpartyName = 'Vibes Affiliate';
                    $counterpartyTag = '';
                }

                return [
                    'id' => (string) ($transaction->uuid ?? $transaction->id),
                    'title' => $this->walletActivityTitle((string) $type, $isPositive, $counterpartyName),
                    'amount' => $amount,
                    'isPositive' => $isPositive,
                    'status' => strtolower((string) ($transaction->status ?? 'success')),
                    'type' => $type,
                    'counterparty' => trim($counterpartyName . ($counterpartyTag ? ' @' . ltrim($counterpartyTag, '@') : '')),
                    'note' => $meta['note'] ?? ($type === 'live_earnings_transfer' ? 'Live analytics earnings transfer' : ($type === 'vibe_commission_transfer' ? 'Vibes affiliate commission payout' : null)),
                    'runningBalance' => $currentRunningBalance,
                    'date' => optional($transaction->created_at)->toISOString(),
                    'dateLabel' => optional($transaction->created_at)->diffForHumans(null, true) . ' ago',
                ];
            });

        $subscriptions = \Laravel\Cashier\Subscription::where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($sub) {
                $isCoin = $sub->type === 'coin' || $sub->name === 'coin';
                return [
                    'id' => 'sub-' . $sub->id,
                    'title' => $isCoin ? 'Coin Deposit' : 'Cash Deposit',
                    'amount' => (float) ($sub->stripe_price ?? 0),
                    'isPositive' => true,
                    'status' => strtolower((string) ($sub->stripe_status ?? 'success')),
                    'type' => $isCoin ? 'coin_deposit' : 'cash_deposit',
                    'counterparty' => 'Stripe Payment',
                    'note' => $isCoin ? "{$sub->quantity} Coins Purchased" : 'Cash Top Up',
                    'runningBalance' => null,
                    'date' => optional($sub->created_at)->toISOString(),
                    'dateLabel' => optional($sub->created_at)->diffForHumans(null, true) . ' ago',
                ];
            });

        return $activities->concat($subscriptions)
            ->sortByDesc(fn($item) => $item['date'] ?? '')
            ->values();
    }

    protected function walletActivityTitle(string $type, bool $isPositive, string $counterpartyName): string
    {
        $type = strtolower(trim($type));
        return match ($type) {
            'live_earnings_transfer' => 'Transferred from Live Dashboard',
            'vibe_commission_transfer' => 'Transferred from Vibes Commission',
            'recharge', 'deposit', 'cash_deposit' => 'Cash Deposit',
            'coin', 'coin_deposit', 'coin_purchase' => 'Coin Deposit',
            'p2p_transfer', 'transfer', 'send', 'p2p' => $isPositive ? 'Received from ' . $counterpartyName : 'Sent to ' . $counterpartyName,
            'money_request_payment', 'request' => $isPositive ? 'Request paid by ' . $counterpartyName : 'Paid request to ' . $counterpartyName,
            default => $isPositive ? 'Cash Deposit' : ($counterpartyName && $counterpartyName !== 'System' ? 'Sent to ' . $counterpartyName : 'Wallet payment'),
        };
    }

    protected function getGiftsReceived(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return GiftCoins::with(['sender', 'receiver'])
            ->where('recieved_id', $user->id)
            ->whereNull('responded_at')
            ->where(function($q) {
                $q->where('source', 'vibe')
                  ->orWhereNotNull('vibe_id');
            })
            ->latest()
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'sender_id' => $g->sender_id,
                'name' => $g->name ?? 'Gift',
                'coins' => (int) $g->coins,
                'sender' => $g->sender ? [
                    'id' => $g->sender->id,
                    'name' => $g->sender->name,
                    'avatar' => $g->sender->avatar,
                ] : null,
            ]);
    }

    public function giftsReceivedApi(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'gifts_received' => $this->getGiftsReceived($user),
        ]);
    }

    public function activityApi(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only Vibes activity (likes, comments, replies, mentions, follows, reposts, Big Ups) —
        // not every notification on the account.
        $notifications = Notification::where('user_id', $user->id)
            ->whereIn('context', \App\Services\VibeActivityNotifier::CONTEXTS)
            ->with('sender')
            ->latest()
            ->take(60)
            ->get();

        $followingUserIds = CreatorFollower::followedIds($user->id);

        $activities = $notifications->map(function ($n) use ($followingUserIds) {
            $sender = $n->sender;
            $isFollowing = $sender ? in_array((int) $sender->id, $followingUserIds, true) : false;

            $type = match (true) {
                str_ends_with((string) $n->context, '_like') => 'like',
                str_ends_with((string) $n->context, '_comment'), str_ends_with((string) $n->context, '_reply') => 'comment',
                str_ends_with((string) $n->context, '_mention') => 'mention',
                $n->context === 'vibe_follow' => 'follow',
                $n->context === 'vibe_repost' => 'repost',
                $n->context === 'say_thank_you' => 'thanks',
                default => 'gift',
            };

            $metadata = is_array($n->metadata) ? $n->metadata : json_decode($n->metadata ?? '{}', true);
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true);
            }
            $metadata = is_array($metadata) ? $metadata : [];

            return [
                'id' => $n->id,
                'type' => $type,
                'context' => $n->context,
                'user' => [
                    'id' => $sender?->id,
                    'name' => $sender?->name ?? 'User',
                    'handle' => $sender?->linkup_id ?? $sender?->name ?? 'user',
                    'avatar' => $sender?->avatar ?: $n->avatar,
                    'is_following' => $isFollowing,
                ],
                'message' => $n->message,
                'title' => $n->title,
                'comment' => $metadata['comment'] ?? null,
                'vibe_id' => isset($metadata['vibe_id']) ? (int) $metadata['vibe_id'] : null,
                'reel_id' => isset($metadata['reel_id']) ? (int) $metadata['reel_id'] : null,
                'coins' => isset($metadata['amount']) ? (int) $metadata['amount'] : null,
                'unread' => (bool) $n->unread,
                'time_group' => $n->created_at?->isToday() ? 'Today' : ($n->created_at?->isYesterday() ? 'Yesterday' : $n->created_at?->format('M d, Y')),
                'time_ago' => $n->created_at?->diffForHumans(null, true),
                'media_thumbnail' => $metadata['thumbnail'] ?? $metadata['image'] ?? null,
                'is_following' => $isFollowing,
                'created_at' => $n->created_at?->toISOString(),
            ];
        });

        // Opening the activity list reads it.
        Notification::where('user_id', $user->id)->whereIn('context', \App\Services\VibeActivityNotifier::CONTEXTS)
            ->where('unread', true)->update(['unread' => false]);

        return response()->json([
            'activity' => $activities,
        ]);
    }

    protected function walletSubscriptions(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return $user->subscribed()
            ->where('stripe_status', 'complete')
            ->latest()
            ->get(['id', 'type', 'stripe_price', 'stripe_status', 'created_at'])
            ->map(fn ($subscription) => [
                'id' => $subscription->id,
                'type' => $subscription->type ?? 'wallet',
                'amount' => (float) ($subscription->stripe_price ?? 0),
                'status' => $subscription->stripe_status ?? 'complete',
                'created_at' => optional($subscription->created_at)->toISOString(),
            ])
            ->values();
    }

    protected function walletBankWithdrawals(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return \App\Models\BankWithdrawal::where('user_id', $user->id)
            ->latest()
            ->get(['id', 'amount', 'bank_name', 'status', 'failure_reason', 'created_at'])
            ->map(fn ($withdrawal) => [
                'id' => $withdrawal->id,
                'amount' => (float) $withdrawal->amount,
                'bank_name' => $withdrawal->bank_name ?: 'Bank',
                'status' => $withdrawal->status ?? 'pending',
                'failure_reason' => $withdrawal->failure_reason,
                'created_at' => optional($withdrawal->created_at)->toISOString(),
            ])
            ->values();
    }

    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('User/Profile/Index', [
            'profileUser' => $this->profileUserData($user),
            'profileStats' => $this->profileStats($user),
            'wallet' => $this->homeWalletData($user),
            'tickets' => $this->profileTickets($user),
            'marketplaceOrders' => $this->profileMarketplaceOrders($user),
            'matchesData' => $this->profileMatches($user),
            'gifts' => $this->profileGifts($user),
            'newsItems' => $this->profileNews($user),
            'subscription' => $this->profileSubscription($user),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return back();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['nullable', 'string', 'max:255', Rule::unique('users', 'linkup_id')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'job' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
        ]);

        $nameParts = preg_split('/\s+/', trim($data['name']), 2);

        $user->name = $data['name'];
        $user->first_name = $nameParts[0] ?? $data['name'];
        $user->last_name = $nameParts[1] ?? '';
        $user->linkup_id = ltrim((string) ($data['handle'] ?? ''), '@');
        $user->email = $data['email'];
        $user->phone_number = $data['phone'] ?? null;
        $user->new_city = $data['city'] ?? null;
        $user->new_state = $data['state'] ?? null;
        $user->new_country = $data['country'] ?? null;
        $user->job = $data['job'] ?? null;
        $user->gender = $data['gender'] ?? null;
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    protected function profileUserData(?User $user): array
    {
        if (! $user) {
            return [
                'id' => null,
                'name' => 'Guest',
                'handle' => '@guest',
                'avatar' => null,
                'email' => '',
                'phone' => '',
                'birthday' => '',
                'job' => '',
                'gender' => '',
                'city' => '',
                'state' => '',
                'country' => '',
                'flag' => '',
                'website' => '',
                'verified' => false,
                'interests' => [],
                'has_transaction_pin' => false,
            ];
        }

        $country = $user->new_country ?? $user->country;
        $city = $user->new_city ?? $user->city;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'handle' => $user->linkup_id ? $user->linkup_id : '@user-' . $user->id,
            'avatar' => $user->avatar,
            'email' => $user->email,
            'phone' => $user->phone_number ?? $user->phone ?? '',
            'birthday' => $this->profileDate($user->birthday),
            'job' => $user->job ?? $user->occupation ?? '',
            'gender' => $user->gender ?? '',
            'city' => $city ?? '',
            'state' => $user->new_state ?? $user->state ?? '',
            'country' => $country ?? '',
            'flag' => CountryFlag::emoji($country),
            'website' => $user->linkup_id ? 'linkup.app/' . ltrim($user->linkup_id, '@') : '',
            'verified' => (bool) ($user->email_verified_at || $user->is_verified ?? false),
            'interests' => $this->profileInterests($user),
            'has_transaction_pin' => (bool) $user->has_transaction_pin,
        ];
    }

    protected function profileStats(?User $user): array
    {
        if (! $user) {
            return [
                'vibes' => 0,
                'followers' => 0,
                'following' => 0,
                'tickets' => 0,
                'cancelled_tickets' => 0,
                'marketplace' => 0,
                'matches' => 0,
                'likes' => 0,
                'gifts_purchased' => 0,
                'gifts_sent_coins' => 0,
                'gifts_received_coins' => 0,
                'net_gift_coins' => 0,
                'news' => 0,
            ];
        }

        $giftsSentCoins = (int) GiftCoins::where('sender_id', $user->id)->sum('coins');
        $giftsReceivedCoins = (int) GiftCoins::where('recieved_id', $user->id)->sum('coins');

        return [
            'vibes' => 0,
            'followers' => method_exists($user, 'followers') ? $user->followers()->count() : 0,
            'following' => method_exists($user, 'following') ? $user->following()->count() : 0,
            'tickets' => TicketSale::where('user_id', $user->id)->where('ticket_status', 'confirmed')->count(),
            'cancelled_tickets' => TicketSale::where('user_id', $user->id)->where('ticket_status', 'cancelled')->count(),
            'marketplace' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'matches' => UserMatch::where('user_id', $user->id)->count(),
            'likes' => UserMatch::where('user_id', $user->id)->where('status', 'like')->count(),
            'gifts_purchased' => GiftPurchase::where('user_id', $user->id)->count(),
            'gifts_sent_coins' => $giftsSentCoins,
            'gifts_received_coins' => $giftsReceivedCoins,
            'net_gift_coins' => $giftsReceivedCoins - $giftsSentCoins,
            'news' => News::where('user_id', $user->id)->count(),
        ];
    }

    protected function profileTickets(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return TicketSale::with('event')
            ->where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (TicketSale $ticket) => [
                'id' => $ticket->id,
                'event' => $ticket->event?->title ?? 'Event',
                'ticket_name' => $ticket->ticket_name ?? $ticket->ticket?->name ?? 'Ticket',
                'status' => $ticket->ticket_status,
                'amount' => (float) ($ticket->total ?? 0),
                'date' => optional($ticket->created_at)->format('M j, Y'),
            ]);
    }

    protected function profileMarketplaceOrders(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return Order::where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'number' => $order->number ?? 'Order #' . $order->id,
                'status' => $order->status,
                'total' => (float) ($order->net_total ?? $order->total ?? 0),
                'items_count' => method_exists($order, 'items') ? $order->items()->count() : 0,
                'date' => optional($order->created_at)->format('M j, Y'),
            ]);
    }

    protected function profileMatches(?User $user): array
    {
        if (! $user) {
            return ['likes_sent' => 0, 'likes_received' => 0, 'mutual' => 0];
        }

        $likedUsers = UserMatch::where('user_id', $user->id)->where('status', 'like')->pluck('target_user_id');
        $likedByUsers = UserMatch::where('target_user_id', $user->id)->where('status', 'like')->pluck('user_id');

        return [
            'likes_sent' => $likedUsers->count(),
            'likes_received' => $likedByUsers->count(),
            'mutual' => $likedUsers->intersect($likedByUsers)->count(),
        ];
    }

    protected function profileGifts(?User $user): array
    {
        if (! $user) {
            return [
                'sent' => [],
                'received' => [],
                'sent_total' => 0,
                'received_total' => 0
            ];
        }

        $mapGift = fn (GiftCoins $gift) => [
            'id' => $gift->id,
            'name' => $gift->name ?? 'Gift coins',
            'coins' => (int) $gift->coins,
            'status' => $gift->status,
            'vibe_id' => $gift->vibe_id,
            'source' => ($gift->vibe_id || str_contains(strtolower($gift->name ?? ''), 'vibe') || str_contains(strtolower($gift->name ?? ''), 'big up') || in_array($gift->name, ['Wha Gwaan', 'Likkle Smile', 'Soca Vibe', 'Wine & Dance', 'Sunset Lime', 'Coconut Cheers', 'Island Rose', 'Sweet Kiss', 'Candlelit Date', 'Beach Linkup', 'Sweetheart', 'Island Royalty'])) ? 'Vibes' : (str_contains(strtolower($gift->name ?? ''), 'u vibe') ? 'U Vibes' : (isset($gift->stream_id) ? 'Live' : 'LinkUp')),
            'sender' => $gift->sender ? [
                'id' => $gift->sender->id,
                'name' => $gift->sender->name,
            ] : null,
            'receiver' => $gift->receiver ? [
                'id' => $gift->receiver->id,
                'name' => $gift->receiver->name,
            ] : null,
            'created_at' => $gift->created_at->toIso8601String(),
            'date' => optional($gift->created_at)->format('M j, Y'),
        ];

        $sent = GiftCoins::with(['sender', 'receiver'])->where('sender_id', $user->id)->latest()->get();
        $received = GiftCoins::with(['sender', 'receiver'])->where('recieved_id', $user->id)->latest()->get();

        return [
            'sent' => $sent->map($mapGift),
            'received' => $received->map($mapGift),
            'sent_total' => (int) $sent->sum('coins'),
            'received_total' => (int) $received->sum('coins'),
        ];
    }

    protected function profileNews(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return News::where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (News $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'source' => $item->source_name ?? $item->category ?? 'LinkUp News',
                'status' => $item->status ?? '',
                'date' => optional($item->published_at ?? $item->created_at)->format('M j, Y'),
            ]);
    }

    protected function profileSubscription(?User $user): array
    {
        if (! $user) {
            return ['active' => null, 'history_count' => 0];
        }

        $active = SubscribedPlan::where('user_id', $user->id)
            ->where('status', 1)
            ->with('plan')
            ->latest('end_date')
            ->first();

        $endsAt = $active?->end_date ? Carbon::parse($active->end_date) : null;

        return [
            'active' => $active ? [
                'name' => $active->plan?->name ?? 'Active plan',
                'status' => 'Active',
                'amount' => (float) ($active->plan?->price ?? $active->amount ?? 0),
                'ends_at' => optional($endsAt)->format('M j, Y'),
                'days_left' => $endsAt ? max(0, now()->diffInDays($endsAt, false)) : 0,
            ] : null,
            'history_count' => SubscribedPlan::where('user_id', $user->id)->count(),
        ];
    }

    protected function profileInterests(User $user): array
    {
        $interests = $user->interests ?? [];

        if (is_string($interests)) {
            $decoded = json_decode($interests, true);
            $interests = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $interests)));
        }

        return array_values(array_filter((array) $interests));
    }

    protected function profileDate($value): string
    {
        if (! $value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function vibeUserProfile(User $user)
    {
        $currentUser = Auth::user();

        $isFollowing = CreatorFollower::where('follower_id', $currentUser->id)->where('creator_id', $user->id)->exists();

        $reels = UserReel::active()
            ->where('user_id', $user->id)
            ->with('user:id,name,avatar,linkup_id,city,country')
            ->latest()
            ->get()
            ->map(function ($reel) use ($currentUser) {
                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'user_id' => $reel->user_id,
                    'handle' => $reel->user?->linkup_id ?? $reel->user?->name ?? 'User',
                    'avatar' => $reel->user?->avatar,
                    'name' => $reel->user?->name,
                    'type' => $reel->type,
                    'file_path' => $reel->file_path,
                    'thumbnail_path' => $reel->thumbnail_path,
                    'caption' => $reel->caption,
                    'location' => $reel->location,
                    'likes_count' => $reel->likes_count,
                    'comments_count' => $reel->comments_count,
                    'shares_count' => $reel->shares_count,
                    'is_liked' => $reel->likes()->where('user_id', $currentUser->id)->exists(),
                    'is_saved' => $reel->saves()->where('user_id', $currentUser->id)->exists(),
                    'allow_coin_gifts' => (bool) ($reel->allow_coin_gifts ?? true),
                    'created_at' => $reel->created_at?->toISOString(),
                ];
            });

        $vibesRaw = Vibe::where('created_by', $user->id)
            ->visibleTo($currentUser)
            ->with(['creator', 'media', 'products', 'events', 'comments', 'likes'])
            ->latest()
            ->get();

        $vibes = $vibesRaw->map(function ($vibe) use ($currentUser) {
            $tag = null;
            if ($vibe->products->isNotEmpty()) {
                $p = $vibe->products->first();
                $tag = [
                    'kind' => 'product',
                    'vibe_id' => $vibe->id,
                    'id' => $p->id,
                    'title' => $p->name,
                    'price' => $p->price,
                    'seller' => $p->user?->name ?? $p->seller?->name,
                    'image' => $this->resolveStorageImage($p->cover_image) ?? $p->image_url,
                ];
            } elseif ($vibe->events->isNotEmpty()) {
                $e = $vibe->events->first();
                $tag = [
                    'kind' => 'event',
                    'vibe_id' => $vibe->id,
                    'id' => $e->id,
                    'title' => $e->title,
                    'price' => $e->tickets?->min('price') ?? 0,
                    'date' => \App\Support\VibeFeedPresenter::eventDate($e)?->format('Y-m-d'),
                    'location' => $e->venue,
                    'seller' => $e->user?->name,
                    'image' => $this->resolveStorageImage($e->featured_image) ?? $e->image_url,
                ];
            }

            $mediaItems = $vibe->media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'type' => $m->media_type->value ?? 'image',
                    'url' => $m->disk === 'public' ? asset('storage/' . $m->path) : Storage::disk($m->disk)->url($m->path),
                    'file_path' => $m->disk === 'public' ? asset('storage/' . $m->path) : Storage::disk($m->disk)->url($m->path),
                    'thumbnail' => $m->thumbnail_path ? ($m->disk === 'public' ? asset('storage/' . $m->thumbnail_path) : Storage::disk($m->disk)->url($m->thumbnail_path)) : null,
                ];
            });

            $firstMedia = $mediaItems->first();

            return [
                'id' => $vibe->id,
                'uid' => $vibe->id,
                'user_id' => $vibe->created_by,
                'is_vibe' => true,
                'caption' => $vibe->caption,
                'content' => $vibe->caption,
                'file_path' => $firstMedia['file_path'] ?? null,
                'thumbnail_path' => $firstMedia['thumbnail'] ?? null,
                'type' => $firstMedia['type'] ?? 'image',
                'media' => $mediaItems,
                'tag' => $tag,
                'creator' => [
                    'id' => $vibe->creator?->id,
                    'name' => $vibe->creator?->name,
                    'handle' => '@' . ($vibe->creator?->linkup_id ?? $vibe->creator?->name),
                    'avatar' => $vibe->creator?->avatar,
                ],
                'likes_count' => $vibe->likes ? $vibe->likes->count() : 0,
                'comments_count' => $vibe->comments ? $vibe->comments->count() : 0,
                'is_liked' => $currentUser && $vibe->likes ? $vibe->likes->where('user_id', $currentUser->id)->isNotEmpty() : false,
                'created_at' => $vibe->created_at?->diffForHumans(),
            ];
        });

        $shopItems = collect();
        foreach ($vibesRaw as $vibe) {
            foreach ($vibe->products as $p) {
                $shopItems->push([
                    'id' => $p->id,
                    'kind' => 'product',
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'image' => $this->resolveStorageImage($p->cover_image) ?? $p->image_url,
                ]);
            }
            foreach ($vibe->events as $e) {
                $shopItems->push([
                    'id' => $e->id,
                    'kind' => 'event',
                    'name' => $e->title,
                    'price' => (float) ($e->tickets?->min('price') ?? 0),
                    'image' => $this->resolveStorageImage($e->featured_image) ?? $e->image_url,
                ]);
            }
        }
        $shopItems = $shopItems->unique('id')->values();

        $topSenders = \App\Models\GiftCoins::where('recieved_id', $user->id)
            ->with('sender:id,name,avatar,linkup_id')
            ->select('sender_id', DB::raw('sum(coins) as total_coins'))
            ->groupBy('sender_id')
            ->orderByDesc('total_coins')
            ->limit(3)
            ->get()
            ->map(fn($g, $index) => [
                'rank' => $index + 1,
                'id' => $g->sender_id,
                'user_id' => $g->sender_id,
                'name' => $g->sender?->name ?? 'Supporter',
                'handle' => '@' . ($g->sender?->linkup_id ?? 'supporter'),
                'avatar' => $g->sender?->avatar,
                'coins' => (int) $g->total_coins,
            ]);

        $followersCount = CreatorFollower::where('creator_id', $user->id)->count();

        $followingCount = CreatorFollower::where('follower_id', $user->id)->count();

        $followers = CreatorFollower::where('creator_id', $user->id)
            ->with('follower:id,name,linkup_id,avatar')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id' => $r->follower?->id,
                'name' => $r->follower?->name,
                'handle' => '@' . ($r->follower?->linkup_id ?? $r->follower?->name ?? 'user'),
                'avatar' => $r->follower?->avatar,
            ])
            ->filter(fn($f) => $f['id'] !== null)
            ->values();

        // Fetch Real Affiliate Earnings
        $affEarnings = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->latest()
            ->get();

        $pendingAff = (float) $affEarnings->where('status', 'pending')->sum('commission_amount');
        $availableAff = (float) $affEarnings->whereIn('status', ['released', 'available'])->sum('commission_amount');
        $paidAff = (float) $affEarnings->where('status', 'paid')->sum('commission_amount');

        $earningsStats = [
            'sales_driven' => $affEarnings->count(),
            'total_commission' => (float) $affEarnings->sum('commission_amount'),
            'pending' => $pendingAff,
            'available' => $availableAff,
            'paid' => $paidAff,
            'recent' => $affEarnings->take(20)->map(fn ($e) => $e->summaryRow())
        ];

        $postsCount = UserReel::where('user_id', $user->id)->count() + Vibe::where('created_by', $user->id)->count();

        return Inertia::render('User/Vibes/UserProfile', [
            'shopFee' => app(\App\Repositories\ShopFeeRepository::class)->getFee(),
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'handle' => '@' . ($user->linkup_id ?? $user->name),
                'linkup_id' => $user->linkup_id,
                'avatar' => $user->avatar,
                'city' => $user->city,
                'country' => $user->country,
                'is_following' => $isFollowing,
                'posts_count' => $postsCount,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ],
            'reels' => $reels,
            'vibes' => $vibes,
            'shopItems' => $shopItems,
            'topSenders' => $topSenders,
            'followers' => $followers,
            'earningsStats' => $earningsStats,
            'gifts_received' => $this->getGiftsReceived($user),
        ]);
    }

    protected function getEarningsStats($user)
    {
        $affEarnings = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->latest()
            ->get();

        $pendingAff = (float) $affEarnings->where('status', 'pending')->sum('commission_amount');
        $availableAff = (float) $affEarnings->whereIn('status', ['released', 'available'])->sum('commission_amount');
        $paidAff = (float) $affEarnings->where('status', 'paid')->sum('commission_amount');

        return [
            'sales_driven' => $affEarnings->count(),
            'total_commission' => (float) $affEarnings->sum('commission_amount'),
            'pending' => $pendingAff,
            'available' => $availableAff,
            'paid' => $paidAff,
            'recent' => $affEarnings->take(20)->map(fn ($e) => $e->summaryRow())
        ];
    }

    protected function getAffiliateItems($user)
    {
        $affiliateProducts = \App\Models\MarketplaceProduct::with(['seller:id,name,avatar'])
            ->where('status', true)
            ->where('user_id', '!=', $user->id)
            ->where(function ($query) {
                $query->where('commMode', 'flat')->where('commFlat', '>', 0)
                    ->orWhere('commMode', 'pct')->where('commission', '>', 0);
            })
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'kind' => 'product',
                'title' => $p->name,
                'price' => (float) $p->price,
                'image' => $this->resolveStorageImage($p->cover_image) ?? $p->image_url,
                'seller' => $p->seller?->name ?? 'Store',
                'commMode' => $p->commMode,
                'commission' => (float) $p->commission,
                'commFlat' => (float) $p->commFlat,
            ]);

        $affiliateEvents = \App\Models\LinkUpEvent::with(['eventDetails', 'user', 'tickets'])
            ->activeTodayAndFuture()
            ->where('user_id', '!=', $user->id)
            ->whereHas('tickets', function ($query) {
                $query->where(function ($q) {
                    $q->where('commMode', 'flat')->where('commFlat', '>', 0)
                        ->orWhere('commMode', 'pct')->where('commission', '>', 0);
                });
            })
            ->get()
            ->map(function ($e) {
                $ticket = $e->tickets->first(fn ($t) =>
                    ($t->commMode === 'flat' && $t->commFlat > 0) ||
                    ($t->commMode === 'pct' && $t->commission > 0)
                );

                return [
                    'id' => $e->id,
                    'kind' => 'event',
                    'title' => $e->title,
                    'price' => $e->is_free ? 0 : (float) ($e->tickets->min('price') ?? 0),
                    'date' => $this->eventDate($e)?->toISOString(),
                    'location' => $e->venue,
                    'organizer' => $e->user?->name ?? 'Organizer',
                    'commMode' => $ticket?->commMode ?? 'none',
                    'commission' => (float) ($ticket?->commission ?? 0),
                    'commFlat' => (float) ($ticket?->commFlat ?? 0),
                    'image' => $e->image_url,
                ];
            });

        return $affiliateProducts->concat($affiliateEvents);
    }

    /**
     * "Say thank you" to someone who sent you a gift. Only to a real gift sender, never to yourself,
     * and the message is plain text of a sensible length.
     */
    public function thankSender(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:500',
            'gift_coins_id' => 'nullable|integer',
        ]);

        $sender = Auth::user();
        $recipient = User::findOrFail($data['user_id']);

        if ((int) $recipient->id === (int) $sender->id) {
            return response()->json(['success' => false, 'message' => 'You can’t thank yourself.'], 422);
        }

        // The thank-you must answer a gift this person actually sent you.
        $gifts = \App\Models\GiftCoins::where('recieved_id', $sender->id)->where('sender_id', $recipient->id);
        if (! empty($data['gift_coins_id'])) {
            $gifts->whereKey($data['gift_coins_id']);
        }
        if (! (clone $gifts)->exists()) {
            return response()->json(['success' => false, 'message' => 'You can only thank someone who sent you a gift.'], 422);
        }

        if (! empty($data['gift_coins_id'])) {
            $gifts->update(['responded_at' => now()]);
        } else {
            $gifts->whereNull('responded_at')->update(['responded_at' => now()]);
        }

        \App\Models\Notification::create([
            'title'    => 'Thank you for your support!',
            'message'  => strip_tags(trim($data['message'])),
            'send_by'  => $sender->id,
            'user_id'  => $recipient->id,
            'type'     => 'gift',
            'context'  => 'say_thank_you',
            'unread'   => true,
            'avatar'   => $sender->avatar ?? null,
            'metadata' => json_encode([
                'sender_name' => $sender->name,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you message sent successfully!'
        ]);
    }
}
