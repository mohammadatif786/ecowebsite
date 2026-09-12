<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\IndexControllerAction;
use App\Actions\Interactions\LikeUserAction;
use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\CaribbeanIsland;
use App\Models\FlaggedUser;
use App\Models\Frontend\FriendRequest;
use App\Models\HideSpecificUser;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\GiftCoins;
use Illuminate\Support\Facades\Log;
use App\Models\Advertisement;
use App\Services\IndexControllerService;
use Pusher\PushNotifications\PushNotifications;

class IndexController extends Controller
{
    protected $indexAction;

    public function __construct(IndexControllerAction $indexAction)
    {
        $this->indexAction = $indexAction;
    }

    public function index()
    {
        return Inertia::render('User/Landing2', [
            'canLogin'       => Route::has('login'),
            'canRegister'    => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion'     => PHP_VERSION,
        ]);
    }

    public function index2()
    {
        $pushNotifications = new PushNotifications([
            "instanceId" => env('PUSHER_INSTANCE_ID'),
            "secretKey"  => env('PUSHER_PRIMARY_KEY'),
        ]);

        $publishResponse = $pushNotifications->publishToInterests(
            ["linkup"],
            [
                "web" => ["notification" => [
                    "title"     => "Hello",
                    "body"      => "Hello, World!",
                    "deep_link" => "https://www.pusher.com",
                ]],
            ]
        );
        return Inertia::render('User/Landing2');
    }

    public function homematches()
    {
        $response = $this->indexAction->homePageIndex();

        $tempSwipAds = [[
            "id" => 48,
            "firebase_id" => null,
            "category" => "general",
            "name" => "Sushi Zen",
            "country" => "USA",
            "state" => "California",
            "city" => "Los Angeles",
            "location" => "West Hollywood",
            "email" => "info@sushizen.com",
            "phone" => "3105550456",
            "headline" => "Fresh Sushi Daily",
            "description" => "Premium sushi bar with daily fresh fish imports and omakase experience.",
            "ad_type" => "video",
            "image" => null,
            "video" => "a",
            "thumbnail" => "adsf",
            "max_duration" => "15",
            "autoplay_sound" => "muted",
            "cta_overlay_timing" => "10",
            "loop_video" => "no",
            "cta_text" => "Reserve Table",
            "brand_color" => "#2E8B57",
            "www" => "https://sushizen.com",
            "cost" => "1999",
            "paid" => "yes",
            "is_paid" => 1,
            "status" => 1,
            "start_date" => "2026-06-03",
            "end_date" => "2026-07-03",
            "duration_days" => 30,
            "price_package" => "network",
            "custom_cost_override" => null,
            "payment_ref" => "",
            "publication_status" => "Active (publish immediately)",
            "targeting_notes" => "High-income professionals in LA",
            "created_at" => "2026-06-02 10:17:25",
            "updated_at" => "2026-06-03 09:30:24",
            "invoice_id" => null
        ], [
            "id" => 48,
            "firebase_id" => null,
            "category" => "general",
            "name" => "Sushi Zen",
            "country" => "USA",
            "state" => "California",
            "city" => "Los Angeles",
            "location" => "West Hollywood",
            "email" => "info@sushizen.com",
            "phone" => "3105550456",
            "headline" => "Fresh Sushi Daily",
            "description" => "Premium sushi bar with daily fresh fish imports and omakase experience.",
            "ad_type" => "video",
            "image" => null,
            "video" => "a",
            "thumbnail" => "adsf",
            "max_duration" => "15",
            "autoplay_sound" => "muted",
            "cta_overlay_timing" => "10",
            "loop_video" => "no",
            "cta_text" => "Reserve Table",
            "brand_color" => "#2E8B57",
            "www" => "https://sushizen.com",
            "cost" => "1999",
            "paid" => "yes",
            "is_paid" => 1,
            "status" => 1,
            "start_date" => "2026-06-03",
            "end_date" => "2026-07-03",
            "duration_days" => 30,
            "price_package" => "network",
            "custom_cost_override" => null,
            "payment_ref" => "",
            "publication_status" => "Active (publish immediately)",
            "targeting_notes" => "High-income professionals in LA",
            "created_at" => "2026-06-02 10:17:25",
            "updated_at" => "2026-06-03 09:30:24",
            "invoice_id" => null
        ]];


        return Inertia::render('User/FindMatch/home', [
            'users'            => $response['responseUsers'],
            'currentUser'      => Auth::user(),
            'google_api_key'   => env('GOOGLE_MAPS_API_KEY'),
            'caribbeanCountry' => $response['caribbeanCountry'],
            'homePage'         => true,
            'swipeAds'         => $tempSwipAds,
        ]);
    }

