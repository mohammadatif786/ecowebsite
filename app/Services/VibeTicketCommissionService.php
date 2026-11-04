<?php

namespace App\Services;

use App\Models\LinkUpEvent;
use App\Models\MarketplaceAffiliateEarning;
use App\Models\Vibe;
use Illuminate\Support\Collection;

/**
 * When someone buys tickets after tapping "Get Tickets" on a Vibe that tags the event, the
 * person who posted earns the ticket's commission (set by the organizer per ticket type).
 * Pending until the hold period passes, like other Vibe commissions.
 */
class VibeTicketCommissionService
{
    /**
     * @param  array  $cart  BuildTicketCartAction's cart (items carry `ticket`, `qty`, `price`)
     * @param  Collection|iterable  $ticketSales  the TicketSale rows the purchase created
     */
    public function record(?int $vibeId, LinkUpEvent $event, int $buyerId, array $cart, iterable $ticketSales): ?MarketplaceAffiliateEarning
    {
        if (! $vibeId) {
            return null;
        }

        $vibe = Vibe::find($vibeId);
        $posterId = (int) ($vibe?->created_by ?? 0);

        // Only a post that tags this event, and never the buyer or the organizer earning from themself.
        if (! $vibe || $posterId === $buyerId || $posterId === (int) $event->user_id
            || ! $vibe->events()->where('link_up_events.id', $event->id)->exists()) {
            return null;
        }

        $sale = 0.0;
        $commission = 0.0;
        foreach ($cart['items'] ?? [] as $item) {
            $ticket = $item['ticket'] ?? null;
            $qty = max(0, (int) ($item['qty'] ?? 0));
            $price = (float) ($item['price'] ?? 0);
            if (! $ticket || $qty === 0) {
                continue;
            }
            $sale += $price * $qty;
            $commission += match ($ticket->commMode) {
                'flat' => (float) $ticket->commFlat * $qty,
                'pct' => $price * $qty * ((float) $ticket->commission / 100),
                default => 0.0,
            };
        }

        if ($commission <= 0) {
            return null;
        }

        $firstSaleId = collect($ticketSales)->map(fn ($s) => (int) data_get($s, 'id'))->filter()->min();

        return MarketplaceAffiliateEarning::firstOrCreate([
            'vibe_id' => $vibe->id,
            'ticket_sale_id' => $firstSaleId,
        ], [
            'affiliate_user_id' => $posterId,
            'source' => $vibe->is_uvibe ? 'uvibe' : 'vibe',
            'title' => 'Tickets · '.$event->title,
            'sale_amount' => round($sale, 2),
            'commission_amount' => round($commission, 2),
            'status' => 'pending',
        ]);
    }
}
