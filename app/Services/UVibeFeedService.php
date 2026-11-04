<?php

namespace App\Services;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Models\ClubFete;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Models\UserCustomPublisher;
use App\Models\UserWalletKyc;
use App\Models\UvibeApplication;
use App\Models\Vibe;
use App\Support\UVibeOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * The U Vibe campus feed and the shape of one U Vibe post. The website page (Inertia) and the
 * mobile API use this same class, so a post looks the same everywhere.
 *
 * Feed rules (from the U Vibe design): a campus filter and a type filter narrow the feed; a
 * search looks across every campus (title, text, poster, campus name, country) and ignores
 * the other filters.
 */
class UVibeFeedService
{
    public const RELATIONS = ['creator', 'publisher', 'media', 'products.user', 'events.user', 'events.tickets', 'university', 'authUserLike'];

    public function __construct(
        private readonly UniversityService $universities,
        private readonly UVibeOrganizationService $organizations,
    ) {}

    /**
     * One page of the feed.
     *
     * @param  array{university_id?:int|null, category?:string|null, q?:string|null, scope?:string|null, per_page?:int|null}  $filters
     * @return array{posts:Collection, meta:array}
     */
    public function feed(User $viewer, array $filters = []): array
    {
        $perPage = max(1, min(50, (int) ($filters['per_page'] ?? 20)));
        $q = trim((string) ($filters['q'] ?? ''));

        $query = Vibe::query()
            ->with(self::RELATIONS)
            ->where('is_uvibe', true)
            ->where('status', VibeStatus::Published)
            ->whereNotIn('id', $this->hiddenBy($viewer));

        if ($q !== '') {
            $term = '%'.addcslashes($q, '%_\\').'%';
            $query->where(fn ($w) => $w
                ->where('title', 'like', $term)
                ->orWhere('caption', 'like', $term)
                ->orWhere('campus', 'like', $term)
                ->orWhereHas('university', fn ($u) => $u->where('name', 'like', $term)->orWhere('full_name', 'like', $term)->orWhere('country', 'like', $term))
                ->orWhereHas('creator', fn ($u) => $u->where('name', 'like', $term)));
        } else {
            $category = $filters['category'] ?? 'all';
            if ($category && $category !== 'all') {
                $query->where('category', $category);
            }

            if (! empty($filters['university_id'])) {
                $query->where('university_id', (int) $filters['university_id']);
            } elseif (in_array($filters['scope'] ?? null, ['mine', 'following'], true)) {
                $table = $filters['scope'] === 'mine' ? 'user_universities' : 'university_follows';
                $query->whereIn('university_id', DB::table($table)->where('user_id', $viewer->id)->select('university_id'));
            }
        }

        /** @var LengthAwarePaginator $page */
        $page = $query->latest()->orderByDesc('id')->paginate($perPage);

        return [
            'posts' => $this->presentMany($page->getCollection(), $viewer),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ];
    }

    /** Present many posts with one lookup each for "verified" and "alumni" instead of one per post. */
    public function presentMany(Collection $vibes, ?User $viewer): Collection
    {
        // "Verified" badge = the poster passed wallet KYC (same check the wallet uses).
        $verified = UserWalletKyc::whereIn('user_id', $vibes->pluck('created_by')->unique())
            ->where('status', 'approved')
            ->pluck('user_id')
            ->flip();

        $alumni = $this->alumniMap($vibes);
        $extras = $this->viewerExtras($vibes, $viewer);

        return $vibes->map(fn (Vibe $v) => $this->present($v, $viewer, $verified, $alumni, $extras))->values();
    }

    /** The posts this member hid ("Hide this post") — as a sub-query for `whereNotIn('id', …)`. */
    public function hiddenBy(?User $viewer): \Illuminate\Database\Query\Builder
    {
        return DB::table('uvibe_hidden_posts')->where('user_id', $viewer?->id ?? 0)->select('vibe_id');
    }

    /** The name a post is shown under (a person, an organization, a group or the university). */
    public function posterName(Vibe $vibe): string
    {
        $vibe->loadMissing(['creator', 'publisher', 'university']);

        return $this->publisher($vibe, null)[0];
    }

