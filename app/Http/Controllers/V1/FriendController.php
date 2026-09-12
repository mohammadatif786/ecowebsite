<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Frontend\FriendRequest;
use App\Services\IndexControllerService;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    public function index(IndexControllerService $indexService)
    {
        $userLoggedin = Auth::user();
        $balance = $userLoggedin->coins ?? 0;

        $friends = $userLoggedin->friends()
            ->orderBy('name', 'asc')
            ->select('id', 'uid', 'name', 'avatar', 'country', 'more_photos', 'distance_filter', 'whyare')
            ->get();

        $friends->transform(function ($user) use ($indexService) {
            $user->country_flag = $user->country
                ? $indexService->getCountryFlag($user->country)
                : null;

            return $user;
        });

        return response()->json([
            'status' => true,
            'users' => $friends,
            'currentUser' => $userLoggedin,
            'google_api_key' => env('GOOGLE_MAPS_API_KEY'),
            'balance' => $balance,
        ]);
    }

    public function unfriend($id)
    {
        FriendRequest::where(function ($query) use ($id) {
            $query->where('user_id', Auth::id())
                ->where('receiver_id', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('user_id', $id)
                ->where('receiver_id', Auth::id());
        })->delete();

        return response()->json([
            'status' => true,
            'message' => 'Unfriended successfully!',
        ]);
    }
}
