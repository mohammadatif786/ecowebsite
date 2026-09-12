<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Linkup\Actions\SwipeProfileAction;
use App\Domain\Linkup\DTOs\SwipeProfileData;
use App\Domain\Linkup\Services\LinkupAccessService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Linkup\SwipeProfileRequest;
use App\Http\Requests\Linkup\ProfileDiscoveryRequest;
use App\Http\Requests\Linkup\RespondFriendRequestRequest;
use App\Domain\Linkup\Actions\RespondFriendRequestAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\Frontend\FriendRequest;
use App\Models\Message;
use App\Models\ConversationPreference;
use App\Models\TicketSale;
use App\Services\IndexControllerService;

class LinkupController extends Controller
{
    public function index(ProfileDiscoveryRequest $request, IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $balance = $user->coins ?? 0;

        $interactedUserIds = UserMatch::where('user_id', $user->id)->pluck('target_user_id')->toArray();
        $friendsIds = $user->friends()->pluck('id')->toArray();
        $excludeIds = array_merge([$user->id], $interactedUserIds, $friendsIds);

        $discoverUsers = $this->applyDiscoveryFilters(User::eligibleForLinkup()->whereNotIn('id', $excludeIds), $request->validated())
            ->limit(50)
            ->get([
                'id', 'name', 'age', 'gender', 'country', 'new_country', 'city', 'new_city',
                'avatar', 'more_photos', 'uid', 'linkup_id', 'distanceinMK', 'about_me', 'whyare',
                'is_live_streaming', 'interests', 'language', 'religion', 'caribbean_interest',
                'latitude', 'longitude', 'job', 'university', 'link_me_with'
            ]);

        $sentRequestIds = FriendRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('receiver_id', $discoverUsers->pluck('id'))
            ->where('status', 0)
            ->pluck('receiver_id')
            ->all();

        $discoverUsers->transform(function ($u) use ($indexService, $sentRequestIds, $user) {
            $country = $u->new_country ?? $u->country;
            $u->country_flag = $country ? $indexService->getCountryFlag($country) : null;
            $u->resolved_country = $country;
            $u->resolved_city = $u->new_city ?? $u->city;
            $u->is_friend_request_sent = in_array((int) $u->id, array_map('intval', $sentRequestIds), true);
            // Normalize interests: could be JSON string or already an array
            if (is_string($u->interests)) {
                $u->interests = json_decode($u->interests, true) ?? [];
            }
            $u->distance_km = $this->distanceInKilometers($user, $u, $indexService);
            return $u;
        });

        $myLikes = UserMatch::where('user_id', $user->id)->where('status', 'like')->pluck('target_user_id')->toArray();
        $likedMe = UserMatch::where('target_user_id', $user->id)->where('status', 'like')->pluck('user_id')->toArray();

        $matchIds = array_intersect($myLikes, $likedMe);
        $matches = User::eligibleForLinkup()->whereIn('id', $matchIds)->get([
            'id', 'name', 'age', 'gender', 'avatar', 'more_photos', 'country', 'new_country',
            'city', 'new_city', 'uid', 'linkup_id', 'distanceinMK', 'is_live_streaming',
            'whyare', 'about_me', 'interests', 'language', 'job', 'university',
        ]);
        $matches->transform(function ($u) use ($indexService) {
            $u->country_flag = $u->country ? $indexService->getCountryFlag($u->country) : null;
            return $u;
        });

        $friends = $user->friends()->eligibleForLinkup()->select('users.id', 'name', 'age', 'avatar', 'country', 'uid')->get();
        $friends->transform(function ($u) use ($indexService) {
            $u->country_flag = $u->country ? $indexService->getCountryFlag($u->country) : null;
            return $u;
        });

        $requestsIds = FriendRequest::where('receiver_id', $user->id)->where('status', 0)->pluck('user_id')->toArray();
        $requests = User::eligibleForLinkup()->whereIn('id', $requestsIds)->get(['id', 'name', 'age', 'avatar', 'country', 'uid']);
        $requests->transform(function ($u) use ($indexService) {
            $u->country_flag = $u->country ? $indexService->getCountryFlag($u->country) : null;
            return $u;
        });

        $likesOnlyIds = array_diff($likedMe, $myLikes);
        $likes = User::eligibleForLinkup()->whereIn('id', $likesOnlyIds)->get(['id', 'name', 'age', 'avatar', 'country', 'uid']);
        $likes->transform(function ($u) use ($indexService) {
            $u->country_flag = $u->country ? $indexService->getCountryFlag($u->country) : null;
            return $u;
        });

        $caribbeanCountries = \App\Models\CaribbeanIsland::query()
            ->select('name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return Inertia::render('new_front/linkup/Index', [
            'currentUser' => $user,
            'balance' => $balance,
            'serverDiscoverUsers' => $discoverUsers,
            'serverMatches' => $matches,
            'serverFriends' => $friends,
            'serverRequests' => $requests,
            'serverLikes' => $likes,
            'caribbeanCountries' => $caribbeanCountries,
        ]);
    }

    // ── Shared helper: build a minimal user array with flag ──────────────
    protected function mapBasicUsers($collection, IndexControllerService $svc): \Illuminate\Support\Collection
    {
        return $collection->map(function ($u) use ($svc) {
            $attrs = $u->getAttributes();
            $country = $attrs['new_country'] ?? $attrs['country'] ?? null;
            $interests = $attrs['interests'] ?? [];

            if (is_string($interests)) {
                $interests = json_decode($interests, true) ?: [];
            }

            return [
                'id'           => $attrs['id'] ?? $u->id,
                'name'         => $attrs['name'] ?? $u->name,
                'age'          => $attrs['age'] ?? 25,
                'gender'       => $attrs['gender'] ?? null,
                'country'      => $country,
                'city'         => $attrs['new_city'] ?? $attrs['city'] ?? null,
                'avatar'       => $attrs['avatar'] ?? null,
                'more_photos'  => $attrs['more_photos'] ?? null,
                'uid'          => $attrs['uid'] ?? null,
                'linkup_id'    => $attrs['linkup_id'] ?? null,
                'distanceinMK' => $attrs['distanceinMK'] ?? null,
                'is_live_streaming' => $attrs['is_live_streaming'] ?? false,
                'pinned'       => (bool) ($attrs['pinned'] ?? false),
                'country_flag' => $country ? $svc->getCountryFlag($country) : null,
                'whyare'       => $attrs['whyare'] ?? null,
                'about_me'     => $attrs['about_me'] ?? null,
                'interests'    => $interests,
                'language'     => $attrs['language'] ?? null,
                'job'          => $attrs['job'] ?? null,
                'university'   => $attrs['university'] ?? null,
            ];
        });
    }

    // ── /dating/find ─────────────────────────────────────────────────────
    public function find(ProfileDiscoveryRequest $request, IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $interactedUserIds = UserMatch::where('user_id', $user->id)->pluck('target_user_id')->toArray();
        $friendsIds = $user->friends()->pluck('id')->toArray();
        $excludeIds = array_merge([$user->id], $interactedUserIds, $friendsIds);

        $discoverUsers = $this->applyDiscoveryFilters(User::eligibleForLinkup()->whereNotIn('id', $excludeIds), $request->validated())
            ->limit(80)
            ->get([
                'id', 'name', 'age', 'gender', 'country', 'new_country', 'city', 'new_city',
                'avatar', 'more_photos', 'uid', 'linkup_id', 'distanceinMK', 'about_me', 'whyare',
                'is_live_streaming', 'interests', 'language', 'religion', 'caribbean_interest', 'latitude', 'longitude', 'job', 'university',
            ]);

        $sentRequestIds = FriendRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('receiver_id', $discoverUsers->pluck('id'))
            ->where('status', 0)
            ->pluck('receiver_id')
            ->all();

        $discoverUsers->transform(function ($u) use ($indexService, $sentRequestIds, $user) {
            $country = $u->new_country ?? $u->country;
            $u->country_flag = $country ? $indexService->getCountryFlag($country) : null;
            $u->resolved_country = $country;
            $u->is_friend_request_sent = in_array((int) $u->id, array_map('intval', $sentRequestIds), true);
            if (is_string($u->interests)) $u->interests = json_decode($u->interests, true) ?? [];
            $u->distance_km = $this->distanceInKilometers($user, $u, $indexService);
            return $u;
        });

        return Inertia::render('new_front/linkup/Find', [
            'currentUser' => $user,
            'balance' => $user->coins ?? 0,
            'serverDiscoverUsers' => $discoverUsers,
            'caribbeanCountries' => \App\Models\CaribbeanIsland::query()
                ->select('name')
                ->whereNotNull('name')
                ->distinct()
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }

    // ── /dating/matches ───────────────────────────────────────────────────
    public function matches(IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $myLikes  = UserMatch::where('user_id', $user->id)->where('status','like')->pluck('target_user_id')->toArray();
        $likedMe  = UserMatch::where('target_user_id', $user->id)->where('status','like')->pluck('user_id')->toArray();
        $matchIds = array_intersect($myLikes, $likedMe);

        $matches = User::eligibleForLinkup()->whereIn('id', $matchIds)
            ->get([
                'id', 'name', 'age', 'gender', 'avatar', 'more_photos', 'country', 'new_country',
                'city', 'new_city', 'uid', 'linkup_id', 'distanceinMK', 'is_live_streaming',
                'whyare', 'about_me', 'interests', 'language', 'job', 'university',
            ]);

        return Inertia::render('new_front/linkup/Matches', [
            'currentUser' => $user,
            'balance' => $user->coins ?? 0,
            'serverMatches' => $this->mapBasicUsers($matches, $indexService),
        ]);
    }

    // ── /dating/friends ───────────────────────────────────────────────────
    public function friends(IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $friends = $user->friends()->eligibleForLinkup()->select('users.id','name','age','avatar','country','new_country','city','new_city','uid','distanceinMK','is_live_streaming','whyare')->get();

        return Inertia::render('new_front/linkup/Friends', [
            'currentUser' => $user,
            'balance' => $user->coins ?? 0,
            'serverFriends' => $this->mapBasicUsers($friends, $indexService),
        ]);
    }

    // ── /dating/requests ──────────────────────────────────────────────────
    public function requests(IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $requestsIds = FriendRequest::where('receiver_id', $user->id)->where('status', 0)->pluck('user_id')->toArray();
        $requests = User::eligibleForLinkup()->whereIn('id', $requestsIds)->get(['id','name','age','avatar','country','new_country','city','new_city','uid','distanceinMK','is_live_streaming']);

        return Inertia::render('new_front/linkup/Requests', [
            'currentUser' => $user,
            'serverRequests' => $this->mapBasicUsers($requests, $indexService),
        ]);
    }

    // ── /dating/likes ─────────────────────────────────────────────────────
    public function likes(IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Only incoming right-swipes: another user liked the signed-in user.
        // Do not exclude mutual matches; they still belong to "People who swiped
        // right on you" and should be visible on this page.
        $likes = User::eligibleForLinkup()
            ->where('id', '!=', $user->id)
            ->whereHas('matchesSent', function ($query) use ($user) {
                $query->where('target_user_id', $user->id)
                    ->where('status', 'like');
            })
            ->get(['id','name','age','avatar','country','new_country','city','new_city','uid','distanceinMK','is_live_streaming','whyare']);

        return Inertia::render('new_front/linkup/Likes', [
            'currentUser' => $user,
            'balance' => $user->coins ?? 0,
            'serverLikes' => $this->mapBasicUsers($likes, $indexService),
        ]);
    }

    // ── /dating/chats ─────────────────────────────────────────────────────
    public function chats(IndexControllerService $indexService)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Chat people = mutual matches + friends
        $myLikes  = UserMatch::where('user_id', $user->id)->where('status','like')->pluck('target_user_id')->toArray();
        $likedMe  = UserMatch::where('target_user_id', $user->id)->where('status','like')->pluck('user_id')->toArray();
        $matchIds = array_intersect($myLikes, $likedMe);
        $friendIds = $user->friends()->pluck('id')->toArray();
        $chatIds = array_unique(array_merge(array_values($matchIds), $friendIds));

        // Allows a zero-downtime deployment where application code is released
        // shortly before the additive conversation_preferences migration runs.
        $pinnedIds = Schema::hasTable('conversation_preferences')
            ? ConversationPreference::query()->where('user_id', $user->id)->where('is_pinned', true)->pluck('other_user_id')->all()
            : [];
        $pinnedIds = array_map('intval', $pinnedIds);
        $chatPeople = User::eligibleForLinkup()->whereIn('id', $chatIds)->get(['id','name','age','avatar','country','new_country','uid','is_live_streaming','whyare']);
        $lastMessageIds = Message::query()
            ->where(function ($query) use ($user, $chatIds) {
                $query->where(fn ($q) => $q->where('from_user_id', $user->id)->whereIn('to_user_id', $chatIds))
                    ->orWhere(fn ($q) => $q->where('to_user_id', $user->id)->whereIn('from_user_id', $chatIds));
            })
            ->selectRaw('MAX(id) as id')
            ->groupByRaw('CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END', [$user->id])
            ->pluck('id');
        $lastMessages = Message::whereIn('id', $lastMessageIds)->get(['id','from_user_id','to_user_id','content','type','meta','created_at','is_read'])
            ->keyBy(fn (Message $message) => $message->from_user_id === $user->id ? $message->to_user_id : $message->from_user_id);
        $unreadCounts = Message::query()->where('to_user_id', $user->id)->whereIn('from_user_id', $chatIds)->where('is_read', false)
            ->selectRaw('from_user_id, COUNT(*) as total')->groupBy('from_user_id')->pluck('total', 'from_user_id');

        // Do not resolve country flags here: getCountryFlag() performs a remote HTTP
        // request for each uncached country, which can make the chat list exceed PHP's
        // request timeout. The chat UI has a globe fallback when no flag is provided.
        $chatPeople = $chatPeople->map(function (User $chatPerson) use ($user, $pinnedIds, $lastMessages, $unreadCounts) {
            $person = [
                'id' => $chatPerson->id,
                'name' => $chatPerson->name,
                'age' => $chatPerson->age ?? 25,
                'country' => $chatPerson->new_country ?? $chatPerson->country,
                'avatar' => $chatPerson->avatar,
                'uid' => $chatPerson->uid,
                'is_live_streaming' => (bool) $chatPerson->is_live_streaming,
                'pinned' => in_array($chatPerson->id, $pinnedIds, true),
                'country_flag' => null,
            ];

            $person['last_message'] = $lastMessages->get($person['id']);
            $person['unread_count'] = (int) ($unreadCounts[$person['id']] ?? 0);

            return $person;
        })->sortByDesc(fn (array $person) => $person['last_message']?->created_at?->timestamp ?? 0)->values();

        return Inertia::render('new_front/linkup/Chats', [
            'currentUser' => $user,
            'serverChatPeople' => $chatPeople,
            'serverTickets' => TicketSale::where('user_id', $user->id)
                ->where('ticket_status', 'confirmed')
                ->with(['event', 'event.eventDetails', 'ticket'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }


    public function swipe(SwipeProfileRequest $request, SwipeProfileAction $swipe, LinkupAccessService $access)
    {
        $target = User::findOrFail($request->validated('target_id'));
        abort_unless($access->canInteract($request->user(), $target), 403, 'This profile is not available for interaction.');

        return response()->json($swipe->execute($request->user(), $target, SwipeProfileData::fromValidated($request->validated())));
    }

    public function handleRequest(RespondFriendRequestRequest $request, RespondFriendRequestAction $respond)
    {
        $friendRequest = FriendRequest::where('user_id', $request->validated('sender_id'))
            ->where('receiver_id', Auth::id())
            ->first();
        abort_unless($friendRequest && $request->user()->can('respond', $friendRequest), 403);
        $respond->execute($request->user(), $friendRequest, $request->validated('action') === '1');

        return response()->json(['success' => true]);
    }

    private function applyDiscoveryFilters($query, array $filters)
    {
        return $query
            ->when($filters['age_min'] ?? null, fn ($q, $value) => $q->where('age', '>=', $value))
            ->when($filters['age_max'] ?? null, fn ($q, $value) => $q->where('age', '<=', $value))
            ->when(($filters['gender'] ?? 'both') !== 'both', fn ($q) => $q->where('gender', $filters['gender']))
            ->when($filters['country'] ?? null, fn ($q, $value) => $q->where(fn ($country) => $country->where('country', $value)->orWhere('new_country', $value)))
            ->when($filters['city'] ?? null, fn ($q, $value) => $q->where(fn ($city) => $city->where('city', $value)->orWhere('new_city', $value)))
            ->when($filters['distance_min'] ?? null, fn ($q, $value) => $q->where('distanceinMK', '>=', $value))
            ->when(($filters['distance_max'] ?? 20000) < 20000, fn ($q, $value) => $q->where('distanceinMK', '<=', $filters['distance_max']))
            ->when($filters['interests'] ?? [], fn ($q, $values) => $q->where(function ($interest) use ($values) { foreach ($values as $value) $interest->orWhereJsonContains('interests', $value); }))
            ->when($filters['languages'] ?? [], fn ($q, $values) => $q->whereIn('language', $values))
            ->when($filters['religions'] ?? [], fn ($q, $values) => $q->whereIn('religion', $values))
            ->when($filters['goals'] ?? [], fn ($q, $values) => $q->where(function ($goal) use ($values) { foreach ($values as $value) $goal->orWhere('whyare', 'like', '%'.$value.'%'); }));
    }

    private function distanceInKilometers(User $viewer, User $profile, IndexControllerService $indexService): ?int
    {
        $coordinates = [$viewer->latitude, $viewer->longitude, $profile->latitude, $profile->longitude];

        if (collect($coordinates)->every(fn ($coordinate) => is_numeric($coordinate))) {
            $distance = $indexService->getDistance(
                (float) $viewer->latitude,
                (float) $viewer->longitude,
                (float) $profile->latitude,
                (float) $profile->longitude,
                'K',
            );

            return is_finite($distance) ? (int) $distance : null;
        }

        return is_numeric($profile->distanceinMK) ? (int) floor((float) $profile->distanceinMK) : null;
    }
}
