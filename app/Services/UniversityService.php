<?php

namespace App\Services;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Models\University;
use App\Models\User;
use App\Models\Vibe;
use App\Support\CountryFlag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * U Vibe campuses: the campus list, a member's own universities ("My universities") and the
 * campuses they follow. Used by the web app and the mobile API through the same controller,
 * so both behave the same.
 *
 * Rules (from the U Vibe design):
 *  - A member lists up to 5 universities, each "student" or "alumni"; exactly one is the main one.
 *  - A member can post to U Vibe only on their own universities.
 *  - Following a campus is separate: anyone can follow or view any campus.
 */
class UniversityService
{
    public const MAX_MINE = 5;
    public const STATUSES = ['student', 'alumni'];

    /** @var array<string, array{name:string, flag:string}>|null campus countries, keyed by countryKey() */
    protected ?array $knownCountries = null;

    /* ---------------------------------------------------------------- reading */

    /** Published U Vibe post count per university id. */
    public function postCounts(): Collection
    {
        return Vibe::query()
            ->where('is_uvibe', true)
            ->where('status', VibeStatus::Published)
            ->whereNotNull('university_id')
            ->selectRaw('university_id, COUNT(*) as c')
            ->groupBy('university_id')
            ->pluck('c', 'university_id');
    }

    /** One campus in the shape both web and API return. */
    public function present(University $u, Collection $counts, ?array $mine = null, array $following = []): array
    {
        $own = $mine[$u->id] ?? null;

        return [
            'id' => $u->id,
            'key' => $u->key,
            'name' => $u->name,
            'full_name' => $u->full_name,
            'country' => $u->country,
            'flag' => $u->flag ?: '🏫',
            'is_custom' => (bool) $u->is_custom,
            'posts_count' => (int) ($counts[$u->id] ?? 0),
            'is_mine' => (bool) $own,
            'is_main' => (bool) ($own['is_main'] ?? false),
            'status' => $own['status'] ?? null,
            'is_following' => in_array($u->id, $following, true),
        ];
    }

    /** Own universities keyed by id: ['status' => ..., 'is_main' => ...]. */
    protected function mineMap(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return DB::table('user_universities')
            ->where('user_id', $user->id)
            ->get(['university_id', 'status', 'is_main'])
            ->mapWithKeys(fn ($r) => [$r->university_id => ['status' => $r->status, 'is_main' => (bool) $r->is_main]])
            ->all();
    }

