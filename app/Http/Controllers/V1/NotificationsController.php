<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function getAllNotifications(Request $request)
    {
        // Assuming you have a Notification model and a user relationship
        $notifications =Notification::all();
        return response()->json($notifications);

        if($notifications->isEmpty()) {
            return response()->json(['message' => 'No notifications found'], 404);
        }
    }
}
