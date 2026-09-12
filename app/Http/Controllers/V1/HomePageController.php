<?php

namespace App\Http\Controllers\V1;

use App\Actions\IndexControllerAction;
use App\Actions\Interactions\LikeUserAction;
use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\IndexControllerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomePageController extends Controller
{
    protected $indexAction;

    public function __construct(IndexControllerAction $indexAction)
    {
        $this->indexAction = $indexAction;
    }

    public function getReleatedUsers()
    {

        $responseUsers = $this->indexAction->homePageIndex();

        return response()->json([
            'status' => true,
            'data' => $responseUsers
        ], 200);
    }

    public function storeGiftCoins(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'gift_name' => 'required|string',
            'coins' => 'required|integer|min:0',
        ]);

        if ($request->user()->coins < $request->coins) {
            return response()->json(['status' => false,  'message' => 'Insufficient coins'], 402);
        }

        $this->indexAction->sendGift($request);

        return response()->json(['status' => true, 'message' => 'Gift sent successfully!'], 200);
    }

    public function likeUser(User $user, LikeUserAction $likeUserAction)
    {
        try {
            $likeUserAction->execute($user);
            return response()->json(['status' => true, 'message' => 'User liked successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while liking user: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function dislikeUser(User $user, LikeUserAction $likeUserAction)
    {
        $likeUserAction->disLike($user);

        return response()->json(['status' => true,  'message' => 'User disliked successfully!'], 200);
    }

    public function getLinkupUser(string $slug, string $user)
    {
        $user = $this->indexAction->userDetail($user);

        return response()->json([
            'status' => true,
            'userdata'               => $user['userdata'],
            'isFriends'              => $user['isFriends'],
            'isFriendRequestSent'    => $user['isFriendRequestSent'],
            'isHiddenByProfileOwner' => $user['isHiddenByProfileOwner'],
        ], 200);
    }

    public function blockUser(Request $request)
    {

        $this->indexAction->blockUser($request);

        return response()->json(['status' => true, 'message' =>  'User blocked successfully'], 200);
    }

    public function unblockUser(Request $request)
    {
        $currentUser   = Auth::user();
        $blockedUserId = $request->input('blocked_user_id');
        $result = BlockedUser::unblockUser($currentUser->id, $blockedUserId);

        return response()->json(['status' => true, 'message' => 'User unblocked successfully'], 200);
    }

    public function getMatches()
    {
        $userLoggedin = Auth::user();
        $balance = $userLoggedin->coins ?? 0;

        $liked = $userLoggedin->likedUsers;

        $mutualMatches = $liked->filter(function ($user) use ($userLoggedin) {
            return UserMatch::where('user_id', $user->id)
                ->where('target_user_id', $userLoggedin->id)
                ->where('status', 'like')
                ->exists();
        });

        return response()->json([
            'users' => $mutualMatches,
        ]);
    }

    public function getLikes(IndexControllerService $indexService)
    {
        $userLoggedin = Auth::user();
        $balance = $userLoggedin->coins ?? 0;

        $likedByUsers = User::query()
            ->select('users.uid', 'users.id', 'users.age', 'users.name', 'users.country', 'users.gender', 'users.more_photos', 'users.avatar', 'users.popularity_score', 'users.last_active', 'users.interests', 'users.caribbean_interest')
            ->where('users.id', '!=', $userLoggedin->id)
            ->whereExists(function ($query) use ($userLoggedin) {
                $query->selectRaw('1')
                    ->from('user_matches')
                    ->whereColumn('user_matches.user_id', 'users.id')
                    ->where('user_matches.target_user_id', $userLoggedin->id)
                    ->where('user_matches.status', 'like');
            })
            ->get();

        $likedByUsers->transform(function ($user) use ($indexService) {
            $user->country_flag = $user->country
                ? $indexService->getCountryFlag($user->country)
                : null;

            return $user;
        });

        return response()->json([
            'status' => true,
            'users' => $likedByUsers,
            'currentUser' => $userLoggedin,
            'google_api_key' => env('GOOGLE_MAPS_API_KEY'),
            'balance' => $balance,
        ]);
    }

    public function userNotifation(Request $request)
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', Auth::id())->latest()->get();

        $senderIds = $notifications->pluck('send_by')->filter(fn ($id) => is_numeric($id))->unique()->values();
        $senders = \App\Models\User::whereIn('id', $senderIds)->get(['id', 'avatar'])->keyBy('id');

        $mutedCategories = $this->mutedNotificationCategories($user);

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

        return response()->json([
            'allnotifications' => $notifications,
            'settings' => $this->notificationSettings($request->user()),
        ]);
    }

    /** Mark every unread notification belonging to the authenticated user as read. */
    public function markAllNotificationsRead(Request $request)
    {
        $updated = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('unread', true)
            ->update(['unread' => false]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
            'updated' => $updated,
        ]);
    }

    /** Mark one of the authenticated user's notifications as read. */
    public function markNotificationRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['unread' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /** Save the notification preferences used by the new frontend settings dialog. */
    public function saveNotificationSettings(Request $request)
    {
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

        $user = $request->user();
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

        return response()->json([
            'success' => true,
            'message' => 'Notification settings saved.',
        ]);
    }

    /**
     * Apply the notification-tab filter used by the new frontend. Raw stored
     * notification types remain supported for mobile clients that use them.
     */
    private function applyNotificationFilter($query, ?string $filter): void
    {
        $filter = strtolower(trim((string) $filter));

        if ($filter === '' || $filter === 'all') {
            return;
        }

        if ($filter === 'unread') {
            $query->unread();

            return;
        }

        if ($filter === 'priority') {
            $query->priority();

            return;
        }

        $types = match ($filter) {
            'messages' => ['message', 'quick_reply'],
            'matches' => ['match', 'like', 'mutual_match'],
            'payments' => ['payment', 'payemnt', 'asue invitation', 'money request', 'money_request_payment'],
            'marketplace orders', 'marketplace-orders' => ['marketplace_order'],
            'eats' => ['eats', 'food_order', 'restaurant_order'],
            'events' => ['live_invite', 'event', 'event_invite'],
            'gifts' => ['gift'],
            'requests' => ['friend_request', 'friend_request_accepted'],
            'system' => ['system'],
            default => [$filter],
        };

        $query->whereIn('type', $types);
    }

    /** Map stored notification types to the categories displayed by the app. */
    private function notificationCategory(?string $type): string
    {
        return match (strtolower((string) $type)) {
            'message', 'quick_reply' => 'Messages',
            'match', 'like', 'mutual_match' => 'Matches',
            'payment', 'payemnt', 'asue invitation', 'money request', 'money_request_payment' => 'Payments',
            'marketplace_order' => 'Marketplace Orders',
            'gift' => 'Gifts',
            'friend_request', 'friend_request_accepted' => 'Requests',
            'live_invite', 'live_stream', 'monthly_sub_expiry' => 'Events',
            default => 'System',
        };
    }

    /** Settings shape used by the new frontend Notification Settings modal. */
    private function notificationSettings(User $user): array
    {
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

    /** Categories hidden from the notification list by the user's preferences. */
    private function mutedNotificationCategories(User $user): array
    {
        $muted = [];

        if (! $user->new_message_notification) {
            $muted[] = 'Messages';
        }
        if (! $user->new_match_notification) {
            $muted[] = 'Matches';
        }
        if ($user->mute_payment_notification) {
            $muted[] = 'Payments';
        }
        if ($user->mute_gift_notification) {
            $muted[] = 'Gifts';
        }
        if ($user->mute_system_notification) {
            $muted[] = 'System';
        }

        return $muted;
    }
}