    protected function followingIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return DB::table('university_follows')->where('user_id', $user->id)->pluck('university_id')->map(fn ($i) => (int) $i)->all();
    }

    /**
     * The member's own universities (main first) and followed campuses — everything the
     * U Vibe header bar needs.
     */
    public function state(User $user): array
    {
        $counts = $this->postCounts();
        $mine = $this->mineMap($user);
        $following = $this->followingIds($user);

        $mineList = University::whereIn('id', array_keys($mine))->get()
            ->map(fn ($u) => $this->present($u, $counts, $mine, $following))
            ->sortBy(fn ($u) => [$u['is_main'] ? 0 : 1, $u['full_name']])
            ->values();

        $followList = University::whereIn('id', $following)->whereNotIn('id', array_keys($mine))->orderBy('name')->get()
            ->map(fn ($u) => $this->present($u, $counts, $mine, $following))
            ->values();

        return [
            'main' => $mineList->first(fn ($u) => $u['is_main']),
            'my_universities' => $mineList,
            'following' => $followList,
            'max' => self::MAX_MINE,
            'total_campuses' => University::count(),
        ];
    }

    /**
     * Full campus list for the Campus Explorer and the sign-up search, with country pills and
     * the explorer shelves (as id lists). Optional filters: search text, country.
     */
    public function catalog(?User $user, ?string $search = null, ?string $country = null): array
    {
        $counts = $this->postCounts();
        $mine = $this->mineMap($user);
        $following = $this->followingIds($user);

        $all = University::orderBy('full_name')->get();

        $countries = $all->groupBy(fn ($u) => $u->country ?: 'Other')
            ->map(fn ($g, $c) => ['country' => $c, 'flag' => $c === 'Other' ? '🏫' : ($g->first()->flag ?: '🏫'), 'count' => $g->count()])
            ->sortBy([['count', 'desc'], ['country', 'asc']])
            ->values();

        $q = $this->norm($search);
        $list = $all->filter(function ($u) use ($q, $country) {
            if ($country && ($u->country ?: 'Other') !== $country) {
                return false;
            }
            return $q === '' || str_contains($this->norm($u->full_name.' '.$u->name.' '.$u->country), $q);
        });

        $main = collect($mine)->search(fn ($m) => $m['is_main']);
        $mainUni = $main ? $all->firstWhere('id', $main) : null;

        return [
            'universities' => $list->map(fn ($u) => $this->present($u, $counts, $mine, $following))->values(),
            'countries' => $countries,
            'total' => $all->count(),
            // Explorer shelves (only meaningful without search/country filter).
            'shelves' => [
                'yours' => array_values(array_unique(array_merge(array_keys($mine), $following))),
                'trending' => $counts->sortDesc()->keys()->take(5)->map(fn ($i) => (int) $i)->values()->all(),
                'near' => $mainUni && $mainUni->country
                    ? ['country' => $mainUni->country, 'ids' => $all->where('country', $mainUni->country)->where('id', '!=', $mainUni->id)->take(6)->pluck('id')->values()->all()]
                    : null,
                'new' => $all->where('is_custom', true)->sortByDesc('created_at')->take(10)->pluck('id')->values()->all(),
            ],
            'can_add_custom' => $q !== '' && mb_strlen(trim((string) $search)) > 2 && ! $this->findByName((string) $search),
        ];
    }

    /**
     * A short list of campuses whose name, full name or country contains the text — what the
     * U Vibe search window shows under "Campuses" (the Explorer uses catalog() for the full list).
     * Needs at least 2 letters, like the design.
     *
     * @return array<int,array> campuses in the shape of present()
     */
    public function search(?User $user, string $search, int $limit = 6): array
    {
        $search = trim($search);
        if (mb_strlen($search) < 2) {
            return [];
        }

        $term = '%'.addcslashes($search, '%_\\').'%';
        $found = University::query()
            ->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('full_name', 'like', $term)->orWhere('country', 'like', $term))
            ->orderBy('full_name')
            ->limit(max(1, $limit))
            ->get();

        $counts = $this->postCounts();
        $mine = $this->mineMap($user);
        $following = $this->followingIds($user);

        return $found->map(fn ($u) => $this->present($u, $counts, $mine, $following))->values()->all();
    }

    /* ---------------------------------------------------------------- changing */

    /**
     * Add a school to My universities. Pass an existing university id, or a name that is
     * matched against the list and created as a new campus if nobody has added it yet.
     */
    public function addMine(User $user, ?int $universityId, ?string $name = null, ?string $status = null, ?string $country = null): University
    {
        $uni = $this->resolve($universityId, $name, $user, $country);

        return DB::transaction(function () use ($user, $uni, $status) {
            $rows = DB::table('user_universities')->where('user_id', $user->id)->lockForUpdate()->get();

            if ($rows->contains('university_id', $uni->id)) {
                throw ValidationException::withMessages(['university' => "{$uni->name} is already on your list."]);
            }
            if ($rows->count() >= self::MAX_MINE) {
                throw ValidationException::withMessages(['university' => 'You can list up to '.self::MAX_MINE.' universities.']);
            }

            DB::table('user_universities')->insert([
                'user_id' => $user->id,
                'university_id' => $uni->id,
                // First school defaults to "current student", later ones to "alumni" (as in the design).
                'status' => in_array($status, self::STATUSES, true) ? $status : ($rows->isEmpty() ? 'student' : 'alumni'),
                'is_main' => ! $rows->contains('is_main', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // A school you belong to is no longer just "followed".
            DB::table('university_follows')->where('user_id', $user->id)->where('university_id', $uni->id)->delete();

            return $uni;
        });
    }

    public function removeMine(User $user, University $uni): void
    {
        DB::transaction(function () use ($user, $uni) {
            $rows = DB::table('user_universities')->where('user_id', $user->id)->lockForUpdate()->get();
            $row = $rows->firstWhere('university_id', $uni->id);

            if (! $row) {
                throw ValidationException::withMessages(['university' => 'That university is not on your list.']);
            }
            if ($rows->count() < 2) {
                throw ValidationException::withMessages(['university' => 'Add another university before removing your only one.']);
            }

            DB::table('user_universities')->where('id', $row->id)->delete();

            if ($row->is_main) {
                $next = $rows->where('id', '!=', $row->id)->sortBy('created_at')->first();
                DB::table('user_universities')->where('id', $next->id)->update(['is_main' => true, 'updated_at' => now()]);
            }
        });
    }

    public function setStatus(User $user, University $uni, string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Status must be student or alumni.']);
        }
        $updated = DB::table('user_universities')->where('user_id', $user->id)->where('university_id', $uni->id)
            ->update(['status' => $status, 'updated_at' => now()]);
        if (! $updated) {
            throw ValidationException::withMessages(['university' => 'That university is not on your list.']);
        }
    }

    public function makeMain(User $user, University $uni): void
    {
        DB::transaction(function () use ($user, $uni) {
            $exists = DB::table('user_universities')->where('user_id', $user->id)->where('university_id', $uni->id)->exists();
            if (! $exists) {
                throw ValidationException::withMessages(['university' => 'That university is not on your list.']);
            }
            DB::table('user_universities')->where('user_id', $user->id)->update(['is_main' => false]);
            DB::table('user_universities')->where('user_id', $user->id)->where('university_id', $uni->id)
                ->update(['is_main' => true, 'updated_at' => now()]);
        });
    }

    public function follow(User $user, University $uni): void
    {
        if (DB::table('user_universities')->where('user_id', $user->id)->where('university_id', $uni->id)->exists()) {
            throw ValidationException::withMessages(['university' => "{$uni->name} is already one of your universities."]);
        }
        DB::table('university_follows')->insertOrIgnore([
            'user_id' => $user->id,
            'university_id' => $uni->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function unfollow(User $user, University $uni): void
    {
        DB::table('university_follows')->where('user_id', $user->id)->where('university_id', $uni->id)->delete();
    }

    /**
     * Sign-up: the chosen school becomes the member's main university. A school nobody has
     * listed yet is added to the campus list with the member's country (or the one given).
     */
    public function setFromRegistration(User $user, ?int $universityId, ?string $name, ?string $country = null): void
    {
        if (! $universityId && ! trim((string) $name)) {
            return;
        }
        $this->addMine($user, $universityId, $name, 'student', $country);
    }

    /**
     * Wizard / profile: "What university did you attend?" is the member's home campus.
     * The school is matched against the campus list (or added as a new campus carrying the
     * country picked next to it), put on My universities and made the main one — the same
     * thing the design does with `cpSetHome` at the sign-up "Background" step.
     *
     * Safe to call again with the same school (nothing changes) and never throws: a profile
     * save must not fail because of the campus list. Answers like "None" are ignored.
     */
    public function syncFromProfile(User $user, ?string $name, ?string $country = null): ?University
    {
        $name = trim(Str::limit((string) $name, 80, ''));
        if (mb_strlen($name) < 3 || $this->isNoUniversity($name)) {
            return null;
        }

        try {
            $uni = $this->resolve(null, $name, $user, $country);

            DB::transaction(function () use ($user, $uni) {
                $rows = DB::table('user_universities')->where('user_id', $user->id)->lockForUpdate()->get();
                $row = $rows->firstWhere('university_id', $uni->id);

                if (! $row) {
                    if ($rows->count() >= self::MAX_MINE) {
                        return; // list is full — leave it, the member can swap schools in My universities
                    }
                    DB::table('user_universities')->insert([
                        'user_id' => $user->id,
                        'university_id' => $uni->id,
                        'status' => $rows->isEmpty() ? 'student' : 'alumni',
                        'is_main' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('university_follows')->where('user_id', $user->id)->where('university_id', $uni->id)->delete();
                } elseif ($row->is_main) {
                    return;
                }

                DB::table('user_universities')->where('user_id', $user->id)->update(['is_main' => false]);
                DB::table('user_universities')->where('user_id', $user->id)->where('university_id', $uni->id)
                    ->update(['is_main' => true, 'updated_at' => now()]);
            });

            return $uni;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Whether the member has any university on their list yet. */
    public function hasAny(User $user): bool
    {
        return DB::table('user_universities')->where('user_id', $user->id)->exists();
    }

    /** True when two free-text university answers are the same school (case/punctuation ignored). */
    public function sameName(?string $a, ?string $b): bool
    {
        return $this->nameKey($a) === $this->nameKey($b);
    }

    /**
     * A country the way the campus list spells it, with its flag — from a name ("Trinidad and
     * Tobago", "The Bahamas") or an ISO code ("TT"). Null when nothing usable is given.
     *
     * @return array{name:string, flag:string}|null
     */
    public function countryInfo(?string $value): ?array
    {
        $v = trim((string) $value);
        if ($v === '' || in_array(Str::lower($v), ['none', 'other', 'n/a'], true)) {
            return null;
        }

        $known = $this->knownCountries();
        if (isset($known[$this->countryKey($v)])) {
            return $known[$this->countryKey($v)];
        }

        $alpha2 = CountryFlag::alpha2($v);
        $emoji = $alpha2 ? CountryFlag::emoji($alpha2) : '';
        if ($emoji !== '') {
            foreach ($known as $country) {
                if ($country['flag'] === $emoji) {
                    return $country;
                }
            }
        }

        // Not on the list yet: a typed name is kept as typed; a bare ISO code needs a name.
        $name = preg_match('/^[A-Za-z]{2}$/', $v) ? CountryFlag::name($v) : Str::limit($v, 60, '');
        if (! $name) {
            return null;
        }

        return ['name' => $name, 'flag' => $emoji !== '' ? $emoji : '🏫'];
    }

    /**
     * The campus a new U Vibe post goes to. Must be one of the member's own universities;
     * when none is given, the main one is used.
     */
    public function postingUniversity(User $user, ?int $universityId, ?string $campusName = null): University
    {
        $mine = $this->mineMap($user);
        if (! $mine) {
            throw ValidationException::withMessages(['university_id' => 'Add your university to your profile before posting to U Vibe.']);
        }

        $id = $universityId;
        if (! $id && $campusName) {
            $id = $this->findByName($campusName)?->id;
        }
        if (! $id) {
            $id = collect($mine)->search(fn ($m) => $m['is_main']) ?: array_key_first($mine);
        }
        if (! isset($mine[$id])) {
            throw ValidationException::withMessages(['university_id' => 'You can only post to your own universities.']);
        }

        return University::findOrFail($id);
    }

    /* ---------------------------------------------------------------- helpers */

    /** The member's own country (profile), used when a new campus is added without one. */
    protected function memberCountry(User $user): ?string
    {
        return $user->new_country ?: $user->country;
    }

    protected function resolve(?int $universityId, ?string $name, User $user, ?string $country = null): University
    {
        if ($universityId) {
            $uni = University::findOrFail($universityId);
        } else {
            $name = trim(Str::limit((string) $name, 80, ''));
            if ($name === '') {
                throw ValidationException::withMessages(['university' => 'Choose a university.']);
            }

            $uni = $this->findByName($name);

            // A school nobody has picked before becomes a campus the moment someone adds it.
            // It carries the country chosen next to it (or the member's own), so it shows up
            // under that country in the Campus Explorer instead of "Other".
            if (! $uni) {
                $info = $this->countryInfo($country) ?? $this->countryInfo($this->memberCountry($user));

                $uni = University::create([
                    'key' => 'c'.Str::limit(str_replace(' ', '', $this->norm($name)), 14, '').Str::lower(Str::random(4)),
                    'name' => $name,
                    'full_name' => $name,
                    'country' => $info['name'] ?? null,
                    'flag' => $info['flag'] ?? '🏫',
                    'is_custom' => true,
                    'created_by' => $user->id,
                ]);
                $this->knownCountries = null;

                return $uni;
            }
        }

        // A member-added campus that has no country yet picks one up from the next member who lists it.
        if ($uni->is_custom && ! $uni->country) {
            $info = $this->countryInfo($country) ?? $this->countryInfo($this->memberCountry($user));
            if ($info) {
                $uni->update(['country' => $info['name'], 'flag' => $info['flag']]);
                $this->knownCountries = null;
            }
        }

        return $uni;
    }

    /** Countries already on the campus list: key => ['name', 'flag']. */
    protected function knownCountries(): array
    {
        return $this->knownCountries ??= University::query()
            ->whereNotNull('country')
            ->get(['country', 'flag'])
            ->unique('country')
            ->mapWithKeys(fn ($u) => [$this->countryKey($u->country) => ['name' => $u->country, 'flag' => $u->flag ?: '🏫']])
            ->all();
    }

    /** "Trinidad & Tobago", "Trinidad and Tobago", "St. Lucia", "Saint Lucia", "The Bahamas" all compare equal to their list spelling. */
    protected function countryKey(string $s): string
    {
        $k = $this->norm(str_replace('&', ' and ', $s));
        $k = preg_replace('/\bst\b/', 'saint', $k);

        return trim(preg_replace('/^the /', '', $k));
    }

    /** "None", "N/A", "I did not attend university"… — answers that are not a school. */
    protected function isNoUniversity(string $name): bool
    {
        $n = $this->norm($name);

        return $n === '' || $n === 'no' || $n === 'na' || $n === 'nil' || $n === 'nope'
            || Str::startsWith($n, ['none', 'n a', 'no university', 'did not', 'i did not', 'i didn t', 'not applicable', 'not attended', 'never attended']);
    }

    public function findByName(string $name): ?University
    {
        $n = $this->nameKey($name);
        if ($n === '') {
            return null;
        }

        return University::all()->first(fn ($u) => $this->nameKey($u->full_name) === $n || $this->nameKey($u->name) === $n);
    }

    /** A school name for matching: like norm(), and a leading "The" is ignored. */
    protected function nameKey(?string $s): string
    {
        return trim(preg_replace('/^the /', '', $this->norm($s)));
    }

    /** Lower-case, accents removed, punctuation collapsed — for matching names. */
    protected function norm(?string $s): string
    {
        $s = Str::lower(Str::ascii((string) $s));

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $s));
    }
}
