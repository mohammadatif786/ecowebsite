<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
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

        return Inertia::render('new_front/home/Index', [
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

        if (! str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }

        return asset($path);
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

                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'category' => $this->notificationCategory($n->type),
                    'unread' => (bool) $n->unread,
                    'priority' => (bool) $n->priority,
                    'avatar' => $sender?->avatar ?? $n->avatar,
                    'timeAgo' => optional($n->created_at)->diffForHumans(null, true) . ' ago',
                ];
            })
            ->reject(fn (array $n) => in_array($n['category'], $mutedCategories, true))
            ->values();

        return Inertia::render('new_front/notifications/Index', [
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

    protected function notificationCategory(?string $type): string
    {
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

        $vibes = Vibe::with(['creator', 'publisher', 'media', 'products.user', 'events.user', 'events.tickets'])
            ->where('status', VibeStatus::Published)
            ->latest()
            ->take(20)
            ->get();

        $formattedVibes = $vibes->map(function ($vibe) use ($user) {
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
                    'image' => $p->cover_image ? asset('storage/' . $p->cover_image) : null,
                ];
            } elseif ($vibe->events->isNotEmpty()) {
                $e = $vibe->events->first();
                $tag = [
                    'kind' => 'event',
                    'vibe_id' => $vibe->id,
                    'id' => $e->id,
                    'title' => $e->title,
                    'price' => $e->tickets->min('price') ?? 0,
                    'date' => $e->start_date?->format('Y-m-d'),
                    'location' => $e->venue,
                    'seller' => $e->user?->name,
                    'image' => $e->featured_image ? asset('storage/' . $e->featured_image) : null,
                ];
            }

            $mediaItems = $vibe->media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'type' => $m->media_type->value,
                    'url' => $m->disk === 'public' ? asset('storage/' . $m->path) : Storage::disk($m->disk)->url($m->path),
                    'thumbnail' => $m->thumbnail_path ? ($m->disk === 'public' ? asset('storage/' . $m->thumbnail_path) : Storage::disk($m->disk)->url($m->thumbnail_path)) : null,
                ];
            });

            $isReel = $vibe->media->contains(fn ($m) => $m->media_type->value === 'video');

            $publisher = $vibe->publisher;
            $publisherName = 'User';
            $publisherType = 'user';
            $publisherAvatar = $vibe->creator?->avatar ?? 'https://i.pravatar.cc/120?img=1';

            if ($publisher instanceof User) {
                $publisherName = $publisher->name;
                $publisherType = 'user';
                $publisherAvatar = $publisher->avatar;
            } elseif ($publisher instanceof \App\Models\OrganizerProfile) {
                $publisherName = $publisher->organizer_name;
                $publisherType = 'organization';
            } elseif ($publisher instanceof \App\Models\ClubFete) {
                $publisherName = $publisher->name;
                $publisherType = 'group';
            } elseif ($publisher instanceof UserCustomPublisher) {
                $publisherName = $publisher->name;
                $publisherType = $publisher->type;
            }

            return [
                'id' => $vibe->id,
                'created_by' => $vibe->created_by,
                'handle' => $publisherName,
                'publisher_type' => $publisherType,
                'avatar' => $publisherAvatar,
                'location' => $vibe->location_name,
                'media' => $mediaItems,
                'kind' => $isReel ? 'reel' : 'photo',
                'caption' => $vibe->caption,
                'likes_count' => $vibe->likes_count,
                'comments_count' => $vibe->comments_count,
                'shares_count' => $vibe->shares_count,
                'bigups_count' => $vibe->bigups_count,
                'is_liked' => $user ? $vibe->likes()->where('user_id', $user->id)->exists() : false,
                'allow_coin_gifts' => (bool) $vibe->allow_coin_gifts,
                'bigup' => $vibe->bigups_count,
                'shoppable' => $tag !== null,
                'tag' => $tag,
            ];
        });

        $customPublishers = UserCustomPublisher::where('user_id', $user->id)->get();

        $trendingTags = DB::table('vibe_hashtags')
            ->select('tag', DB::raw('count(*) as count'))
            ->groupBy('tag')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('tag')
            ->map(fn($tag) => '#' . $tag)
            ->toArray();

        $totalBigUpCoins = \App\Models\GiftCoins::where('recieved_id', $user->id)
            ->whereNotNull('vibe_id')
            ->sum('coins');

        $bigUpValue = (float) ($totalBigUpCoins * 0.01);

        // Fetch Real Affiliate Earnings
        $affEarnings = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->latest()
            ->get();

        $pendingAff = (float) $affEarnings->where('status', 'pending')->sum('commission_amount');
        $availableAff = (float) $affEarnings->where('status', 'released')->sum('commission_amount');
        $paidAff = (float) $affEarnings->where('status', 'paid')->sum('commission_amount');

        $earningsStats = [
            'sales_driven' => $affEarnings->count(),
            'total_commission' => (float) $affEarnings->sum('commission_amount'),
            'pending' => $pendingAff,
            'available' => $availableAff,
            'paid' => $paidAff,
            'recent' => $affEarnings->take(20)->map(fn($e) => [
                'title' => $e->product_id ? (\App\Models\MarketplaceProduct::find($e->product_id)?->name ?? 'Affiliate Sale') : 'Affiliate Sale',
                'amount' => (float) $e->commission_amount,
                'commission' => (float) $e->commission_amount,
                'rate' => 0,
                'status' => $e->status,
                'source' => $e->order_id ? 'marketplace' : 'vibe'
            ])
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
                'image' => $p->cover_image ?? $p->image_url,
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

        // Latest reel per user for the stories carousel
        $latestStoryIds = DB::query()
            ->fromSub(
                UserReel::active()
                    ->select([
                        'id',
                        'user_id',
                        DB::raw('ROW_NUMBER() OVER (
                            PARTITION BY user_id
                            ORDER BY created_at DESC, id DESC
                        ) AS row_num'),
                    ]),
                'ranked_reels'
            )
            ->where('row_num', 1)
            ->orderByDesc('id')
            ->limit(10)
            ->pluck('id');

        $stories = UserReel::active()
            ->with('user:id,name,avatar,linkup_id')
            ->whereIn('id', $latestStoryIds)
            ->get()
            ->sortByDesc(function ($reel) {
                return $reel->created_at?->timestamp ?? 0;
            })
            ->values()
            ->map(function ($reel) use ($user) {
                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'handle' => $reel->user_id === $user->id
                        ? 'Your Reel'
                        : ($reel->user?->linkup_id ?? $reel->user?->name ?? 'User'),
                    'avatar' => $reel->user?->avatar,
                    'type' => $reel->type,
                    'file_path' => $reel->file_path,
                    'thumbnail_path' => $reel->thumbnail_path,
                    'user_id' => $reel->user_id,
                    'name' => $reel->user?->name ?? 'User',
                    'created_at' => $reel->created_at?->toISOString(),
                ];
            })
            ->values();


        // ============================================================
        // ALL ACTIVE REELS FOR THE REEL VIEWER
        // ============================================================

        $allReels = UserReel::active()
            ->with('user:id,name,avatar,linkup_id')
            ->latest('created_at')
            ->latest('id')
            ->get()
            ->map(function ($reel) use ($user) {
                return [
                    'id' => $reel->id,
                    'uid' => $reel->uid,
                    'handle' => $reel->user_id === $user->id
                        ? 'Your Reel'
                        : ($reel->user?->linkup_id ?? $reel->user?->name ?? 'User'),
                    'avatar' => $reel->user?->avatar,
                    'type' => $reel->type,
                    'file_path' => $reel->file_path,
                    'thumbnail_path' => $reel->thumbnail_path,
                    'user_id' => $reel->user_id,
                    'name' => $reel->user?->name ?? 'User',
                    'created_at' => $reel->created_at?->toISOString(),
                ];
            })
            ->values();

        return Inertia::render('new_front/vibes/Index', [
            'vibes' => $formattedVibes,
            'stories' => $stories,
            'allReels' => $allReels,
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
        return Inertia::render('new_front/u_vibe/Index');
    }

    public function eats()
    {
        return Inertia::render('new_front/eats/Index');
    }

    public function dating()
    {
        return Inertia::render('new_front/linkup/Index');
    }

    public function events()
    {
        return Inertia::render('new_front/events/Index');
    }

    public function nightlife()
    {
        return Inertia::render('new_front/night_life/Index');
    }

    public function news(Request $request, NewsRepository $repository)
    {
        $data = $repository->getAll($request);

        return Inertia::render('new_front/caribbean360/Index', [
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

        $categories = \App\Models\ProductCategory::where('status', 1)->get()->map(function ($c) {
            return ['id' => $c->id, 'name' => $c->name];
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

        return Inertia::render('new_front/marketplace/Index', [
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

        return Inertia::render('new_front/wallet/Index', [
            'wallet' => $this->homeWalletData($user),
            'contacts' => $this->walletContacts($user),
            'users' => $this->walletContactUsers(),
            'moneyRequests' => $this->walletMoneyRequests($user),
            'activity' => $this->walletActivity($user),
            'subscriptions' => $this->walletSubscriptions($user),
            'bankWithdrawals' => $this->walletBankWithdrawals($user),
            'activeAsue' => $this->walletActiveAsue($user),
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
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($transaction) use (&$runningBalance, $user) {
                $isPositive = (int) $transaction->to_id === (int) $user->id
                    && $transaction->to_type === User::class;

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
                    : ($transaction->type ?? 'wallet'));

                $counterpartyName = $isPositive
                    ? ($meta['sender_name'] ?? $transaction->from?->name ?? 'System')
                    : ($meta['recipient_name'] ?? $transaction->to?->name ?? 'System');

                $counterpartyTag = $isPositive
                    ? ($meta['sender_linkup_id'] ?? $transaction->from?->linkup_id ?? '')
                    : ($meta['recipient_linkup_id'] ?? $transaction->to?->linkup_id ?? '');

                if ($type === 'live_earnings_transfer') {
                    $counterpartyName = 'Live Dashboard';
                    $counterpartyTag = '';
                }

                return [
                    'id' => $transaction->uuid ?? $transaction->id,
                    'title' => $this->walletActivityTitle((string) $type, $isPositive, $counterpartyName),
                    'amount' => $amount,
                    'isPositive' => $isPositive,
                    'status' => strtolower((string) ($transaction->status ?? 'success')),
                    'type' => $type,
                    'counterparty' => trim($counterpartyName . ($counterpartyTag ? ' @' . ltrim($counterpartyTag, '@') : '')),
                    'note' => $meta['note'] ?? ($type === 'live_earnings_transfer' ? 'Live analytics earnings transfer' : null),
                    'runningBalance' => $currentRunningBalance,
                    'date' => optional($transaction->created_at)->toISOString(),
                    'dateLabel' => optional($transaction->created_at)->diffForHumans(null, true) . ' ago',
                ];
            })
            ->values();
    }

    protected function walletActivityTitle(string $type, bool $isPositive, string $counterpartyName): string
    {
        return match ($type) {
            'live_earnings_transfer' => 'Transferred from Live Dashboard',
            'recharge' => 'Top Up - Card',
            'p2p_transfer' => $isPositive ? 'Received from ' . $counterpartyName : 'Sent to ' . $counterpartyName,
            'money_request_payment' => $isPositive ? 'Request paid by ' . $counterpartyName : 'Paid request to ' . $counterpartyName,
            default => $isPositive ? 'Received wallet funds' : 'Wallet payment',
        };
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

        return Inertia::render('new_front/profile/Index', [
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

    /**
     * Convert received gift coins to cash
     */
    public function convertToCash(Request $request)
    {
        $request->validate([
            'coins' => 'required|integer|min:1',
            'minimum_cashout' => 'sometimes|integer|min:1'
        ]);

        $user = Auth::user();
        $coinsToConvert = $request->input('coins');
        $minimumCashout = $request->input('minimum_cashout', 25);
        $coinRate = 0.01;
        $userPayoutRatio = 0.5;

        // Only coins without status can be converted
        $totalReceivedCoins = GiftCoins::where('recieved_id', $user->id)
            ->whereNull('status')
            ->sum('coins');

        $grossCashValue = $coinsToConvert * $coinRate;
        $userCashValue = $grossCashValue * $userPayoutRatio;
        $platformCashValue = $grossCashValue - $userCashValue;

        if ($userCashValue < $minimumCashout) {
            return response()->json([
                'success' => false,
                'message' => "Minimum cash-out amount is $" . number_format($minimumCashout, 2) . ". You need $" . number_format($minimumCashout - $userCashValue, 2) . " more to convert."
            ], 422);
        }

        if ($coinsToConvert > $totalReceivedCoins) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough received coins to convert this amount.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $currentWalletBalance = (float) ($user->balance('USD')->value->get() ?? 0);

            // Deposit to user wallet using the wallet system
            // Assuming deposit() helper exists as used in reference controller
            deposit($userCashValue, 'USD')
                ->from(Custodian::of('e_money'))
                ->to($user)
                ->overcharge()
                ->commit();

            $user->refresh();

            $remainingCoinsToConvert = $coinsToConvert;

            $receivedGifts = GiftCoins::where('recieved_id', $user->id)
                ->whereNull('status')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($receivedGifts as $gift) {
                if ($remainingCoinsToConvert <= 0) break;

                $coinsToDeduct = min($gift->coins, $remainingCoinsToConvert);

                if ($coinsToDeduct >= $gift->coins) {
                    $gift->status = now();
                    $gift->save();
                    $remainingCoinsToConvert -= $gift->coins;
                } else {
                    // Split the gift if only partially converted
                    $newGift = $gift->replicate();
                    $newGift->coins = $gift->coins - $coinsToDeduct;
                    $newGift->status = null;
                    $newGift->save();

                    $gift->coins = $coinsToDeduct;
                    $gift->status = now();
                    $gift->save();

                    $remainingCoinsToConvert = 0;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully converted ' . number_format($coinsToConvert) . ' coins to $' . number_format($userCashValue, 2),
                'data' => [
                    'coins_converted' => $coinsToConvert,
                    'new_wallet_balance' => (float) $user->balance('USD')->value->get(),
                    'remaining_received_coins' => $totalReceivedCoins - $coinsToConvert
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gift conversion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Conversion failed. Please try again later.'
            ], 500);
        }
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
}
