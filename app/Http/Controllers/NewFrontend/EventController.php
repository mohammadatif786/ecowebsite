<?php

namespace App\Http\Controllers\NewFrontend;
use App\Actions\NewFrontend\GetEventIndexDataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewFrontend\EventIndexRequest;
use App\DTOs\NewFrontend\EventIndexFilters;
use App\Models\Frontend\FavouriteEvent;
use App\Models\LinkUpEvent;
use App\Models\EventReview;
use App\Models\TicketSale;
use App\Models\CancellationRequests;
use App\Services\TaxRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
   public function events(EventIndexRequest $request, GetEventIndexDataAction $action)
    {
         $filters = EventIndexFilters::fromRequest($request);

         $data = $action->handle(Auth::user(), $filters);
        return Inertia::render('new_front/events/Index', $data);
    }

    public function toggleFavorite(LinkUpEvent $event)
    {
        $user = Auth::user();

        $favorite = FavouriteEvent::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $favorite) {
            FavouriteEvent::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
            ]);

            return back()->withSuccess("Added to favorite");
        }

        $favorite->delete();

        return back()->withSuccess("Removed from favorite");
    }

    public function taxRate(Request $request, TaxRateService $taxRateService): JsonResponse
    {
        $validated = $request->validate([
            'zip' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $taxRateService->getRate($validated);

        return response()->json($result, $result['status'] ?? 200);
    }

    public function cancelBooking(TicketSale $ticketSale)
    {
        abort_unless((int) $ticketSale->user_id === (int) Auth::id(), 403);

        $status = strtolower((string) $ticketSale->ticket_status);
        if (!in_array($status, ['cancelled', 'canceled'], true)) {
            $ticketSale->ticket_status = 'cancelled';
            $ticketSale->save();
        }

        return back()->withSuccess('Booking cancelled');
    }

    public function cancelRequest(TicketSale $ticketSale, Request $request)
    {
        abort_unless((int) $ticketSale->user_id === (int) Auth::id(), 403);

        $request->validate([
            'reason' => 'nullable|string|max:1000',
            'amount' => 'required|numeric',
        ]);

        $ticketSale->update(['ticket_status' => 'cancelled']);

        CancellationRequests::create([
            'user_id' => Auth::id(),
            'ticket_id' => $ticketSale->id,
            'event_id' => $ticketSale->link_up_event_id,
            'admin_reason' => $request->reason,
            'status' => 'Pending',
            'refund_amount' => $request->amount,
            'deduct_ammount' => (float) ($ticketSale->total ?? $ticketSale->stripe_price ?? 0) - (float) $request->amount,
        ]);

        return back()->withSuccess('Cancellation request submitted successfully');
    }

    public function reviewsIndex(LinkUpEvent $event)
    {
        $reviews = EventReview::where('event_id', $event->id)
            ->where(function($query) {
                $query->where('status', 'approved')
                      ->orWhere('user_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'name' => $review->reviewer_name,
                    'rating' => $review->rating,
                    'text' => $review->review_text,
                    'created_at' => $review->created_at,
                ];
            });

        return response()->json([
            'reviews' => $reviews
        ]);
    }

    public function reviewStore(Request $request, LinkUpEvent $event)
    {
        // Check if user has already reviewed this event
        $existingReview = EventReview::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->withErrors(['error' => 'You have already reviewed this event']);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'reviewer_name' => 'required|string|max:255',
        ]);

        EventReview::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
            'reviewer_name' => $validated['reviewer_name'],
            'status' => 'pending', // Reviews need approval
        ]);

        return back()->withSuccess('Review submitted successfully');
    }
}
