<?php

namespace App\Services\Organizer;

use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\Payout;
use App\Models\ScanSignUser;
use App\Models\Sponsor;
use App\Models\TicketSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeDashboardService
{
    protected $ticketsQuery;

    /**
     * Get dashboard data including counts and revenue
     */
    public function getDashboardData(OrganizerProfile $organizer)
    {
        $this->ticketsQuery = $this->getOrganizerTicketsQuery($organizer);
        // Counts
        $soldCount = (clone $this->ticketsQuery)->where('ticket_status', 'confirmed')->count();
        $canceledCount = (clone $this->ticketsQuery)->where('ticket_status', 'cancelled')->count();
        $checkedInCount = (clone $this->ticketsQuery)
            ->where('ticket_status', 'confirmed')
            ->whereHas('checkins')
            ->count();

        // Revenue
        $grossRevenue = (clone $this->ticketsQuery)
            ->where('ticket_status', 'confirmed')
            ->sum(DB::raw('CASE WHEN stripe_price > 0 THEN stripe_price ELSE total END'));

        $netRevenue = $this->calculateNetRevenueSQL($this->ticketsQuery);

        return [
            'sold_ticket' => $soldCount,
            'canceled_ticket' => $canceledCount,
            'checked_in' => $checkedInCount,
            'gross_revenue' => (float) $grossRevenue,
            'net_revenue' => $netRevenue,
            'pending_payouts' => $this->pendingPayouts($organizer),
            'quick_stats' => $this->quickStats($organizer),
        ];
    }

    /**
     * Build organizer home dashboard payload used by Dashboard.vue.
     *
     * This dashboard is for non-cookout and non-wellness events.
     *
     * @param OrganizerProfile $organizer
     * @param string $period
     * @param mixed $eventId
     * @return array
     */
    public function getDashboardPayload(OrganizerProfile $organizer, string $period = 'hourly', $eventId = 'all'): array
    {
        $events = LinkUpEvent::query()
            ->where(function ($query) use ($organizer) {
                $query->where('organizer_id', $organizer->id)
                    ->orWhere('user_id', $organizer->user_id);
            })
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();

        $startDate = $this->getPeriodStartDate($period);
        $ticketsQuery = $this->getOrganizerTicketsQuery($organizer);

        if ($eventId !== 'all' && !empty($eventId)) {
            $ticketsQuery->where('link_up_event_id', (int) $eventId);
        }

        $salesData = (clone $ticketsQuery)
            ->where('created_at', '>=', $startDate)
            ->get([
                'id',
                'created_at',
                'total',
                'fee',
                'tax',
                'event_tax',
                'no_of_tickets',
                'drinks_total',
                'tables_total',
                'table_addons',
                'drink_addons',
                'ticket_status',
                'link_up_event_id',
                'fee_breakdown',
            ]);

        $series = $this->buildSeries($salesData, $period);

        $ops = $this->buildOpsData($salesData, $ticketsQuery);

        $kpis = $this->buildKpis($organizer, $ticketsQuery, $series);

        $totalTaxes = (float) $salesData
            ->filter(fn($s) => ($s->ticket_status ?? null) !== 'cancelled')
            ->sum('event_tax');

        $kpis['totalTaxes'] = round($totalTaxes, 2);

        $topEvents = $this->buildTopEvents($salesData, $events);

        return [
            'organizer' => [
                'id' => $organizer->id,
                'organizer_name' => $organizer->organizer_name,
            ],
            'events' => $events,
            'kpis' => $kpis,
            'selectedEventId' => $eventId !== 'all' ? (int) $eventId : null,
            'charts' => [
                'series' => $series,
                'ops' => $ops,
                'topEvents' => $topEvents,
            ],
        ];
    }

    /**
     * Calculate net revenue dynamically using fee rates in SQL
     */
    private function calculateNetRevenueSQL($ticketsQuery)
    {
        // Net = Gross - Platform Fees
        // Platform Fees = fee (service fee) + tax (processing fee) + drink_fees (platform share)
        $netRevenue = (clone $ticketsQuery)
            ->where('ticket_status', 'confirmed')
            ->sum(DB::raw('CASE WHEN stripe_price > 0 THEN stripe_price ELSE total END - (fee + tax + drink_fees)'));

        return (float) $netRevenue;
    }

    /**
     * Compute period start date for the dashboard selector.
     *
     * @param string $period
     * @return Carbon
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        $now = Carbon::now();

        if ($period === 'daily') {
            return $now->copy()->subDays(6)->startOfDay();
        }

        if ($period === 'weekly') {
            return $now->copy()->subWeeks(3)->startOfWeek();
        }

        return $now->copy()->startOfDay();
    }

    /**
     * Base query for organizer tickets (excluding certain ticket types)
     */
    private function getOrganizerTicketsQuery(OrganizerProfile $organizer)
    {
        return TicketSale::query()
            ->whereNotIn('ticket_type', [
                'Cookouts/Food',
                'Cookouts',
                'Wellness and Spa'
            ])
            ->whereHas('ticket.event', function ($query) use ($organizer) {
                $query->where('organizer_id', $organizer->id)
                    ->orWhere('user_id', $organizer->user_id);
            });
    }

    /**
     * Build chart series arrays consumed by Dashboard.vue.
     *
     * Notes:
     * - Cancelled sales are excluded from revenue/velocity series.
     * - Fees are computed from fee + tax if present.
     *
     * @param \Illuminate\Support\Collection $salesData
     * @param string $period
     * @return array
     */
    private function buildSeries($salesData, string $period): array
    {
        [$buckets, $labels] = $this->getPeriodBuckets($period);
        $count = count($labels);

        $activeIndex = $this->getActiveIndex($period, $count);
        $prevIndex = max(0, $activeIndex - 1);

        $tickets = array_fill(0, $count, 0);
        $drinks = array_fill(0, $count, 0);
        $merch = array_fill(0, $count, 0);
        $collected = array_fill(0, $count, 0);
        $fees = array_fill(0, $count, 0);
        $netAvail = array_fill(0, $count, 0);
        $velocity = array_fill(0, $count, 0);

        $keyToIndex = array_flip(array_values($buckets));
        $dateToLabel = $buckets;

        foreach ($salesData as $sale) {
            if (($sale->ticket_status ?? null) === 'cancelled') {
                continue;
            }

            $ts = Carbon::parse($sale->created_at);
            $idx = -1;

            if ($period === 'hourly') {
                $idx = $ts->hour;
            } elseif ($period === 'daily') {
                $k = $ts->format('Y-m-d');
                if (isset($dateToLabel[$k])) {
                    $idx = $keyToIndex[$dateToLabel[$k]] ?? -1;
                }
            } else {
                $k = $ts->format('o-W');
                if (isset($dateToLabel[$k])) {
                    $idx = $keyToIndex[$dateToLabel[$k]] ?? -1;
                }
            }

            if ($idx < 0 || $idx >= $count) {
                continue;
            }

            $gross = (float) ($sale->stripe_price > 0 ? $sale->stripe_price : $sale->total);
            $drinkTotal = (float) ($sale->drinks_total ?? 0);
            $tableTotal = $this->resolveTableTotal($sale);
            $ticketRev = max(0, $gross - $drinkTotal - $tableTotal);

            $tickets[$idx] += $ticketRev;
            $drinks[$idx] += $drinkTotal;
            $merch[$idx] += $tableTotal;

            $collected[$idx] += $gross;
            $f = (float) (($sale->fee ?? 0) + ($sale->tax ?? 0) + ($sale->drink_fees ?? 0));
            $fees[$idx] += $f;
            $netAvail[$idx] += ($gross - $f);

            $velocity[$idx] += (int) ($sale->no_of_tickets ?? 0);
        }

        return [
            'labels' => $labels,
            'tickets' => $tickets,
            'drinks' => $drinks,
            'merch' => $merch,
            'cashFlow' => [
                'collected' => $collected,
                'fees' => $fees,
                'netAvail' => $netAvail,
            ],
            'ticketVelocity' => $velocity,
            'meta' => [
                'activeIndex' => $activeIndex,
                'prevIndex' => $prevIndex,
            ],
        ];
    }

    /**
     * Determine the "active" bucket index used for KPI deltas.
     *
     * @param string $period
     * @param int $count
     * @return int
     */
    private function getActiveIndex(string $period, int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        if ($period === 'hourly') {
            return (int) min(max(0, Carbon::now()->hour), $count - 1);
        }

        return $count - 1;
    }

    /**
     * Build buckets/labels for hourly/daily/weekly series.
     *
     * @param string $period
     * @return array
     */
    private function getPeriodBuckets(string $period): array
    {
        $buckets = [];
        $labels = [];
        $now = Carbon::now();

        if ($period === 'hourly') {
            for ($i = 0; $i < 24; $i++) {
                $key = sprintf('%02d:00', $i);
                $buckets[$i] = $key;
                $labels[] = $key;
            }
            return [$buckets, $labels];
        }

        if ($period === 'daily') {
            for ($i = 6; $i >= 0; $i--) {
                $d = $now->copy()->subDays($i);
                $key = $d->format('D');
                $dateKey = $d->format('Y-m-d');
                $buckets[$dateKey] = $key;
                $labels[] = $key;
            }
            return [$buckets, $labels];
        }

        for ($i = 3; $i >= 0; $i--) {
            $d = $now->copy()->subWeeks($i);
            $key = 'Wk ' . $d->weekOfYear;
            $wKey = $d->format('o-W');
            $buckets[$wKey] = $key;
            $labels[] = $key;
        }

        return [$buckets, $labels];
    }

    /**
     * Build operational aggregates consumed by Dashboard.vue.
     *
     * @param \Illuminate\Support\Collection $salesData
     * @param \Illuminate\Database\Eloquent\Builder $ticketsQuery
     * @return array
     */
    private function buildOpsData($salesData, $ticketsQuery): array
    {
        $incomeSources = ['Tickets' => 0, 'Drinks' => 0, 'Tables' => 0, 'Merch' => 0];

        $totalDrinkRev = 0;
        $totalTableRev = 0;

        $drinkMixUnits = [];
        $barRevenue = [];

        foreach ($salesData as $sale) {
            if (($sale->ticket_status ?? null) === 'cancelled') {
                continue;
            }

            $drinkAddons = $sale->drink_addons;
            if (is_string($drinkAddons)) {
                $drinkAddons = json_decode($drinkAddons, true) ?: [];
            }
            if (!is_array($drinkAddons)) {
                $drinkAddons = [];
            }

            foreach ($drinkAddons as $d) {
                if (!is_array($d)) {
                    continue;
                }

                $name = (string) ($d['name'] ?? 'Drink');
                $qty = (int) ($d['quantity'] ?? 0);
                $totalPrice = (float) ($d['total_price'] ?? 0);

                $drinkMixUnits[$name] = ($drinkMixUnits[$name] ?? 0) + $qty;
                $barRevenue[$name] = ($barRevenue[$name] ?? 0) + $totalPrice;
            }

            $total = (float) ($sale->total ?? 0);
            $drinkTotal = (float) ($sale->drinks_total ?? 0);
            $tableTotal = $this->resolveTableTotal($sale);
            $ticketRev = max(0, $total - $drinkTotal - $tableTotal);

            $totalDrinkRev += $drinkTotal;
            $totalTableRev += $tableTotal;

            $incomeSources['Tickets'] += $ticketRev;
            $incomeSources['Drinks'] += $drinkTotal;
            $incomeSources['Tables'] += $tableTotal;
        }

        $checkedIn = (clone $ticketsQuery)
            ->where('ticket_status', 'confirmed')
            ->whereHas('checkins')
            ->count();

        $canceledTickets = (clone $ticketsQuery)
            ->where('ticket_status', 'cancelled')
            ->count();

        return [
            'drinkRevenue' => round((float) $totalDrinkRev, 2),
            'tableRevenue' => round((float) $totalTableRev, 2),
            'canceledTickets' => (int) $canceledTickets,
            'checkedIn' => (int) $checkedIn,
            'drinkMixUnits' => $this->topAssoc($drinkMixUnits, 5),
            'barRevenue' => $this->topAssoc($barRevenue, 5),
            'incomeSources' => array_map(fn($v) => round((float) $v, 2), $incomeSources),
        ];
    }

    /**
     * Return top-N entries for an associative array sorted descending by value.
     *
     * @param array $map
     * @param int $limit
     * @return array
     */
    private function topAssoc(array $map, int $limit = 5): array
    {
        arsort($map);
        return array_slice($map, 0, $limit, true);
    }

    /**
     * Build KPI block consumed by Dashboard.vue.
     *
     * @param OrganizerProfile $organizer
     * @param \Illuminate\Database\Eloquent\Builder $ticketsQuery
     * @return array
     */
    private function buildKpis(OrganizerProfile $organizer, $ticketsQuery, array $series): array
    {
        $confirmedQuery = (clone $ticketsQuery)->where('ticket_status', '!=', 'cancelled');

        $totalRevenue = (float) $confirmedQuery->sum(DB::raw('CASE WHEN stripe_price > 0 THEN stripe_price ELSE total END'));

        $netRevenue = (float) $this->calculateNetRevenueSQL($confirmedQuery);

        $ticketsSold = (int) (clone $ticketsQuery)->whereIn('ticket_status', ['confirmed', 'served'])->sum('no_of_tickets');
        $ticketsCancelled = (int) (clone $ticketsQuery)->where('ticket_status', 'cancelled')->sum('no_of_tickets');
        $posSalesQuery = (clone $ticketsQuery)
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->where(function ($query) {
                $query->where('pay_type', 'pos')
                    ->orWhere('stripe_id', 'like', 'POS-%');
            });
        $posSalesRevenue = (float) (clone $posSalesQuery)->sum('total');
        $posTicketsSold = (int) (clone $posSalesQuery)->sum('no_of_tickets');
        $posOrdersCount = (int) (clone $posSalesQuery)->distinct('stripe_id')->count('stripe_id');

        $complimentaryQuery = (clone $ticketsQuery)
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->where(function ($q) {
                $q->where('total', 0)
                    ->orWhere('stripe_price', 0);
            });

        $complimentaryTickets = (int) (clone $complimentaryQuery)->sum('no_of_tickets');
        $complimentaryOrders = (int) (clone $complimentaryQuery)->count();

        $eventsCount = LinkUpEvent::where('organizer_id', $organizer->id)->count();
        $sponsorsCount = Sponsor::whereHas('event', function ($query) use ($organizer) {
            $query->where('organizer_id', $organizer->id);
        })->count();

        $scannerCount = ScanSignUser::where('org_id', $organizer->id)->count();

        $pendingPayouts = Payout::where('organizer_id', $organizer->id)
            ->where('status', 'pending')
            ->sum('net_amount');

        $completedPayouts = Payout::where('organizer_id', $organizer->id)
            ->where('status', 'verified')
            ->count();

        $totalPayouts = Payout::where('organizer_id', $organizer->id)
            ->where('status', 'verified')
            ->sum('net_amount');

        $labels = $series['labels'] ?? [];
        $count = count($labels);
        $activeIndex = (int) ($series['meta']['activeIndex'] ?? max(0, $count - 1));
        $prevIndex = (int) ($series['meta']['prevIndex'] ?? max(0, $count - 2));
        $activeIndex = max(0, min($activeIndex, max(0, $count - 1)));
        $prevIndex = max(0, min($prevIndex, max(0, $count - 1)));

        $grossByLabel = $series['cashFlow']['collected'] ?? [];
        $netByLabel = $series['cashFlow']['netAvail'] ?? [];
        $ticketsByLabel = $series['ticketVelocity'] ?? [];

        $grossDeltaPct = 0;
        if (($grossByLabel[$prevIndex] ?? 0) > 0) {
            $grossDeltaPct = ((($grossByLabel[$activeIndex] ?? 0) - ($grossByLabel[$prevIndex] ?? 0)) / ($grossByLabel[$prevIndex] ?? 1)) * 100;
        } elseif (($grossByLabel[$activeIndex] ?? 0) > 0) {
            $grossDeltaPct = 100;
        }

        $netDeltaPct = 0;
        if (($netByLabel[$prevIndex] ?? 0) > 0) {
            $netDeltaPct = ((($netByLabel[$activeIndex] ?? 0) - ($netByLabel[$prevIndex] ?? 0)) / ($netByLabel[$prevIndex] ?? 1)) * 100;
        } elseif (($netByLabel[$activeIndex] ?? 0) > 0) {
            $netDeltaPct = 100;
        }

        $ticketsDelta = (int) (($ticketsByLabel[$activeIndex] ?? 0) - ($ticketsByLabel[$prevIndex] ?? 0));

        return [
            'totalRevenue' => round((float) $totalRevenue, 2),
            'netRevenue' => round((float) $netRevenue, 2),
            'ticketsSold' => $ticketsSold,
            'ticketsCancelled' => $ticketsCancelled,
            'posSalesRevenue' => round((float) $posSalesRevenue, 2),
            'posTicketsSold' => $posTicketsSold,
            'posOrdersCount' => $posOrdersCount,
            'complimentaryTickets' => $complimentaryTickets,
            'complimentaryOrders' => $complimentaryOrders,
            'eventsCount' => (int) $eventsCount,
            'sponsorsCount' => (int) $sponsorsCount,
            'scannerCount' => (int) $scannerCount,
            'pending_payouts' => round((float) $pendingPayouts, 2),
            'completed_payouts' => (int) $completedPayouts,
            'total_payouts' => round((float) $totalPayouts, 2),
            'grossDeltaPct' => round((float) $grossDeltaPct, 1),
            'netDeltaPct' => round((float) $netDeltaPct, 1),
            'ticketsDelta' => $ticketsDelta,
        ];
    }

    /**
     * Build top events list (by revenue) for Dashboard.vue.
     *
     * @param \Illuminate\Support\Collection $salesData
     * @param \Illuminate\Support\Collection $events
     * @return array
     */
    private function buildTopEvents($salesData, $events): array
    {
        $grouped = $salesData
            ->filter(fn($s) => ($s->ticket_status ?? null) !== 'cancelled')
            ->groupBy('link_up_event_id');

        $result = [];
        foreach ($grouped as $eventId => $sales) {
            $rev = (float) $sales->sum(fn($s) => (float) ($s->stripe_price > 0 ? $s->stripe_price : $s->total));
            $ev = $events->firstWhere('id', $eventId);
            $name = $ev ? ($ev->title ?? "Event #{$eventId}") : "Event #{$eventId}";
            $result[] = ['name' => $name, 'val' => round($rev, 2)];
        }

        usort($result, fn($a, $b) => $b['val'] <=> $a['val']);

        return array_slice($result, 0, 5);
    }

    /**
     * Calculate pending payouts
     */
    private function pendingPayouts(OrganizerProfile $organizer)
    {
        $pendingPayouts = Payout::where('organizer_id', $organizer->id)
            ->where('status', 'verified')
            ->sum('net_amount');

        return $pendingPayouts;
    }

    /**
     * Count Quick Stats
     */
    private function quickStats(OrganizerProfile $organizer)
    {
        $this->ticketsQuery = $this->getOrganizerTicketsQuery($organizer);
        $countEvent = LinkUpEvent::where('organizer_id', $organizer->id)->count();

        $countSponsor = Sponsor::whereHas('event', function ($query) use ($organizer) {
            $query->where('organizer_id', $organizer->id);
        })->count();

        $countScanners = ScanSignUser::where('user_id', $organizer->id)->count();

        $canceledCount = (clone $this->ticketsQuery)->where('ticket_status', 'cancelled')->count();


        return [
            'countEvent' => $countEvent,
            'countSponsor' => $countSponsor,
            'countScanners' => $countScanners,
            'canceled_ticket_sales' => $canceledCount,
        ];
    }
    /**
     * Resolve table total with fallback to table_addons JSON if tables_total is 0.
     */
    private function resolveTableTotal($sale): float
    {
        $tableTotal = (float) ($sale->tables_total ?? 0);
        if ($tableTotal <= 0 && !empty($sale->table_addons)) {
            $addons = $sale->table_addons;
            if (is_string($addons)) {
                $addons = json_decode($addons, true) ?: [];
            }
            if (is_array($addons)) {
                foreach ($addons as $addon) {
                    $uPrice = (float) ($addon['unit_price'] ?? 0);
                    $qty = (int) ($addon['quantity'] ?? 1);
                    $tPrice = (float) ($addon['total_price'] ?? 0);
                    $tableTotal += ($tPrice != 0 ? $tPrice : ($uPrice * $qty));
                }
            }
        }
        return $tableTotal;
    }
}