    public function findmatches(Request $request, IndexControllerService $indexService)
    {
        $currentUser = Auth::user();

        $likedUserIds = UserMatch::where('user_id', $currentUser->id)
            ->where('status', 'like')
            ->pluck('target_user_id');

        $flaggedUserIds = FlaggedUser::where('from_user_id', $currentUser->id)
            ->pluck('to_user_id');
        $hide_profile_id = HideSpecificUser::where('user_id', $currentUser->id)->where('is_hidden', true)->pluck('target_user_id');

        $query = User::query()
            ->whereNotNull('type')
            ->whereNot('id', Auth::id())
            ->whereNotIn('id', $hide_profile_id)
            ->whereNotIn('id', $flaggedUserIds)
            ->whereNotIn('id', $likedUserIds)
            ->where('status', 1)
            ->whereNotIn('id', function ($q) use ($currentUser) {
                $q->select('target_user_id')
                    ->from('hide_specific_users')
                    ->where('user_id', $currentUser->id);
            });

        // Add distance column and filter by distance if requested
        if ($request->filled('distance') && is_array($request->distance) && $currentUser->latitude && $currentUser->longitude) {
            $minDistance = (float) $request->distance[0];
            $maxDistance = (float) $request->distance[1];
            $query->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) as distance',
                [$currentUser->latitude, $currentUser->longitude, $currentUser->latitude]
            )->havingBetween('distance', [$minDistance, $maxDistance]);
        } else {
            $query->select(['id', 'age', 'name', 'country', 'caribbean_interest', 'city', 'gender', 'avatar', 'language', 'whyare', 'about_me', 'more_photos', 'uid']);
        }

        // Filters
        if ($request->filled('age') && is_array($request->age)) {
            $query->whereBetween('age', $request->age);
        }

        if ($request->filled('preferences') && is_array($request->preferences)) {
            $query->where(function ($q) use ($request) {
                foreach (array_unique($request->preferences) as $preference) {
                    $q->orWhere('gender', 'LIKE', '%' . $preference . '%');
                }
            });
        }

        if ($request->filled('caribbean_interest') && $request->caribbean_interest !== 'all') {
            $query->where('caribbean_interest', $request->caribbean_interest);
        }

        if ($request->filled('languages') && is_array($request->languages)) {
            $query->where(function ($q) use ($request) {
                foreach (array_unique($request->languages) as $lang) {
                    $q->orWhere('language', 'LIKE', '%' . $lang . '%');
                }
            });
        } elseif ($request->filled('languages')) {
            $query->where('language', 'LIKE', '%' . $request->languages . '%');
        }

        if ($request->filled('interests') && is_array($request->interests)) {
            $query->where(function ($q) use ($request) {
                foreach (array_unique($request->interests) as $interest) {
                    $q->orWhere('interests', 'LIKE', '%' . $interest . '%');
                }
            });
        }

        if ($request->filled('relationships') && is_array($request->relationships)) {
            $query->where(function ($q) use ($request) {
                foreach (array_unique($request->relationships) as $relationship) {
                    $q->orWhere('whyare', 'LIKE', '%' . $relationship . '%');
                }
            });
        }

        $users = $query
            ->whereNotNull('uid')
            ->inRandomOrder()
            ->paginate(10);

        $users->getCollection()->transform(function ($user) use ($indexService) {

            $user->country_flag = $user->country
                ? $indexService->getCountryFlag($user->country)
                : null;

            return $user;
        });

        $filters = $request->only([
            'age',
            'distance',
            'preferences',
            'link_me_with_country_name',
            'interests',
            'languages',
            'caribbean_interest',
            'religions',
            'relationships',
            'networkingOptions',
            'verificationStatus',
        ]);

        return Inertia::render('User/FindMatch/Index', [
            'users'            => $users,
            'filters'          => $filters,
            'currentUser'      => Auth::user(),
            'google_api_key'   => env('GOOGLE_MAPS_API_KEY'),
            'caribbeanCountry' => CaribbeanIsland::all(),
            'swipeAds'         => Advertisement::where('status', 1)->where('category', 'general')->get(),
        ]);
    }

    public function likeUser(User $user, LikeUserAction $likeUserAction)
    {
        try {
            $likeUserAction->execute($user);
            return back()->with('message', 'User liked successfully!');
        } catch (\Exception $e) {
            Log::error('Error occurred while liking user: ' . $e->getMessage());
            return back()->withErrors($e->getMessage());
        }
    }

    public function dislikeUser(User $user, LikeUserAction $likeUserAction)
    {
        $likeUserAction->disLike($user);

        return back()->with('message', 'User disliked successfully!');
    }

    public function getMatches(IndexControllerService $indexService)
    {
        $userLoggedin = User::select('id', 'name', 'coins', 'uid')
            ->findOrFail(Auth::id());
        $balance = $userLoggedin->coins ?? 0;

        $mutualMatches = User::query()
            ->select('users.uid', 'users.id', 'users.age', 'users.name', 'users.country', 'users.gender', 'users.more_photos', 'users.avatar', 'users.popularity_score', 'users.last_active', 'users.interests', 'users.caribbean_interest')
            ->where('users.id', '!=', $userLoggedin->id)
            ->whereExists(function ($query) use ($userLoggedin) {
                $query->selectRaw('1')
                    ->from('user_matches')
                    ->whereColumn('user_matches.target_user_id', 'users.id')
                    ->where('user_matches.user_id', $userLoggedin->id)
                    ->where('user_matches.status', 'like');
            })
            ->whereExists(function ($query) use ($userLoggedin) {
                $query->selectRaw('1')
                    ->from('user_matches')
                    ->whereColumn('user_matches.user_id', 'users.id')
                    ->where('user_matches.target_user_id', $userLoggedin->id)
                    ->where('user_matches.status', 'like');
            })
            ->get();

        $mutualMatches->transform(function ($user) use ($indexService) {
            $user->country_flag = $user->country
                ? $indexService->getCountryFlag($user->country)
                : null;

            return $user;
        });
        return Inertia::render('User/Match/Index', [
            'users' => $mutualMatches,
            'currentUser' => $userLoggedin,
            'google_api_key' => env('GOOGLE_MAPS_API_KEY'),
            'balance' => $balance,
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
        return Inertia::render('User/Likes/Index', [
            'users' => $likedByUsers,
            'currentUser'      => Auth::user(),
            'google_api_key'   => env('GOOGLE_MAPS_API_KEY'),
            'balance' => $balance,
        ]);
    }

    public function getLinkupUser(string $slug, string $user)
    {
        $user = $this->indexAction->userDetail($user);

        return Inertia::render('User/Profile/OtherProfile', [
            'userdata'               => $user['userdata'],
            'isFriends'              => $user['isFriends'],
            'isFriendRequestSent'    => $user['isFriendRequestSent'],
            'isHiddenByProfileOwner' => $user['isHiddenByProfileOwner'],
        ]);
    }

    public function calculateDistance($originCity, $originCountry, $destCity, $destCountry, $apiKey)
    {
        if (! $originCity || ! $originCountry || ! $destCity || ! $destCountry) {
            return null;
        }

        $origin      = urlencode("$originCity, $originCountry");
        $destination = urlencode("$destCity, $destCountry");

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&key={$apiKey}";

        try {
            $response = Http::get($url);
            $data     = $response->json();

            if (
                isset($data['rows'][0]['elements'][0]['status']) &&
                $data['rows'][0]['elements'][0]['status'] === 'OK'
            ) {
                return $data['rows'][0]['elements'][0]['distance']['text'];
            }
        } catch (\Exception $e) {
            // Optionally log
        }

        return null;
    }

    public function hideFromUser()
    {
        $currentUser = Auth::user();

        // Get target user ID from form data
        $targetUserId = request()->input('user_id');

        // Prevent user from hiding from themselves
        if ($currentUser->id === $targetUserId) {
            return back()->with('error', 'You cannot hide from yourself.');
        }

        // Get target user for response message
        $targetUser = User::find($targetUserId);

        // Toggle the hide status
        $hideRecord = HideSpecificUser::where('user_id', $currentUser->id)
            ->where('target_user_id', $targetUserId)
            ->first();

        if ($hideRecord) {
            // If record exists, toggle the is_hidden status
            $hideRecord->is_hidden = ! $hideRecord->is_hidden;
            $hideRecord->save();
            $message = $hideRecord->is_hidden ?
                "You are now hidden from {$targetUser->name}" :
                "You are now visible to {$targetUser->name}";
        } else {
            // Create new hide record
            $hideData = [
                'user_id'        => $currentUser->id,
                'target_user_id' => $targetUserId,
                'is_hidden'      => true,
            ];


            HideSpecificUser::create($hideData);
            $message = "You are now hidden from {$targetUser->name}";
        }


        return back()->with('success', $message);
    }

    public function blockUser(Request $request)
    {

        $this->indexAction->blockUser($request);

        return back()->with('success', 'User blocked successfully');
    }

    public function unblockUser(Request $request)
    {
        $currentUser   = Auth::user();
        $blockedUserId = $request->input('blocked_user_id');
        $result = BlockedUser::unblockUser($currentUser->id, $blockedUserId);

        return back()->with('success', 'User unblocked successfully');
    }


    public function storeGiftCoins(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'gift_name' => 'required|string',
            'coins' => 'required|integer|min:0',
        ]);

        $this->indexAction->sendGift($request);

        return back()->with('success', 'Gift sent successfully!');
    }

    public function getReceivedGifts(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $gifts = GiftCoins::with('sender')
            ->where('recieved_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($gift) {
                return [
                    'id' => $gift->id,
                    'sender_id' => $gift->sender_id,
                    'recieved_id' => $gift->recieved_id,
                    'name' => $gift->name,
                    'coins' => $gift->coins,
                    'status' => $gift->status,
                    'created_at' => $gift->created_at,
                    'viewed_at' => $gift->viewed_at ?? null,
                    'responded_at' => $gift->responded_at ?? null,
                    'sender' => $gift->sender ? [
                        'id' => $gift->sender->id,
                        'name' => $gift->sender->name,
                        'avatar' => $gift->sender->avatar,
                    ] : null,
                ];
            });

        return response()->json(['gifts' => $gifts]);
    }

    public function markGiftAsRead(Request $request, $giftId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $gift = GiftCoins::where('id', $giftId)
            ->where('recieved_id', $user->id)
            ->first();

        if (!$gift) {
            return response()->json(['error' => 'Gift not found'], 404);
        }

        // Add viewed_at timestamp if not already present
        if (!$gift->viewed_at) {
            $gift->viewed_at = now();
            $gift->save();
        }

        return response()->json(['success' => true]);
    }

    public function storeGiftReply(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $sender = Auth::user();
        $receiver = User::find($request->receiver_id);

        Notification::create([
            'title'    => 'New Reply',
            'message'  => "{$sender->name} replied to your gift: \"{$request->message}\"",
            'send_by'  => $sender->id,
            'user_id'  => $receiver->id,
            'type'     => 'quick_reply',
            'unread'   => true,
            'avatar'   => $sender->avatar ?? null,
            'metadata' => [
                'sender_id'   => $sender->id,
                'sender_name' => $sender->name,
                'reply_message' => $request->message,
            ],
        ]);

        return response()->json(['success' => true, 'message' => 'Reply sent successfully!']);
    }

    public function getRecentNotifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->where('unread', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    public function getUnreadNotificationCount(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('unread', true)
            ->count();

        return response()->json(['unreadCount' => $unreadCount]);
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->unread = false;
            $notification->save();
        }

        return response()->json(['success' => true]);
    }
}
