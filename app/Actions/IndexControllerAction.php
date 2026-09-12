<?php

namespace App\Actions;

use App\Models\BlockedUser;
use App\Models\Frontend\FriendRequest;
use App\Models\GiftCoins;
use App\Models\HideSpecificUser;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\IndexControllerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\PopularityScoreService;

class IndexControllerAction
{
    protected $indexService;

    public function __construct(IndexControllerService $indexService)
    {
        $this->indexService = $indexService;
    }
    public function homePageIndex()
    {
        $user = Auth::user();

        $distanceFilter = $user->distance_filter ?? ['0.0', '1000.0'];
        $ageFilter      = $user->age_filter ?? ['20', '40'];

        $query = User::query();

        $likedUserIds = UserMatch::where('user_id', $user->id)
            ->where('status', 'like')
            ->pluck('target_user_id');

        $query = $query->nearby(
            $user->latitude,
            $user->longitude,
            (float) $distanceFilter[0],
            (float) $distanceFilter[1]
        )
            ->where('id', '!=', $user->id)
            ->where('status', true)
            ->where('type', '!=', 'admin')
            ->whereBetween('age', [(int) $ageFilter[0], (int) $ageFilter[1]])
            ->whereNotNull('uid')
            ->whereNotIn('id', $likedUserIds);

        $ResponseUsers = $query->inRandomOrder()->paginate(10);
        $ResponseUsers->getCollection()->shuffle();

        $ResponseUsers->getCollection()->transform(function ($user) {
            $user->country_flag = $this->indexService->getCountryFlag($user->country);
            return $user;
        });

        $response = [
            'responseUsers' => $ResponseUsers,
            'caribbeanCountry' => $this->indexService->caribbeanIsland(),
            'advertisementList' => $this->indexService->advertisementList(),
            'logiUserWalletAmount' => $user->balance('USD')->value->get(),
            'logiUserCoins' => $user->coins,

        ];

        return $response;
    }

    public function sendGift(object $request)
    {
        $sender = $request->user();
        $receiver = User::find($request->receiver_id);


        if ($sender->coins < $request->coins) {
            return back()->with('error', 'Insufficient coins');
        }

        DB::transaction(function () use ($sender, $receiver, $request) {
            $sender->decrement('coins', $request->coins);

            $receiver->increment('coins', $request->coins);

            GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'name' => $request->gift_name,
                'coins' => $request->coins,
            ]);

            Notification::create([
                'title'    => 'You received a gift!',
                'message'  => "{$sender->name} has sent you {$request->gift_name} ({$request->coins} coins).",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'coins_transfer',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'amount'     => $request->coins,
                    'sender_id'  => $sender->id,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });
    }

    public function userDetail(string $user)
    {
        $user = User::where('uid', $user)->first();

        $currentUser = Auth::user();

        if ($currentUser && $currentUser->id !== $user->id) {
            DB::table('user_profile_views')->updateOrInsert(
                [
                    'viewer_id' => $currentUser->id,
                    'viewed_user_id' => $user->id,
                    'viewed_date' => now()->toDateString(),
                ],
                [
                    'viewed_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            app(PopularityScoreService::class)->bump($user, 1);
        }

        $isHiddenByProfileOwner = HideSpecificUser::where('user_id', $user->id)
            ->where('target_user_id', $currentUser->id)
            ->where('is_hidden', true)
            ->exists();

        $distance = null;
        if ($currentUser->latitude && $currentUser->longitude && $user->latitude && $user->longitude) {
            $distance = $this->indexService->getDistance(
                $currentUser->latitude,
                $currentUser->longitude,
                $user->latitude,
                $user->longitude,
                'K'
            );
            if (! is_finite($distance)) {
                $distance = null;
            }
        }

        $isFriends = FriendRequest::where(function ($query) use ($user) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', Auth::id())
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('receiver_id', Auth::id());
            });
        })
            ->where('status', 1)
            ->exists();

        $isFriendRequestSent = false;
        if (! $isFriends) {
            $isFriendRequestSent = FriendRequest::where('user_id', Auth::id())
                ->where('receiver_id', $user->id)
                ->exists();
        }
        $user->distance = $distance;

        $user->is_hidden_by_current_user = HideSpecificUser::where('user_id', $currentUser->id)
            ->where('target_user_id', $user->id)
            ->where('is_hidden', true)
            ->exists();

        $user->is_hidden_by_current_user = HideSpecificUser::where('user_id', $user->id)
            ->where('target_user_id', $currentUser->id)
            ->where('is_hidden', true)
            ->exists();

        $hideRecord = $user->id === Auth::id()
            ? HideSpecificUser::where('user_id', $currentUser->id)
            ->where('target_user_id', $user->id)
            ->where('is_hidden', true)
            ->first()
            : HideSpecificUser::where('user_id', $currentUser->id)
            ->where('target_user_id', $currentUser->id)
            ->where('is_hidden', true)
            ->first();

        $user->is_hidden_by_current_user = $hideRecord ? $hideRecord->is_hidden : false;

        $user->is_blocked_by_current_user = BlockedUser::where('user_id', $currentUser->id)
            ->where('blocked_user_id', $user->id)
            ->where('is_blocked', true)
            ->exists();

        $user->is_blocking_current_user = BlockedUser::where('user_id', $user->id)
            ->where('blocked_user_id', $currentUser->id)
            ->where('is_blocked', true)
            ->exists();

        $userdata = [
            'id' => $user->id,
            'uid' => $user->uid,
            'name' => $user->name,
            'age' => $user->age,
            'country' => $user->country,
            'interests' => $user->interests,
            'more_photos' => $user->more_photos,
            'about_me' => $user->about_me,
            'whyare' => $user->whyare,
            'job' => $user->job,
            'university' => $user->university,
            'language' => $user->language,
            'avatar' => $user->avatar,
            'distance' => $distance,
            'linkup_id' => $user->linkup_id,
            'is_hidden_by_current_user' => $user->is_hidden_by_current_user,
            'is_blocked_by_current_user' => $user->is_blocked_by_current_user,
        ];

        $returnUserDetail = [
            'userdata'               => $userdata,
            'isFriends'              => $isFriends,
            'isFriendRequestSent'    => $isFriendRequestSent,
            'isHiddenByProfileOwner' => $isHiddenByProfileOwner,
        ];

        return $returnUserDetail;
    }

    public function blockUser(object $request)
    {
        $reason = $request->input('reason');
        $note   = $request->input('note');

        $currentUser   = Auth::user();
        $blockedUserId = $request->input('blocked_user_id');


        if ($currentUser->id === $blockedUserId) {
            return back()->with('error', 'You cannot block yourself.');
        }
        $blockedUser = BlockedUser::blockUser($currentUser->id, $blockedUserId, $reason, $note);

        FriendRequest::where('user_id', $currentUser->id)
            ->where('receiver_id', $blockedUserId)
            ->delete();

        FriendRequest::where('user_id', $blockedUserId)
            ->where('receiver_id', $currentUser->id)
            ->delete();

        return $reason;
    }
}
