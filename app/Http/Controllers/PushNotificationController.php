<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PushNotificationController extends Controller
{
    public function savePlayerId(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string',
        ]);

        $user = Auth::user();
        $user->fcmToken = $request->player_id;
        $user->save();

        return response()->json(['message' => 'Player ID saved']);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        if (!$user->fcmToken) {
            return response()->json(['error' => 'User has no player ID'], 400);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ONESIGNAL_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => env('ONESIGNAL_APP_ID'),
            'include_player_ids' => [$user->fcmToken],
            'headings' => ['en' => $request->title],
            'contents' => ['en' => $request->message],
        ]);

        return response()->json($response->json());
    }
}
