<?php

namespace App\Repositories;

use App\Models\LinkUpEvent;
use App\Models\Sponsor;
use Illuminate\Support\Collection;

class SponsorRepository
{
    public function getForEvent(LinkUpEvent $event): Collection
    {
        return Sponsor::where('link_up_event_id', $event->id)->get();
    }
}
