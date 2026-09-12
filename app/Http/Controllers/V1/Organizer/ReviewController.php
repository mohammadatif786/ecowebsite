<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventReview;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for organizer's events.
     */
    public function index()
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => true,
                'events' => [],
                'reviews' => []
            ]);
        }

        // Get all events that belong to this organizer and have reviews
        $eventsWithReviews = LinkUpEvent::where('organizer_id', $organizerProfile->id)
            ->whereHas('reviews')
            ->with(['reviews' => function ($query) {
                $query->select('id', 'event_id', 'user_id', 'rating', 'review_text', 'reviewer_name', 'status', 'created_at');
            }])
            ->get(['id', 'title', 'organizer_name']);

        // Transform the data for the frontend
        $events = $eventsWithReviews->map(function ($event) use ($user) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'organizer_name' => $event->organizer_name,
                'user_avatar' => $user->avatar,
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
                        'is_visible' => $review->status === 'approved'
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'events' => $events,
            'reviews' => $events->pluck('reviews')->flatten(1)
        ]);
    }
}
