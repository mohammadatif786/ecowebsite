<?php

namespace App\Services;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Models\ClubFete;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Models\UserCustomPublisher;
use App\Models\Vibe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The U Vibe search window (the magnifying glass in the header), for the website and the mobile
 * app alike. Two lists, as in the design:
 *
 *  - People: the members who post on U Vibe. With no text, everyone is listed up front (A–Z); as you
 *    type it narrows by their name, the campus they post on (name, full name, country) and the
 *    organizations they post for.
 *  - Campuses: schools whose name or country contains the text (from 2 letters).
 *
 * Only live posts count (published, not hidden by the member), and you never see yourself.
 */
class UVibeSearchService
{
    /** How many people one search returns. */
    public const PEOPLE_LIMIT = 40;

    /** How many campuses one search returns. */
    public const CAMPUS_LIMIT = 6;

    /** How many of the newest matching posts are read to find the people behind them. */
    private const SCAN = 600;

    public function __construct(
        private readonly UVibeFeedService $feed,
        private readonly UniversityService $universities,
    ) {}

    /**
     * @return array{q:string, people:array<int,array>, campuses:array<int,array>}
     */
    public function search(User $viewer, ?string $q = null): array
    {
        $q = trim((string) preg_replace('/\s+/u', ' ', (string) $q));

        return [
            'q' => $q,
            'people' => $this->people($viewer, $q),
            'campuses' => $this->universities->search($viewer, $q, self::CAMPUS_LIMIT),
        ];
    }

    /**
     * @return array<int,array{user_id:int, name:string, handle:?string, avatar:?string, campus:?array, posts:int, orgs:array<int,string>}>
     */
    protected function people(User $viewer, string $q): array
    {
        $query = $this->livePosts($viewer);

        if ($q !== '') {
            $term = '%'.addcslashes($q, '%_\\').'%';
            $query->where(fn (Builder $m) => $m
                ->whereHas('creator', fn ($u) => $u->where(fn ($x) => $x->where('name', 'like', $term)->orWhere('linkup_id', 'like', $term)))
                ->orWhereHas('university', fn ($u) => $u->where(fn ($x) => $x->where('name', 'like', $term)->orWhere('full_name', 'like', $term)->orWhere('country', 'like', $term)))
                ->orWhereHasMorph('publisher', [UserCustomPublisher::class], fn ($p) => $p->where('name', 'like', $term))
                ->orWhereHasMorph('publisher', [OrganizerProfile::class], fn ($p) => $p->where('organizer_name', 'like', $term))
                ->orWhereHasMorph('publisher', [ClubFete::class], fn ($p) => $p->where('name', 'like', $term)));
        }

        /** @var Collection<int,Vibe> $posts newest first */
        $posts = $query
            ->with(['creator:id,name,avatar,linkup_id', 'university:id,name,full_name,flag', 'publisher'])
            ->latest()->orderByDesc('id')
            ->limit(self::SCAN)
            ->get();

        $byMember = $posts->groupBy('created_by')->take(self::PEOPLE_LIMIT);

        // Real post counts for the members shown (the scan above is capped).
        $counts = $this->livePosts($viewer)
            ->whereIn('created_by', $byMember->keys())
            ->selectRaw('created_by, COUNT(*) as c')
            ->groupBy('created_by')
            ->pluck('c', 'created_by');

        $people = $byMember->map(function (Collection $theirs, $memberId) use ($counts) {
            $latest = $theirs->first();
            $member = $latest->creator;
            if (! $member) {
                return null;
            }
            $campus = $latest->university;

            return [
                'user_id' => (int) $memberId,
                'name' => (string) ($member->name ?: 'Member'),
                'handle' => $member->linkup_id ? '@'.$member->linkup_id : null,
                'avatar' => $member->avatar,
                'campus' => $campus ? ['id' => $campus->id, 'name' => $campus->name, 'flag' => $campus->flag ?: '🎓'] : null,
                'posts' => (int) ($counts[$memberId] ?? $theirs->count()),
                // Organizations they post for (so "student union" finds the member behind it).
                'orgs' => $theirs->map(fn (Vibe $p) => $this->organizationName($p))->filter()->unique()->take(3)->values()->all(),
            ];
        })->filter()->values();

        $needle = mb_strtolower($q);

        // Names that start with what you typed come first, then A–Z.
        return $people
            ->sortBy(fn (array $p) => [($needle !== '' && str_starts_with(mb_strtolower($p['name']), $needle)) ? 0 : 1, mb_strtolower($p['name'])])
            ->values()
            ->all();
    }

    /** Posts that count for search: live U Vibe posts by someone else that the member hasn't hidden. */
    protected function livePosts(User $viewer): Builder
    {
        return Vibe::query()
            ->where('is_uvibe', true)
            ->where('status', VibeStatus::Published)
            ->whereNotNull('created_by')
            ->where('created_by', '!=', $viewer->id)
            ->whereNotIn('id', $this->feed->hiddenBy($viewer));
    }

    /** The organization a post goes out under, if any (a club, a student union, a group…). */
    protected function organizationName(Vibe $post): ?string
    {
        $p = $post->publisher;

        return match (true) {
            $p instanceof UserCustomPublisher, $p instanceof ClubFete => $p->name ?: null,
            $p instanceof OrganizerProfile => $p->organizer_name ?: null,
            default => null,
        };
    }
}
