<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\LinkUpEvent;
use App\Models\Product;
use App\Models\User;
use App\Models\UserCustomPublisher;
use App\Support\VibeFeedPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * What the New Vibe composer needs from the server: who the member can post as, and the
 * Marketplace items / events they can tag. Same lists the website's composer shows.
 */
class VibeComposeController extends Controller
{
    private const MAX_CUSTOM_PUBLISHERS = 20;

    /** `GET vibes/publishers` → { organizations, groups, custom } the member may post as. */
    public function publishers(Request $request): JsonResponse
    {
        return response()->json($this->publisherLists($request->user()));
    }

    /** `POST vibes/custom-publishers` {name, type: organization|group} → the new publisher. */
    public function storeCustomPublisher(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', 'in:organization,group'],
        ]);

        $name = trim($data['name']);
        abort_if($name === '', 422, 'Add a name.');

        $existing = UserCustomPublisher::where('user_id', $user->id)->where('name', $name)->where('type', $data['type'])->first();
        if (! $existing && UserCustomPublisher::where('user_id', $user->id)->count() >= self::MAX_CUSTOM_PUBLISHERS) {
            return response()->json(['message' => 'You can add up to '.self::MAX_CUSTOM_PUBLISHERS.' organizations and groups.'], 422);
        }

        $publisher = $existing ?? UserCustomPublisher::create([
            'user_id' => $user->id,
            'name' => $name,
            'type' => $data['type'],
        ]);

        return response()->json([
            'id' => $publisher->id,
            'name' => $publisher->name,
            'type' => $publisher->type,
            'publisher_type' => 'custom_publisher',
        ], $existing ? 200 : 201);
    }

    /**
     * `GET vibes/taggable?kind=product|event&q=` → { items } — active Marketplace items in stock, or
     * upcoming events, that a post can tag. Each shows the commission the poster earns per sale.
     */
    public function taggable(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kind' => ['required', 'in:product,event'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);
        $q = trim((string) ($data['q'] ?? ''));
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';

        if ($data['kind'] === 'product') {
            $items = Product::query()
                ->with('user:id,name,first_name,last_name')
                ->where('status', true)
                ->where('qty', '>', 0)
                ->when($q !== '', fn ($w) => $w->where('name', 'like', $like))
                ->latest()
                ->limit(40)
                ->get()
                ->map(fn (Product $p) => [
                    'kind' => 'product',
                    'id' => $p->id,
                    'title' => $p->name,
                    'price' => (float) $p->price,
                    'image' => VibeFeedPresenter::image($p->cover_image) ?? $p->image_url,
                    'seller' => $p->user?->name ?? 'Marketplace',
                    'commission' => $this->commissionLabel($p->commMode, (float) $p->commission, (float) $p->commFlat),
                    'is_mine' => (int) $p->user_id === (int) $request->user()->id,
                ]);
        } else {
            $items = LinkUpEvent::query()
                ->activeTodayAndFuture()
                ->with(['tickets', 'eventDetails', 'user:id,name,first_name,last_name'])
                ->when($q !== '', fn ($w) => $w->where(fn ($m) => $m->where('title', 'like', $like)->orWhere('venue', 'like', $like)->orWhere('city', 'like', $like)))
                ->latest()
                ->limit(40)
                ->get()
                ->map(function (LinkUpEvent $e) use ($request) {
                    $ticket = $e->tickets->first(fn ($t) => ($t->commMode === 'flat' && $t->commFlat > 0) || ($t->commMode === 'pct' && $t->commission > 0));

                    return [
                        'kind' => 'event',
                        'id' => $e->id,
                        'slug' => $e->slug,
                        'title' => $e->title,
                        'price' => $e->is_free ? 0.0 : (float) ($e->tickets->min('price') ?? 0),
                        'image' => VibeFeedPresenter::image($e->featured_image) ?? $e->image_url,
                        'seller' => $e->organizer_name ?: ($e->user?->name ?? 'Organizer'),
                        'date' => VibeFeedPresenter::eventDate($e)?->format('Y-m-d'),
                        'location' => $e->venue ?: $e->city,
                        'commission' => $ticket ? $this->commissionLabel($ticket->commMode, (float) $ticket->commission, (float) $ticket->commFlat) : null,
                        'is_mine' => (int) $e->user_id === (int) $request->user()->id,
                    ];
                });
        }

        return response()->json(['kind' => $data['kind'], 'items' => $items->values()]);
    }

    private function publisherLists(User $user): array
    {
        return [
            'organizations' => $user->organizerProfile()->get(['id', 'organizer_name'])
                ->map(fn ($o) => ['id' => $o->id, 'name' => $o->organizer_name, 'type' => 'organization', 'publisher_type' => 'organization'])->values(),
            'groups' => $user->clubFetes()->wherePivot('is_active', true)
                ->wherePivotIn('role', ['owner', 'admin'])->where('club_fetes.status', true)
                ->get(['club_fetes.id', 'club_fetes.name'])
                ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'type' => 'group', 'publisher_type' => 'group'])->values(),
            'custom' => UserCustomPublisher::where('user_id', $user->id)->orderBy('name')->get()
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'type' => $c->type, 'publisher_type' => 'custom_publisher'])->values(),
        ];
    }

    private function commissionLabel(?string $mode, float $pct, float $flat): ?string
    {
        return match (true) {
            $mode === 'flat' && $flat > 0 => '$'.number_format($flat, 2).' per sale',
            $mode === 'pct' && $pct > 0 => rtrim(rtrim(number_format($pct, 2), '0'), '.').'% commission',
            default => null,
        };
    }
}
