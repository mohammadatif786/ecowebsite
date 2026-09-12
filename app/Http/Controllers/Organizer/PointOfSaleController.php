<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class PointOfSaleController extends Controller
{
    /**
     * Static placeholder for the POS fee summary navigation page.
     */
    public function feeSummary()
    {
        return Inertia::render('organizer/point_of_sales/FeeSummary');
    }

    /**
     * Display a listing of the points of sale.
     */
    public function index()
    {
        $userId = auth()->id();
        $organizer = OrganizerProfile::where('user_id', $userId)->first();
        $organizerId = $organizer ? $organizer->id : null;

        $pointsOfSale = PointOfSale::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pos) {
                return [
                    'id' => $pos->id,
                    'name' => $pos->name,
                    'username' => $pos->username,
                    'creationDate' => $pos->created_at->format('Y-m-d'),
                    'lastLogin' => $pos->last_login ? $pos->last_login->format('Y-m-d') : $pos->created_at->format('Y-m-d'),
                    'eventsCount' => 0,
                    'status' => $pos->status
                ];
            })->values()->all();

        // Fetch events for the terminal
        $events = [];
        if ($organizerId) {
            $events = LinkUpEvent::where(function($q) use ($userId, $organizerId) {
                    $q->where('organizer_id', $organizerId)
                      ->orWhere('user_id', $userId);
                })
                ->where('status', 'live')
                ->with(['tickets' => function ($query) {
                    $query->orderBy('price', 'asc');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($event) {
                    // Use the first ticket price as the base price for the grid, like in the reference
                    $basePrice = $event->tickets->first()?->price ?? 0;
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'image' => $event->image_url ?? 'https://placehold.co/400x300?text=Event',
                        'price' => $basePrice,
                        'tickets' => $event->tickets->map(function ($t) {
                            return [
                                'id' => $t->id,
                                'name' => $t->name,
                                'price' => $t->price
                            ];
                        })
                    ];
                });
        }

        // Today's Sales Summary
        $todaySales = 0;
        if ($organizerId) {
            $todaySales = TicketSale::whereIn('link_up_event_id', function ($query) use ($userId, $organizerId) {
                $query->select('id')->from('link_up_events')
                    ->where('organizer_id', $organizerId)
                    ->orWhere('user_id', $userId);
            })
                ->whereDate('created_at', now()->today())
                ->sum('total');
        }

        // Today's Transactions
        $todayTransactions = [];
        if ($organizerId) {
            $todayTransactions = TicketSale::with('event')
                ->whereIn('link_up_event_id', function ($query) use ($userId, $organizerId) {
                    $query->select('id')->from('link_up_events')
                        ->where('organizer_id', $organizerId)
                        ->orWhere('user_id', $userId);
                })
                ->whereDate('created_at', now()->today())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'event' => $t->event->title ?? 'Deleted Event',
                        'amount' => $t->total,
                        'qty' => $t->no_of_tickets,
                        'method' => $t->payment_method ?? 'Cash',
                        'time' => $t->created_at->format('H:i')
                    ];
                });
        }

        return Inertia::render('organizer/point_of_sales/Index', [
            'pointsOfSale' => $pointsOfSale,
            'events' => $events,
            'todaySales' => (float)$todaySales,
            'todayTransactions' => $todayTransactions
        ]);
    }

    /**
     * Store a newly created point of sale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:points_of_sale,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        PointOfSale::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'status' => 'enabled',
        ]);

        return redirect()->back()->withSuccess('Point of Sale created successfully');
    }

    /**
     * Record a POS ticket sale and return the confirmed backend receipt data.
     */
    public function charge(Request $request)
    {
        $validated = $request->validate([
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.id' => ['required', 'integer', 'exists:tickets,id'],
            'cart.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
            'payment_method' => ['required', 'string', Rule::in(['Cash', 'Card', 'Wallet Transfer'])],
        ]);

        $userId = auth()->id();
        $organizer = OrganizerProfile::where('user_id', $userId)->firstOrFail();
        $paymentMethod = $validated['payment_method'];
        $orderId = 'POS-' . Str::upper(Str::random(8));

        $ticketIds = collect($validated['cart'])->pluck('id')->unique()->values();

        $tickets = Ticket::query()
            ->with('event:id,title,organizer_id,user_id')
            ->whereIn('id', $ticketIds)
            ->whereHas('event', function ($query) use ($organizer, $userId) {
                $query->where('organizer_id', $organizer->id)
                    ->orWhere('user_id', $userId);
            })
            ->get()
            ->keyBy('id');

        if ($tickets->count() !== $ticketIds->count()) {
            throw ValidationException::withMessages([
                'cart' => 'One or more selected tickets are unavailable for this organizer.',
            ]);
        }

        $createdSales = DB::transaction(function () use ($validated, $tickets, $userId, $paymentMethod, $orderId) {
            $sales = collect();

            foreach ($validated['cart'] as $cartItem) {
                $ticket = $tickets->get((int) $cartItem['id']);
                $qty = (int) $cartItem['qty'];
                $unitPrice = round((float) ($ticket->price ?? 0), 2);

                for ($i = 0; $i < $qty; $i++) {
                    $uniqueCode = Str::upper(Str::random(20));

                    $sales->push(TicketSale::create([
                        'ticket_id' => $ticket->id,
                        'ticket_type' => $ticket->type,
                        'ticket_name' => $ticket->name,
                        'ticket_qrcode' => $uniqueCode,
                        'ticket_qrcode_id' => $uniqueCode,
                        'no_of_tickets' => 1,
                        'user_id' => $userId,
                        'link_up_event_id' => $ticket->event_id ?? $ticket->link_up_event_id,
                        'ticket_status' => 'confirmed',
                        'payment_method' => $paymentMethod,
                        'pay_type' => 'pos',
                        'stripe_id' => $orderId,
                        'sub_total' => $unitPrice,
                        'total' => $unitPrice,
                        'stripe_price' => $unitPrice,
                        'web_qrcode' => 'qr-codes/' . $uniqueCode . '.png',
                    ]));
                }
            }

            return $sales;
        });

        return response()->json([
            'orderId' => $orderId,
            'total' => round((float) $createdSales->sum('total'), 2),
            'method' => $paymentMethod,
            'time' => now()->format('H:i'),
            'lines' => $createdSales
                ->groupBy('ticket_id')
                ->map(function ($sales, $ticketId) use ($tickets) {
                    $first = $sales->first();
                    $ticket = $tickets->get((int) $ticketId);

                    return [
                        'id' => $first->ticket_id,
                        'eventTitle' => $ticket?->event?->title ?? 'Event',
                        'ticketName' => $first->ticket_name,
                        'price' => (float) $first->total,
                        'qty' => $sales->count(),
                    ];
                })
                ->values(),
        ]);
    }

    /**
     * Update the specified point of sale.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:points_of_sale,username,' . $id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $pos = PointOfSale::where('user_id', auth()->id())->findOrFail($id);

        $pos->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
        ]);

        if (!empty($validated['password'])) {
            $pos->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->back()->withSuccess('Point of Sale updated successfully');
    }

    /**
     * Toggle the status of the specified point of sale.
     */
    public function toggleStatus($id)
    {
        $pos = PointOfSale::where('user_id', auth()->id())->findOrFail($id);

        $pos->update([
            'status' => $pos->status === 'enabled' ? 'disabled' : 'enabled'
        ]);

        return redirect()->back()->withSuccess('Point of Sale status updated successfully');
    }

    /**
     * Remove the specified point of sale.
     */
    public function destroy($id)
    {
        $pos = PointOfSale::where('user_id', auth()->id())->findOrFail($id);
        $pos->delete();

        return redirect()->back()->withSuccess('Point of Sale deleted successfully');
    }
}
