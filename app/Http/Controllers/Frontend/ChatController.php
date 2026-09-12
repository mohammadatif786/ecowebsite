<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Send a chat message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        $messageData = [
            'id' => uniqid(),
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_avatar' => $user->avatar ?? '/images/default-avatar.png',
            'message' => $request->message,
            'timestamp' => now()->toISOString(),
        ];

        // Broadcast the message
        broadcast(new ChatMessage($messageData));

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $messageData
        ]);
    }

    /**
     * Get recent messages (optional - for loading chat history)
     */
    public function getRecentMessages()
    {
        // For now, return empty array since we're focusing on real-time chat
        // In a real application, you might want to store messages in database
        return response()->json([
            'success' => true,
            'messages' => []
        ]);
    }
}
