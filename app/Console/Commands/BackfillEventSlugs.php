<?php

namespace App\Console\Commands;

use App\Models\LinkUpEvent;
use App\Services\SlugGenerator;
use Illuminate\Console\Command;

class BackfillEventSlugs extends Command
{
    protected $signature = 'events:backfill-slugs';

    protected $description = 'Generate slugs for existing events that do not have one yet';

    public function __construct(
        private readonly SlugGenerator $slugs,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = 0;

        LinkUpEvent::whereNull('slug')
            ->orWhere('slug', '')
            ->chunkById(100, function ($events) use (&$count) {
                foreach ($events as $event) {
                    $event->slug = $this->slugs->unique(
                        $event->title ?: ('event-' . $event->id),
                        'link_up_events',
                        ignoreId: $event->id
                    );
                    $event->saveQuietly(); // skip the observer, slug is already set
                    $this->line("Event {$event->id} -> {$event->slug}");
                    $count++;
                }
            });

        $this->info("Backfilled {$count} event slug(s).");

        return self::SUCCESS;
    }
}
