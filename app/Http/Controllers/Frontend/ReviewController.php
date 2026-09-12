<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EventReview;
use App\Models\LinkUpEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a new review for an event
     */
    public function store(Request $request, LinkUpEvent $event)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'reviewer_name' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already reviewed this event
        if (Auth::check()) {
            $existingReview = EventReview::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this event.'
                ], 409);
            }
        }

        $review = EventReview::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'reviewer_name' => $request->reviewer_name,
            'status' => 'approved' // Auto-approve for now, can be changed to 'pending'
        ]);
        return back()->with(['message', 'Review Submit Successfully']);

    }

    /**
     * Get reviews for an event (top 5 latest)
     */
    public function index(LinkUpEvent $event)
    {
        $userId = Auth::id();
        $isEventOwner = Auth::check() && ($event->organizer_id === $userId || $event->user_id === $userId);

        $reviews = $event->approvedReviews()
            ->orderBy('created_at', 'desc')
            ->take(5) // Limit to top 5 latest reviews
            ->get()
            ->map(function ($review) use ($userId, $isEventOwner) {
                $isReviewAuthor = Auth::check() && $userId === $review->user_id;
                
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'reviewer_name' => $review->reviewer_name,
                    'user_id' => $review->user_id,
                    'created_at' => $review->created_at->format('M d, Y'),
                    'can_edit' => $isReviewAuthor,
                    'can_delete' => $isEventOwner || $isReviewAuthor
                ];
            });

        $averageRating = $event->average_rating;
        $reviewsCount = $event->reviews_count;

        // Check if current user has already reviewed this event
        $userHasReviewed = false;
        if (Auth::check()) {
            $userHasReviewed = EventReview::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->exists();
        }

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'reviews_count' => $reviewsCount,
            'user_has_reviewed' => $userHasReviewed
        ]);
    }

    /**
     * Update an existing review
     */
    public function update(Request $request, LinkUpEvent $event, EventReview $review)
    {
        // Check if the authenticated user owns this review
        if (!Auth::check() || Auth::id() !== $review->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own reviews.'
            ], 403);
        }

        // Check if the review belongs to this event
        if ($review->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Review does not belong to this event.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'reviewer_name' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update([
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'reviewer_name' => $request->reviewer_name,
        ]);

        return back()->with(['message', 'Review Updated Successfully']);
    }

    /**
     * Delete a review (only event owner or review author can delete)
     */
    public function destroy(Request $request, LinkUpEvent $event, EventReview $review)
    {
        // Check if the review belongs to this event
        if ($review->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Review does not belong to this event.'
            ], 404);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to delete reviews.'
            ], 401);
        }

        $userId = Auth::id();
        
        // Check if user is the event owner or the review author
        $isEventOwner = $event->organizer_id === $userId || $event->user_id === $userId;
        $isReviewAuthor = $review->user_id === $userId;

        if (!$isEventOwner && !$isReviewAuthor) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own reviews or reviews on your events.'
            ], 403);
        }

        // Delete the review
        $review->delete();

        return back()->with(['message', 'Review deleted successfully!']);
    }
}
