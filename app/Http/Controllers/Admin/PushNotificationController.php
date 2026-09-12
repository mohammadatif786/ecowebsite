<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Country;
use App\Models\Notification;
use App\Models\CaribbeanIsland;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Cloud\Core\Timestamp;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\ClientException;
use Pusher\PushNotifications\PushNotifications;

class PushNotificationController extends Controller
{
    public function view()
    {
        $countries = Country::all();
        $caribbean_islands = CaribbeanIsland::all();

        return Inertia::render('admin/pushNotification/Create', [
            'countries' => $countries,
            'caribbean_islands' => $caribbean_islands,
        ]);
    }
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'message' => 'required',
            'country' => 'nullable',
            'state' => 'nullable',
            'city' => 'nullable',
            'caribbean_island' => 'nullable',
        ]);

        // Get target users
        $query = User::query();

        if ($request->filled('country')) {
            $query->whereRaw('LOWER(country) = ?', [strtolower($request->country)]);
        }
        if ($request->filled('state')) {
            $query->whereRaw('LOWER(state) = ?', [strtolower($request->state)]);
        }
        if ($request->filled('city')) {
            $query->whereRaw('LOWER(city) = ?', [strtolower($request->city)]);
        }
        if ($request->filled('caribbean_island')) {
            $query->whereRaw('LOWER(caribbean_interest) = ?', [strtolower($request->caribbean_island)]);
        }

        $users = $query->get();

        // Initialize Pusher push notifications
        $pushNotifications = new PushNotifications([
            "instanceId" => env('PUSHER_INSTANCE_ID'),
            "secretKey" => env('PUSHER_PRIMARY_KEY'),
        ]);

        // Loop through users and send + save
        foreach ($users as $user) {
            $notification = Notification::create([
                'title'    => $request->title,
                'message'  => $request->message,
                'send_by'  => Auth::id(),
                'user_id'  => $user->id,
                'unread'   => true,
                'type'     => 'system',
                'priority' => false,
            ]);

            // Send push notification
            $pushNotifications->publishToInterests(
                ["user-{$user->id}"], // each user has their own interest
                [
                    "web" => [
                        "notification" => [
                            "title" => $notification->title,
                            "body"  => $notification->message,
                            "deep_link" => "https://linkupvibes.com",
                        ],
                    ],
                    "fcm" => [
                        "notification" => [
                            "title" => $notification->title,
                            "body"  => $notification->message,
                        ],
                        "data" => [
                            "deep_link" => "https://linkupvibes.com",
                        ],
                    ],
                ]
            );
        }

        return back()->withSuccess('Notification successfully sent to all users');
    }
}
