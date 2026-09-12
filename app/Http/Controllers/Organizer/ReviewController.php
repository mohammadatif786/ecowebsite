<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\EventReview;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return Inertia::render('organizer/review/Index', [
                'events' => [],
                'reviews' => []
            ]);
        }

        // Get all events that belong to this organizer and have reviews
        // Using same robust logic as POS to identify organizer's events
        $eventsWithReviews = LinkUpEvent::where(function($q) use ($user, $organizerProfile) {
                $q->where('organizer_id', $organizerProfile->id)
                  ->orWhere('user_id', $user->id);
            })
            ->whereHas('reviews')
            ->with(['reviews' => function ($query) {
                $query->with('user:id,avatar')
                      ->select('id', 'event_id', 'user_id', 'rating', 'review_text', 'reviewer_name', 'status', 'created_at')
                      ->orderBy('created_at', 'desc');
            }])
            ->get(['id', 'title', 'organizer_name']);

        // Transform the data for the frontend
        $events = $eventsWithReviews->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'organizer_name' => $event->organizer_name,
                'reviews' => $event->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'event_id' => $review->event_id,
                        'user_id' => $review->user_id,
                        'rating' => $review->rating,
                        'review_text' => $review->review_text,
                        'reviewer_name' => $review->reviewer_name,
                        'status' => $review->status,
                        'created_at' => $review->created_at,
                        'is_visible' => true, // Show all reviews to organizer
                        'user_avatar' => $review->user ? $review->user->avatar : null
                    ];
                })
            ];
        });

        return Inertia::render('organizer/review/Index', [
            'events' => $events,
            'reviews' => $events->pluck('reviews')->flatten(1)
        ]);
    }
}
