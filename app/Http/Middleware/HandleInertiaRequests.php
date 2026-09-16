<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use App\Support\CountryFlag;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => function() use ($request) {
                    if (!$request->user()) return null;

                    $user = $request->user()->load('organizerProfile');
                    $organizer = $user->organizerProfile;

                    $counts = [
                        'events' => 0,
                        'cookouts' => 0,
                        'wellness' => 0
                    ];

                    if ($organizer) {
                        $counts['events'] = \App\Models\LinkUpEvent::where('organizer_id', $organizer->id)
                            ->whereHas('category', function($q) {
                                $q->whereNotIn('name', ['Cookouts/Food', 'Food', 'Wellness and Spa']);
                            })
                            ->count();

                        $counts['cookouts'] = \App\Models\LinkUpEvent::where('organizer_id', $organizer->id)
                            ->whereHas('category', function($q) {
                                $q->whereIn('name', ['Cookouts/Food', 'Food']);
                            })
                            ->count();

                        $counts['wellness'] = \App\Models\LinkUpEvent::where('organizer_id', $organizer->id)
                            ->whereHas('category', function($q) {
                                $q->where('name', 'Wellness and Spa');
                            })
                            ->count();
                    }

                    return array_merge($user->append('popularity_level')->toArray(), [
                        'nav_counts' => $counts
                    ]);
                },
                'permissions' => Auth::user() ? Auth::user()->getAllPermissions()->pluck('name')->toArray() : [],
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'messages' => fn() => $request->session()->get('messages') ?? [],
            'message' => fn() => $request->session()->get('message'),
            'messageType' => fn() => $request->session()->get('messageType'),
            'walletBalance' => fn() => (float) (auth()?->user()?->balance('USD')->value->get() ?? 0),
            'userCountryFlag' => fn() => $request->user()
                ? CountryFlag::emoji($request->user()->new_country ?? $request->user()->country)
                : null,
            'notifications' => fn() => [
                'unreadCount' => $request->user()
                    ? Notification::query()->where('user_id', $request->user()->id)->where('unread', true)->count()
                    : 0,
            ],
            'legalPagesStatus' => function() {
                try {
                    return \App\Models\Settings::where('key', 'like', 'legal_page_%')
                        ->get()
                        ->mapWithKeys(function($item) {
                            $slug = str_replace('legal_page_', '', $item->key);
                            $val = $item->value;
                            return [$slug => is_array($val) ? ($val['is_visible'] ?? true) : true];
                        })->toArray();
                } catch (\Exception $e) {
                    return [];
                }
            },
            'createModalData' => Inertia::lazy(fn () => [
                'merchants' => Auth::user() ? \App\Models\Merchants::where('user_id', Auth::id())->where('is_active', 1)->get() : [],
                'categories' => \App\Models\ProductCategory::where('status', 1)->get()->map(function ($c) {
                    return ['id' => $c->id, 'name' => $c->name];
                }),
                'userProducts' => Auth::user() ? \App\Models\Product::withCount('orderItems')->where('user_id', Auth::id())->get() : [],
                'allProducts' => \App\Models\MarketplaceProduct::with(['seller:id,name', 'category:id,name'])->where('status', true)->latest()->get()->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->name,
                    'price' => (float)$p->price,
                    'image' => $p->cover_image ?? $p->image_url,
                    'seller' => $p->seller?->name ?? 'Seller',
                    'commMode' => $p->commMode ?? 'pct',
                    'commission' => $p->commission ?? 10,
                    'commFlat' => $p->commFlat ?? 0,
                ]),
                'allEvents' => \App\Models\LinkUpEvent::activeTodayAndFuture()->with(['tickets', 'organizer'])->latest()->get()->map(fn($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'price' => $e->is_free ? 0 : (float)($e->tickets->min('price') ?? 0),
                    'image' => $e->image_url,
                    'organizer' => $e->organizer?->name ?? 'Organizer',
                    'date' => optional($e->single_event_date ?? $e->recurr_start_date)->format('M d'),
                    'location' => $e->city,
                ]),
                'sellerHub' => function() {
                    if (!Auth::check()) return null;
                    $userId = Auth::id();
                    $analyticsRepo = app(\App\Repositories\SellerAnalyticsRepository::class);
                    $earningRepo = app(\App\Repositories\SellerEarningRepository::class);
                    $summary = $analyticsRepo->getSummaryStats($userId);
                    $available = $earningRepo->getAvailableEarnings($userId);
                    $pending = $earningRepo->getPendingEarnings($userId);

                    $sellerOrders = \App\Models\Order::whereHas('items', function ($q) use ($userId) {
                            $q->whereHas('product', function ($pq) use ($userId) {
                                $pq->where('user_id', $userId);
                            });
                        })
                        ->with(['items' => function($q) use ($userId) {
                            $q->whereHas('product', function($pq) use ($userId) {
                                $pq->where('user_id', $userId);
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

                    return [
                        'revenue' => $summary['total_revenue'],
                        'unitsSold' => $summary['units_sold'],
                        'orderCount' => $summary['total_orders'],
                        'earnings' => [
                            'available' => $available,
                            'pending' => $pending,
                        ],
                        'orders' => $sellerOrders,
                    ];
                }
            ]),
        ];
    }
}
