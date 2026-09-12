<?php

namespace App\Repositories;

use App\Models\LinkUpEvent;
use App\Models\Ticket;
use Illuminate\Support\Collection;

class TicketRepository
{
    public function getActiveTicketsForEvent(LinkUpEvent $event): Collection
    {
        return Ticket::where('event_id', $event->id)
            ->where('status', 1)
            ->with(['drinkPackage', 'extraSetting', 'wellnessSlotBlocks'])
            ->get();
    }
}