    /**
     * What depends on who is looking, looked up once for the whole page: which posts they already
     * reposted, and where they stand on each Campus Gig.
     *
     * @return array{reposted:array<int,bool>, gigs:array<int,array>}
     */
    protected function viewerExtras(Collection $vibes, ?User $viewer): array
    {
        $reposted = [];
        if ($viewer && $vibes->isNotEmpty()) {
            $reposted = array_fill_keys(
                Vibe::where('created_by', $viewer->id)->whereIn('reposted_from_id', $vibes->pluck('id'))->pluck('reposted_from_id')->all(),
                true,
            );
        }

        $gigs = [];
        $gigIds = $vibes->where('category', 'opportunity')->pluck('id');
        if ($gigIds->isNotEmpty()) {
            foreach (UvibeApplication::whereIn('vibe_id', $gigIds)->get(['id', 'vibe_id', 'user_id', 'status'])->groupBy('vibe_id') as $vibeId => $apps) {
                $mine = $viewer ? $apps->firstWhere('user_id', $viewer->id) : null;
                $gigs[$vibeId] = [
                    'applicants' => $apps->count(),
                    'paid' => $apps->where('status', 'paid')->count(),
                    'my_status' => $mine?->status,
                ];
            }
        }

        return ['reposted' => $reposted, 'gigs' => $gigs];
    }

    /**
     * One U Vibe post.
     *
     * @param  Collection|null  $verified  user ids (as keys) that passed KYC
     * @param  array<string,bool>|null  $alumni  "userId:universityId" => true when the poster is an alumnus there
     * @param  array|null  $extras  what depends on the viewer (reposted posts, gig applications), see viewerExtras()
     */
    public function present(Vibe $vibe, ?User $viewer, ?Collection $verified = null, ?array $alumni = null, ?array $extras = null): array
    {
        $alumni ??= $this->alumniMap(collect([$vibe]));
        $verified ??= collect();
        $extras ??= $this->viewerExtras(collect([$vibe]), $viewer);

        $tag = null;
        if ($vibe->products->isNotEmpty()) {
            $p = $vibe->products->first();
            $tag = [
                'kind' => 'product',
                'vibe_id' => $vibe->id,
                'affiliate_user_id' => $vibe->created_by,
                'id' => $p->id,
                'title' => $p->name,
                'price' => $p->price,
                'seller' => $p->user?->name ?? $p->seller?->name,
                'image' => $p->image_url,
            ];
        } elseif ($vibe->events->isNotEmpty()) {
            $e = $vibe->events->first();
            $tag = [
                'kind' => 'event',
                'vibe_id' => $vibe->id,
                'affiliate_user_id' => $vibe->created_by,
                'id' => $e->id,
                'title' => $e->title,
                'price' => $e->tickets->min('price') ?? 0,
                'date' => \App\Support\VibeFeedPresenter::eventDate($e)?->format('Y-m-d'),
                'location' => $e->venue,
                'seller' => $e->user?->name,
                'image' => $e->image_url,
            ];
        }

        $media = $vibe->media->map(fn ($m) => [
            'id' => $m->id,
            'type' => $m->media_type->value,
            'url' => $this->mediaUrl($m->disk, $m->path),
            'thumbnail' => $m->thumbnail_path ? $this->mediaUrl($m->disk, $m->thumbnail_path) : null,
        ]);

        [$posterName, $asOrg] = $this->publisher($vibe, $viewer);
        $category = $vibe->category ?: 'campus-life';
        $isText = ! empty($vibe->text_bg) && $media->isEmpty();
        $mine = (bool) ($viewer && (int) $vibe->created_by === (int) $viewer->id);
        $gig = $extras['gigs'][$vibe->id] ?? ['applicants' => 0, 'paid' => 0, 'my_status' => null];

        return [
            'id' => $vibe->id,
            // The member who made the post (also when it goes out under an organization).
            'poster_id' => $vibe->created_by,
            'org' => $posterName,
            'verified' => $verified->has($vibe->created_by),
            'mine' => $mine,
            'group' => $posterName,
            // Posted under an organization or group: who it is and who posted it.
            'asOrg' => $asOrg,
            'postedBy' => $asOrg ? $vibe->creator?->name : null,
            'role' => (string) ($vibe->posted_role ?? ''),
            // The poster is an alumnus (not a current student) of the campus the post is on.
            'alumni' => isset($alumni[$vibe->created_by.':'.$vibe->university_id]),
            'campus' => $vibe->university?->name ?? $vibe->campus ?? 'All Campuses',
            'university_id' => $vibe->university_id,
            'campus_flag' => $vibe->university?->flag,
            'time' => $vibe->created_at?->diffForHumans() ?? 'Just now',
            'created_at' => $vibe->created_at?->toISOString(),
            'category' => $category,
            'tagLabel' => collect(UVibeOptions::CATEGORIES)->firstWhere(0, $category)[1] ?? 'Campus Life',
            'image' => $media->first()['url'] ?? null,
            'images' => $media->pluck('url')->all(),
            'media' => $media->all(),
            'title' => $vibe->title ?? $vibe->caption,
            'price' => (float) ($vibe->price ?? 0),
            'desc' => $vibe->caption,
            'likes' => $vibe->likes_count ?? 0,
            'comments' => $vibe->comments_count ?? 0,
            'reposts' => $vibe->reposts_count ?? 0,
            'is_liked' => $vibe->relationLoaded('authUserLike') && $vibe->authUserLike !== null,
            'onVibes' => (bool) ($vibe->show_on_vibes ?? true),
            'allow_coin_gifts' => (bool) $vibe->allow_coin_gifts,
            // The Boost button: shown on every post whose poster left "Allow Boosts" on (as in the design).
            // Sending one to your own post is still refused — the button says so instead of opening the window.
            'can_boost' => (bool) $vibe->allow_coin_gifts,
            'boost_coins' => (int) ($vibe->boost_coins ?? 0),
            // Repost: where it came from, and whether this member already reposted it.
            'repostOf' => $vibe->reposted_from_id ? ['id' => (int) $vibe->reposted_from_id, 'name' => (string) $vibe->reposted_from_name] : null,
            'reposted' => isset($extras['reposted'][$vibe->id]),
            'edited' => $vibe->edited_at !== null,
            'share_url' => route('user.uvibe', ['post' => $vibe->id]),
            // Campus Gig: the poster sees how many applied/were paid; everyone else sees their own status
            // (null = not applied, 'applied' = waiting for the poster to pay, 'paid').
            'gig' => $category === 'opportunity' ? [
                'pay' => (float) ($vibe->price ?? 0),
                'applicants' => $mine ? $gig['applicants'] : null,
                'paid' => $mine ? $gig['paid'] : null,
                'status' => $mine ? null : $gig['my_status'],
            ] : null,
            // Text-card post: a background and no media.
            'isText' => $isText,
            'text_bg' => $vibe->text_bg,
            'text_font' => $vibe->text_font,
            // Everything needed to draw the text card (colours, font, motion, sticker).
            'text_card' => $isText ? UVibeOptions::textCard($vibe->text_style, $vibe->caption, $vibe->text_bg) : null,
            'tag' => $tag,
        ];
    }

