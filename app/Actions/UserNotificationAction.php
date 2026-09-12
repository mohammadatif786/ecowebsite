<?php

namespace App\Actions;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class UserNotificationAction
{
    public function getNotications(object $request)
    {
        $query = Notification::query()
            ->where('user_id', Auth::id())
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('type') && $request->type !== 'all') {
            if ($request->type === 'unread') {
                $query->unread();
            } elseif ($request->type === 'priority') {
                $query->priority();
            } else {
                $query->byType($request->type);
            }
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('context', 'like', "%{$search}%");
            });
        }

        $allnotifications = $query
            ->with([
                'sender:id,name,avatar',
                'sender.organizerProfile:id,user_id',
                'sender.organizerProfile.media',
                'user:id,name,avatar',
            ])
            ->latest()->take(10)->get()
            ->map(function ($notification) {

                $senderPhoto = $notification->sender?->organizerProfile?->media?->profile_photo;

                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'context' => $notification->context,
                    'unread' => $notification->unread,
                    'metadata' => $notification->metadata,
                    'priority' => $notification->priority,
                    'created_at' => $notification->created_at,

                    'sender' => [
                        'id' => $notification->sender?->id,
                        'name' => $notification->sender?->name,
                        'avatar' => $notification->sender?->avatar,
                        'photo' => $senderPhoto ? asset($senderPhoto) : null,
                    ],

                    'receiver' => [
                        'id' => $notification->user?->id,
                        'name' => $notification->user?->name,
                        'avatar' => $notification->user?->avatar,
                    ],
                ];
            });

        return [
            'allnotifications' => $allnotifications,
            'appURL' => config('app.url') . '/storage/',
        ];
    }
}
