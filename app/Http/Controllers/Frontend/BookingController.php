<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TicketSale;
use App\Models\EventFeeSetting;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function upcomming()
    {
        $ticketSales = TicketSale::with(['event', 'event.eventDetails', 'ticket', 'SaleAddon'])
            ->where('user_id', Auth::id())
            ->where('ticket_status', 'confirmed')
            ->orderBy('id', 'desc')
            ->get();
        return Inertia::render("User/Bookings/Upcomming", [
            'ticketSales' => $ticketSales,
        ]);
    }

    public function completed()
    {
        $ticketSales = TicketSale::with(['event', 'ticket'])
            ->where('user_id', Auth::id())
            ->where('ticket_status', 'confirmed')
            ->whereHas('event', function ($query) {
                $query->where('end_time', '<', now());
            })
            ->get();

        return Inertia::render("User/Bookings/Completed", [
            'ticketSales' => $ticketSales,
        ]);
    }

    public function cancelled()
    {
        $ticketSales = TicketSale::with(['event', 'ticket', 'cancellationRequest'])
            ->where('user_id', auth()->id())
            ->where('ticket_status', 'cancelled')
            ->get();

        return Inertia::render("User/Bookings/Cancelled", [
            'ticketSales' => $ticketSales,
        ]);
    }

    public function cancel(TicketSale $ticketSale)
    {
        $ticketSale->ticket_status = 'cancelled';
        $ticketSale->save();
        return back()->withSuccess("Bookings cancelled");
    }

    public function eticket(string $ticketSale)
    {
        $ticketSale = TicketSale::where('ticket_qrcode', $ticketSale)->first();
        $ticketSale->load(['event', 'event.eventDetails', 'ticket', 'user', 'SaleAddon']);

        // Get all tickets with the same stripe_id (same purchase transaction)
        $ticketSales = TicketSale::with(['event', 'event.eventDetails', 'ticket', 'user', 'SaleAddon', 'checkins'])
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($ticketSale) {
                if ($ticketSale->stripe_id) {
                    // If stripe_id exists, get all tickets with same stripe_id
                    $query->where('stripe_id', $ticketSale->stripe_id);
                } else {
                    // If no stripe_id (free ticket), just get this ticket
                    $query->where('id', $ticketSale->id);
                }
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Build fees summary similar to SuccessPurchase for display
        $eventFeeSettings = EventFeeSetting::first();
        $serviceFeeTotal = 0;
        $processingFeeTotal = 0;
        $drinkFeesTotal = 0;
        $bottleFeesTotal = 0;
        $vipFeesTotal = 0;
        $mobileFeeTotal = 0;
        $spaFeesTotal = 0;
        $couponAmount = 0;

        $firstTicket = $ticketSales->first();
        if ($firstTicket) {
            $serviceFeeTotal = floatval($firstTicket->fee ?? 0);
            $processingFeeTotal = floatval($firstTicket->tax ?? 0);
            $couponAmount = floatval($firstTicket->coupan_amount ?? 0);

            $breakdown = is_array($firstTicket->fee_breakdown)
                ? $firstTicket->fee_breakdown
                : (is_string($firstTicket->fee_breakdown) ? json_decode($firstTicket->fee_breakdown, true) : []);

            $mobileFeeTotal = floatval($breakdown['mobile_fee_amount'] ?? 0);
            $drinkFeesTotal = floatval($breakdown['drink_fee_amount'] ?? ($firstTicket->drink_fees ?? 0));
            $bottleFeesTotal = floatval($breakdown['bottle_fee_amount'] ?? 0);
            $vipFeesTotal = floatval($breakdown['vip_package_fee_amount'] ?? 0);
            $spaFeesTotal = floatval($breakdown['spa_platform_fee_amount'] ?? 0);
            $cookoutFeesTotal = floatval($breakdown['cookout_fee_amount'] ?? 0);
        }

        if (!$drinkFeesTotal && $eventFeeSettings) {
            // Reconstruct drink/bottle fees from addons for legacy rows
            $drinkPct = floatval($eventFeeSettings->drink_fee_pct ?? 0) / 100.0;
            $bottlePct = floatval($eventFeeSettings->bottle_fee_pct ?? 0) / 100.0;
            foreach ($ticketSales as $row) {
                $addons = is_array($row->drink_addons) ? $row->drink_addons : [];
                foreach ($addons as $addon) {
                    $cat = $addon['category'] ?? '';
                    $totalPrice = floatval($addon['total_price'] ?? 0);
                    if (in_array($cat, ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks'])) {
                        $drinkFeesTotal += $totalPrice * $drinkPct;
                    } elseif ($cat === 'bottles') {
                        $bottleFeesTotal += $totalPrice * $bottlePct;
                    }
                }
            }
        }

        if (!$vipFeesTotal && $eventFeeSettings) {
            $vipPct = floatval($eventFeeSettings->vip_fee_pct ?? 0) / 100.0;
            if ($vipPct > 0) {
                $vipTicketSubtotal = 0.0;
                foreach ($ticketSales as $row) {
                    $pkg = $row->ticket;
                    $hasTable = $pkg?->has_table === 'yes';
                    if ($hasTable) {
                        $vipTicketSubtotal += floatval($row->sub_total ?? 0);
                    }
                }
                $vipFeesTotal = $vipTicketSubtotal * $vipPct;
            }
        }

        $totalSubtotal = $ticketSales->sum(function ($t) {
            return floatval($t->sub_total ?? 0);
        });
        $totalTables = $ticketSales->sum(function ($t) {
            return floatval($t->tables_total ?? 0);
        });
        $totalDrinks = $ticketSales->sum(function ($t) {
            return floatval($t->drinks_total ?? 0);
        });
        $totalCookout = $ticketSales->sum(function ($t) {
            return floatval($t->cookout_total ?? 0);
        });
        $totalWellness = $ticketSales->sum(function ($t) {
            return floatval($t->wellness_total ?? 0);
        });
        $totalTax = $ticketSales->max(function ($t) {
            return floatval($t->event_tax ?? 0);
        });

        $grandTotal = $totalSubtotal + $totalTables + $totalDrinks + $totalCookout + $totalWellness
            + $serviceFeeTotal + $processingFeeTotal + $drinkFeesTotal + $bottleFeesTotal + $vipFeesTotal + $mobileFeeTotal + $spaFeesTotal + $cookoutFeesTotal + $totalTax
            - $couponAmount;

        return Inertia::render('User/Bookings/ETicket', [
            'ticketSales' => $ticketSales,
            'app_url' => env('APP_URL'),
            'fees_summary' => [
                'service_fee_total' => $serviceFeeTotal,
                'processing_fee_total' => $processingFeeTotal,
                'drink_fees_total' => $drinkFeesTotal,
                'bottle_fees_total' => $bottleFeesTotal,
                'vip_fees_total' => $vipFeesTotal,
                'mobile_fee_total' => $mobileFeeTotal,
                'cookout_fees_total' => $cookoutFeesTotal,
                'coupon_amount' => $couponAmount,
                'drink_fee_pct' => $eventFeeSettings?->drink_fee_pct ?? 0,
                'bottle_fee_pct' => $eventFeeSettings?->bottle_fee_pct ?? 0,
                'vip_fee_pct' => $eventFeeSettings?->vip_fee_pct ?? 0,
                'subtotal' => $totalSubtotal + $totalTables + $totalDrinks + $totalCookout + $totalWellness,
                'total' => $grandTotal,
                'tax_total' => $totalTax,
            ],
        ]);
    }
}