    /** @return array{0:string, 1:array|null} [name shown on the post, organization badge or null] */
    protected function publisher(Vibe $vibe, ?User $viewer): array
    {
        // Posted as the university itself: the school is the poster, "University (official)".
        if ($vibe->as_university && $vibe->university) {
            return [$vibe->university->full_name, ['type' => 'University (official)', 'emoji' => UVibeOptions::orgEmoji('University (official)')]];
        }

        $p = $vibe->publisher;

        return match (true) {
            $p instanceof OrganizerProfile => [$p->organizer_name, ['type' => 'Organization', 'emoji' => '🏛️']],
            $p instanceof ClubFete => [$p->name, ['type' => 'Group', 'emoji' => '🤝']],
            $p instanceof UserCustomPublisher => $this->customOrganization($p),
            $p instanceof User => [$p->name, null],
            default => [$vibe->creator?->name ?? $viewer?->name ?? 'University', null],
        };
    }

    /** @return array{0:string, 1:array} an organization the member added: its name and "Club / Society 🤝"-style badge */
    protected function customOrganization(UserCustomPublisher $p): array
    {
        $o = $this->organizations->present($p);

        return [$o['name'], ['type' => $o['org_type'], 'emoji' => $o['emoji']]];
    }

    /** "userId:universityId" => true for posters who list that campus as alumni. */
    protected function alumniMap(Collection $vibes): array
    {
        $pairs = $vibes->filter(fn ($v) => $v->university_id);
        if ($pairs->isEmpty()) {
            return [];
        }

        return DB::table('user_universities')
            ->where('status', 'alumni')
            ->whereIn('user_id', $pairs->pluck('created_by')->unique())
            ->whereIn('university_id', $pairs->pluck('university_id')->unique())
            ->get(['user_id', 'university_id'])
            ->mapWithKeys(fn ($r) => [$r->user_id.':'.$r->university_id => true])
            ->all();
    }

    protected function mediaUrl(?string $disk, ?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return $disk === 'public' ? asset('storage/'.$path) : Storage::disk($disk)->url($path);
    }
}
