<?php

namespace App\Http\Controllers\V1;

use App\Domain\Linkup\Actions\SwipeProfileAction;
use App\Domain\Linkup\DTOs\SwipeProfileData;
use App\Domain\Linkup\Services\LinkupAccessService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Linkup\ProfileDiscoveryRequest;
use App\Http\Requests\Linkup\SwipeProfileRequest;
use App\Models\Frontend\FriendRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkupController extends Controller
{
    /** Return the data used by the Linkup/Dating landing screen. */
    public function index(ProfileDiscoveryRequest $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'current_user' => $this->currentUser($user),
                'discover_users' => $this->discoverUsers($user, $request->validated(), 50),
                'matches' => $this->matchUsers($user),
                'friends' => $this->friendUsers($user),
                'requests' => $this->requestUsers($user),
                'likes' => $this->likedByUsers($user),
            ],
        ]);
    }

    /** Return filtered profiles for the Find Dating screen. */
    public function find(ProfileDiscoveryRequest $request): JsonResponse
    {
        $profiles = $this->discoverUsers($request->user(), $request->validated(), 80);

        return response()->json([
            'success' => true,
            'data' => $profiles,
            'meta' => ['total' => $profiles->count()],
        ]);
    }
    /**
     * Record a left/right swipe. A right swipe returns match=true when the
     * target has already right-swiped the authenticated user.
     */
    public function swipe(
        SwipeProfileRequest $request,
        SwipeProfileAction $swipe,
        LinkupAccessService $access,
    ): JsonResponse {
        $target = User::findOrFail($request->validated('target_id'));

        abort_unless(
            $access->canInteract($request->user(), $target),
            403,
            'This profile is not available for interaction.',
        );

        return response()->json(
            $swipe->execute(
                $request->user(),
                $target,
                SwipeProfileData::fromValidated($request->validated()),
            ),
        );
    }

    /**
     * Return people who have mutually right-swiped with the authenticated user.
     */
    public function matches(Request $request): JsonResponse
    {
        $user = $request->user();

        $matches = $this->matchUsers($user);

        return response()->json([
            'success' => true,
            'data' => $matches,
            'meta' => ['total' => $matches->count()],
        ]);
    }

    public function friends(Request $request): JsonResponse
    {
        $friends = $this->friendUsers($request->user());

        return response()->json(['success' => true, 'data' => $friends, 'meta' => ['total' => $friends->count()]]);
    }

    public function requests(Request $request): JsonResponse
    {
        $requests = $this->requestUsers($request->user());

        return response()->json(['success' => true, 'data' => $requests, 'meta' => ['total' => $requests->count()]]);
    }

    public function likes(Request $request): JsonResponse
    {
        $likes = $this->likedByUsers($request->user());

        return response()->json(['success' => true, 'data' => $likes, 'meta' => ['total' => $likes->count()]]);
    }

    public function chats(Request $request): JsonResponse
    {
        $user = $request->user();
        $chatIds = $this->mutualMatchIds($user)
            ->merge($user->friends()->pluck('id'))
            ->unique()
            ->values();
        $people = User::eligibleForLinkup()->whereIn('id', $chatIds)->get($this->profileColumns());
        $lastMessageIds = Message::query()
            ->where(function ($query) use ($user, $chatIds) {
                $query->where(fn ($q) => $q->where('from_user_id', $user->id)->whereIn('to_user_id', $chatIds))
                    ->orWhere(fn ($q) => $q->where('to_user_id', $user->id)->whereIn('from_user_id', $chatIds));
            })
            ->selectRaw('MAX(id) as id')
            ->groupByRaw('CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END', [$user->id])
            ->pluck('id');
        $lastMessages = Message::whereIn('id', $lastMessageIds)->get(['id', 'from_user_id', 'to_user_id', 'content', 'type', 'created_at'])
            ->keyBy(fn (Message $message) => $message->from_user_id === $user->id ? $message->to_user_id : $message->from_user_id);
        $unreadCounts = Message::query()->where('to_user_id', $user->id)->whereIn('from_user_id', $chatIds)->where('is_read', false)
            ->selectRaw('from_user_id, COUNT(*) as total')->groupBy('from_user_id')->pluck('total', 'from_user_id');

        $chats = $people->map(function (User $person) use ($lastMessages, $unreadCounts) {
            return array_merge($this->profilePayload($person), [
                'last_message' => $lastMessages->get($person->id),
                'unread_count' => (int) ($unreadCounts[$person->id] ?? 0),
            ]);
        })->sortByDesc(fn (array $chat) => $chat['last_message']?->created_at?->timestamp ?? 0)->values();

        return response()->json(['success' => true, 'data' => $chats, 'meta' => ['total' => $chats->count()]]);
    }

    private function discoverUsers(User $user, array $filters, int $limit)
    {
        $interactedIds = UserMatch::where('user_id', $user->id)->pluck('target_user_id');
        $excludedIds = $interactedIds->merge($user->friends()->pluck('id'))->push($user->id)->unique();
        $profiles = $this->applyDiscoveryFilters(User::eligibleForLinkup()->whereNotIn('id', $excludedIds), $filters)
            ->limit($limit)->get($this->profileColumns());
        $sentIds = FriendRequest::where('user_id', $user->id)->where('status', 0)->whereIn('receiver_id', $profiles->pluck('id'))->pluck('receiver_id');

        return $profiles->map(fn (User $profile) => array_merge($this->profilePayload($profile), [
            'is_friend_request_sent' => $sentIds->contains($profile->id),
        ]))->values();
    }

    private function matchUsers(User $user)
    {
        return User::eligibleForLinkup()->whereIn('id', $this->mutualMatchIds($user))->get($this->profileColumns())
            ->map(fn (User $profile) => $this->profilePayload($profile))->values();
    }

    private function friendUsers(User $user)
    {
        return $user->friends()->eligibleForLinkup()->get($this->friendProfileColumns())
            ->map(fn (User $profile) => $this->profilePayload($profile))->values();
    }

    private function requestUsers(User $user)
    {
        $ids = FriendRequest::where('receiver_id', $user->id)->where('status', 0)->pluck('user_id');

        return User::eligibleForLinkup()->whereIn('id', $ids)->get($this->profileColumns())
            ->map(fn (User $profile) => $this->profilePayload($profile))->values();
    }

    private function likedByUsers(User $user)
    {
        $ids = UserMatch::where('target_user_id', $user->id)->where('status', 'like')->pluck('user_id');

        return User::eligibleForLinkup()->whereIn('id', $ids)->get($this->profileColumns())
            ->map(fn (User $profile) => $this->profilePayload($profile))->values();
    }

    private function mutualMatchIds(User $user)
    {
        return UserMatch::where('user_id', $user->id)->where('status', 'like')->pluck('target_user_id')
            ->intersect(UserMatch::where('target_user_id', $user->id)->where('status', 'like')->pluck('user_id'))->values();
    }

    private function profileColumns(): array
    {
        return ['id', 'name', 'age', 'gender', 'avatar', 'more_photos', 'country', 'new_country', 'city', 'new_city', 'uid', 'linkup_id', 'distanceinMK', 'is_live_streaming', 'whyare', 'about_me', 'interests', 'language', 'job', 'university'];
    }

    private function friendProfileColumns(): array
    {
        return array_map(fn (string $column) => 'users.'.$column, $this->profileColumns());
    }

    private function profilePayload(User $profile): array
    {
        $interests = is_string($profile->interests) ? json_decode($profile->interests, true) ?? [] : $profile->interests ?? [];

        return [
            'id' => $profile->id, 'name' => $profile->name, 'age' => $profile->age, 'gender' => $profile->gender,
            'avatar' => $profile->avatar, 'more_photos' => $profile->more_photos, 'country' => $profile->new_country ?? $profile->country,
            'city' => $profile->new_city ?? $profile->city, 'uid' => $profile->uid, 'linkup_id' => $profile->linkup_id,
            'distanceinMK' => $profile->distanceinMK, 'is_live_streaming' => (bool) $profile->is_live_streaming,
            'whyare' => $profile->whyare, 'about_me' => $profile->about_me, 'interests' => $interests,
            'language' => $profile->language, 'job' => $profile->job, 'university' => $profile->university,
        ];
    }

    private function currentUser(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar, 'coins' => (int) ($user->coins ?? 0)];
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
            ->when(($filters['distance_max'] ?? 20000) < 20000, fn ($q, $value) => $q->where('distanceinMK', '<=', $value))
            ->when($filters['interests'] ?? [], fn ($q, $values) => $q->where(function ($interest) use ($values) { foreach ($values as $value) $interest->orWhereJsonContains('interests', $value); }))
            ->when($filters['languages'] ?? [], fn ($q, $values) => $q->whereIn('language', $values))
            ->when($filters['religions'] ?? [], fn ($q, $values) => $q->whereIn('religion', $values))
            ->when($filters['goals'] ?? [], fn ($q, $values) => $q->where(function ($goal) use ($values) { foreach ($values as $value) $goal->orWhere('whyare', 'like', '%'.$value.'%'); }));
    }
}
