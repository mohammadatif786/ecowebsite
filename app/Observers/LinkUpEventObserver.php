<?php

namespace App\Observers;

use App\Models\LinkUpEvent;
use App\Services\SlugGenerator;

class LinkUpEventObserver
{
    public function __construct(
        private readonly SlugGenerator $slugs,
    ) {}

    public function creating(LinkUpEvent $event): void
    {
        if (blank($event->slug)) {
            $event->slug = $this->slugs->unique(
                $event->title ?: 'event',
                'link_up_events'
            );
        }
    }
}
