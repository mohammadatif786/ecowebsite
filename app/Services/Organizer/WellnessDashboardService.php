<?php

namespace App\Services\Organizer;

use App\Models\LinkUpEvent;
use App\Models\EventCategory;
use App\Models\OrganizerProfile;
use App\Models\TicketCheckin;
use App\Models\TicketSale;
use Carbon\Carbon;

class WellnessDashboardService
{
    /**
     * Build wellness dashboard payload for organizer dashboards.
     *
     * Returns:
     * - filters (events/currentPeriod/currentSpa)
     * - chart series (labels/revenue/counts/bookings/retention/topServices)
     * - computed KPIs and ops insights
     *
     * Notes:
     * - Cancelled sales are excluded from revenue/count metrics but are counted in cancellations.
     * - Checked-in is derived from ticket_checkins existence (ticket_sale_id present).
     *
     * @param OrganizerProfile $organizer
     * @param string $period
     * @param mixed $spaId
     * @return array
     */
    public function getDashboardData(OrganizerProfile $organizer, string $period = 'hourly', $spaId = 'all'): array
    {
        $events = $this->getWellnessEvents($organizer);
        $ticketsQuery = $this->getOrganizerWellnessTicketsQuery($organizer, $spaId);

        $complimentaryQuery = (clone $ticketsQuery)
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->where(function ($q) {
                $q->where('total', 0)
                    ->orWhere('stripe_price', 0);
            });

        $complimentaryTickets = (int) (clone $complimentaryQuery)->sum('no_of_tickets');
        $complimentaryOrders = (int) (clone $complimentaryQuery)->count();

        $startDate = $this->getPeriodStartDate($period);
        $periodSales = $this->getPeriodSales($ticketsQuery, $startDate);

        $series = $this->buildSeries($periodSales, $period);
        $kpis = $this->computePeriodKpis($series);
        $ops = $this->computeOps($periodSales, $series, $kpis);

        $totalTaxes = (float) $periodSales
            ->filter(fn($s) => ($s->ticket_status ?? null) !== 'cancelled')
            ->sum('event_tax');

        // POS Data matching HomeDashboardService logic
        $posSalesQuery = (clone $ticketsQuery)
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->where(function ($query) {
                $query->where('pay_type', 'pos')
                    ->orWhere('stripe_id', 'like', 'POS-%');
            });

        $posSalesRevenue = (float) (clone $posSalesQuery)->sum('total');
        $posTicketsSold = (int) (clone $posSalesQuery)->sum('no_of_tickets');
        $posOrdersCount = (int) (clone $posSalesQuery)->distinct('stripe_id')->count('stripe_id');

        $kpis['totalTaxes'] = round($totalTaxes, 2);
        $kpis['posSalesRevenue'] = round((float) $posSalesRevenue, 2);
        $kpis['posTicketsSold'] = $posTicketsSold;
        $kpis['posOrdersCount'] = $posOrdersCount;

        $grossByLabel = $kpis['grossByLabel'] ?? [];
        unset($kpis['grossByLabel']);

        $kpis['complimentaryTickets'] = $complimentaryTickets;
        $kpis['complimentaryOrders'] = $complimentaryOrders;

        return [
            'events' => $events,
            'currentPeriod' => $period,
            'currentSpa' => $spaId,
            'kpis' => $kpis,
            'charts' => [
                'timeline' => [
                    'labels' => $series['labels'],
                    'grossByLabel' => $grossByLabel,
                    'counts' => $series['counts'],
                ],
                'serviceMix' => [
                    'bookingsByCategory' => $ops['serviceCategories'] ?? [],
                ],
                'revenueByCategory' => [
                    'revenueByCategory' => $ops['serviceRevenue'] ?? [],
                    'topContributorLabel' => $ops['revTop'] ?? null,
                ],
                'revenueByCategoryOverTime' => [
                    'series' => $series['revenue'],
                ],
                'bookingsTimeline' => [
                    'completed' => $series['bookings']['completed'] ?? [],
                    'canceled' => $series['bookings']['canceled'] ?? [],
                    'noshow' => $series['bookings']['noshow'] ?? [],
                ],
                'retention' => [
                    'returning' => $series['retention']['returning'] ?? [],
                    'new' => $series['retention']['new'] ?? [],
                ],
                'topServicesByRevenue' => [
                    'items' => $series['topServices'] ?? [],
                ],
                'quickStats' => [
                    'addonRevenue' => $ops['addonRevenue'] ?? 0,
                    'mobileRevenue' => $ops['mobileRevenue'] ?? 0,
                    'noShowCount' => $ops['noShowCount'] ?? 0,
                    'avgRating' => $ops['avgRating'] ?? 0,
                    'addonsSold' => $ops['quick']['addonsSold'] ?? 0,
                    'mobileBookings' => $ops['quick']['mobileBookings'] ?? 0,
                    'avgTicket' => $ops['quick']['avgTicket'] ?? 0,
                    'repeatRate' => $ops['quick']['repeatRate'] ?? 0,
                    'coordinator' => $ops['quick']['coordinator'] ?? '—',
                    'nextAvail' => $ops['quick']['nextAvail'] ?? 0,
                    'nextEta' => $ops['quick']['nextEta'] ?? '—',
                ],
                'meta' => $series['meta'] ?? [],
            ],
        ];
    }

    /**
     * Fetch organizer wellness events for the dashboard selector.
     *
     * @param OrganizerProfile $organizer
     * @return \Illuminate\Support\Collection
     */
    private function getWellnessEvents(OrganizerProfile $organizer)
    {
        // Dynamic category matching
        $targetNames = collect($organizer->categories ?? [])
            ->filter(fn($c) => str_contains(strtolower($c), 'wellness') || str_contains(strtolower($c), 'spa'))
            ->toArray();

        if (empty($targetNames)) {
            $targetNames = ['Wellness and Spa'];
        }

        return LinkUpEvent::query()
            ->where('organizer_id', $organizer->id)
            ->whereHas('category', function ($q) use ($targetNames) {
                $q->whereIn('name', $targetNames);
            })
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Base TicketSale query for wellness/spa sales scoped to the organizer (and optionally to one spa/event).
     *
     * @param OrganizerProfile $organizer
     * @param mixed $spaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getOrganizerWellnessTicketsQuery(OrganizerProfile $organizer, $spaId = 'all')
    {
        $targetNames = collect($organizer->categories ?? [])
            ->filter(fn($c) => str_contains(strtolower($c), 'wellness') || str_contains(strtolower($c), 'spa'))
            ->toArray();

        if (empty($targetNames)) {
            $targetNames = ['Wellness and Spa'];
        }

        $query = TicketSale::query()
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->whereHas('ticket.event', function ($q) use ($organizer, $targetNames) {
                $q->where('organizer_id', $organizer->id)
                  ->whereHas('category', function($qq) use ($targetNames) {
                      $qq->whereIn('name', $targetNames);
                  });
            });

        if ($spaId !== 'all' && !empty($spaId)) {
            $query->where('link_up_event_id', (int) $spaId);
        }

        return $query;
    }

    /**
     * Compute start date for the given dashboard period.
     *
     * - hourly: today
     * - daily: last 7 days
     * - weekly: last 4 weeks
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
     * Fetch period-scoped wellness ticket sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $ticketsQuery
     * @param Carbon $startDate
     * @return \Illuminate\Support\Collection
     */
    private function getPeriodSales($ticketsQuery, Carbon $startDate)
    {
        return (clone $ticketsQuery)
            ->where('created_at', '>=', $startDate)
            ->get([
                'id', 'created_at', 'ticket_name', 'ticket_status', 'stripe_id',
                'sub_total', 'total', 'event_tax', 'wellness_total', 'wellness_addons', 'fee_breakdown',
            ]);
    }

    /**
     * Determine the "active" bucket index used for KPI delta comparisons.
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
     * Build bucket map and display labels for the requested period.
     *
     * @param string $period
     * @return array{0: array, 1: array}
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
     * Map a service name to one of the dashboard categories.
     *
     * @param string $name
     * @return string
     */
    private function classifyService(string $name): string
    {
        $n = strtolower($name);
        if (
            str_contains($n, 'massage')
            || str_contains($n, 'deep tissue')
            || str_contains($n, 'swedish')
            || str_contains($n, 'hot stone')
            || str_contains($n, 'thai')
            || str_contains($n, 'sports')
            || str_contains($n, 'aromatherapy')
            || str_contains($n, 'prenatal')
            || str_contains($n, 'reflexology')
        ) {
            return 'massage';
        }
        if (str_contains($n, 'nail') || str_contains($n, 'mani') || str_contains($n, 'pedi')) {
            return 'nails';
        }
        if (str_contains($n, 'facial')) {
            return 'facials';
        }
        return 'wellness';
    }

    /**
     * Detect whether a wellness_addons payload indicates mobile mode.
     *
     * @param mixed $wellnessAddons
     * @return bool
     */
    private function hasMobileMode($wellnessAddons): bool
    {
        if (!is_array($wellnessAddons)) {
            return false;
        }

        foreach ($wellnessAddons as $a) {
            if (!is_array($a)) {
                continue;
            }
            if (($a['category'] ?? null) === 'wellness_mode') {
                $name = strtolower((string) ($a['name'] ?? ''));
                if (str_contains($name, 'mobile')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Extract the included service name from wellness_addons.
     *
     * Uses category: wellness_included_service.
     *
     * @param mixed $wellnessAddons
     * @return string|null
     */
    private function getIncludedServiceName($wellnessAddons): ?string
    {
        if (!is_array($wellnessAddons)) {
            return null;
        }

        foreach ($wellnessAddons as $a) {
            if (!is_array($a)) {
                continue;
            }
            if (($a['category'] ?? null) === 'wellness_included_service') {
                $name = trim((string) ($a['name'] ?? ''));
                if ($name !== '') {
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Extract the included service type from wellness_addons.
     *
     * Uses category: wellness_included_service and reads key: service_type.
     *
     * @param mixed $wellnessAddons
     * @return string|null
     */
    private function getIncludedServiceType($wellnessAddons): ?string
    {
        if (!is_array($wellnessAddons)) {
            return null;
        }

        foreach ($wellnessAddons as $a) {
            if (!is_array($a)) {
                continue;
            }
            if (($a['category'] ?? null) === 'wellness_included_service') {
                $type = trim((string) ($a['service_type'] ?? ''));
                if ($type !== '') {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Build the chart series arrays used by the wellness dashboard.
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

        $revenue = [
            'massage' => array_fill(0, $count, 0),
            'nails' => array_fill(0, $count, 0),
            'facials' => array_fill(0, $count, 0),
            'wellness' => array_fill(0, $count, 0),
            'addons' => array_fill(0, $count, 0),
            'mobile' => array_fill(0, $count, 0),
        ];

        $counts = [
            'massage' => array_fill(0, $count, 0),
            'nails' => array_fill(0, $count, 0),
            'facials' => array_fill(0, $count, 0),
            'wellness' => array_fill(0, $count, 0),
            'addons' => array_fill(0, $count, 0),
            'mobile' => array_fill(0, $count, 0),
        ];

        $bookings = [
            'completed' => array_fill(0, $count, 0),
            'canceled' => array_fill(0, $count, 0),
            'noshow' => array_fill(0, $count, 0),
        ];

        $retention = [
            'returning' => array_fill(0, $count, 0),
            'new' => array_fill(0, $count, 0),
        ];

        $keyToIndex = array_flip(array_values($buckets));
        $dateToLabel = $buckets;

        $topRevenueByName = [];
        $mobileRevenueTotal = 0;

        $checkedInSaleIds = TicketCheckin::query()
            ->whereIn('ticket_sale_id', $salesData->pluck('id')->filter()->values())
            ->pluck('ticket_sale_id')
            ->flip();

        foreach ($salesData as $sale) {
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

            $status = $sale->ticket_status ?? null;
            if ($status === 'cancelled') {
                $bookings['canceled'][$idx] += 1;
                continue;
            } else {
                $isCheckedIn = isset($checkedInSaleIds[$sale->id]);
                if ($isCheckedIn) {
                    $bookings['completed'][$idx] += 1;
                }
            }

            $retention['new'][$idx] += 1;

            $base = (float) ($sale->sub_total ?? 0);
            if ($base <= 0) {
                $base = max(0, (float) ($sale->total ?? 0) - (float) ($sale->wellness_total ?? 0));
            }

            $addons = $sale->wellness_addons;
            if (is_string($addons)) {
                $addons = json_decode($addons, true) ?: [];
            }
            if (!is_array($addons)) {
                $addons = [];
            }

            $includedServiceName = $this->getIncludedServiceName($addons);
            $includedServiceType = $this->getIncludedServiceType($addons);
            $serviceKey = $this->classifyService($includedServiceType ?: ($includedServiceName ?: (string) ($sale->ticket_name ?? 'Wellness')));
            $revenue[$serviceKey][$idx] += $base;
            $counts[$serviceKey][$idx] += 1;

            $topRevenueByName[$includedServiceName ?: (string) ($sale->ticket_name ?? 'Wellness')] = ($topRevenueByName[$includedServiceName ?: (string) ($sale->ticket_name ?? 'Wellness')] ?? 0) + $base;

            foreach ($addons as $addon) {
                if (!is_array($addon)) {
                    continue;
                }
                $cat = $addon['category'] ?? null;
                $name = (string) ($addon['name'] ?? 'Wellness');
                $qty = (int) ($addon['quantity'] ?? 0);
                $total = (float) ($addon['total_price'] ?? 0);

                if (in_array($cat, ['wellness_service', 'wellness_manual'], true)) {
                    $revenue['addons'][$idx] += $total;
                    $counts['addons'][$idx] += ($qty > 0 ? $qty : 1);
                    $topRevenueByName[$name] = ($topRevenueByName[$name] ?? 0) + $total;
                }
            }

            $isMobile = $this->hasMobileMode($addons);
            $feeBreakdown = $sale->fee_breakdown;
            if (is_string($feeBreakdown)) {
                $feeBreakdown = json_decode($feeBreakdown, true) ?: [];
            }
            $mobileFee = 0;
            if (is_array($feeBreakdown)) {
                $mobileFee = (float) ($feeBreakdown['mobile_fee_amount'] ?? 0);
            }
            if ($isMobile || $mobileFee > 0) {
                $revenue['mobile'][$idx] += $mobileFee;
                $counts['mobile'][$idx] += 1;
                $mobileRevenueTotal += $mobileFee;
            }
        }

        arsort($topRevenueByName);
        $topServices = [];
        foreach (array_slice($topRevenueByName, 0, 10, true) as $name => $val) {
            $topServices[] = ['name' => $name, 'val' => round((float) $val, 2)];
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'counts' => $counts,
            'bookings' => $bookings,
            'retention' => $retention,
            'topServices' => $topServices,
            'meta' => [
                'activeIndex' => $activeIndex,
                'prevIndex' => $prevIndex,
            ],
        ];
    }

    /**
     * Compute KPI totals and deltas from the built series.
     *
     * @param array $series
     * @return array
     */
    private function computePeriodKpis(array $series): array
    {
        $labels = $series['labels'] ?? [];
        $count = count($labels);

        $activeIndex = (int) ($series['meta']['activeIndex'] ?? max(0, $count - 1));
        $prevIndex = (int) ($series['meta']['prevIndex'] ?? max(0, $count - 2));
        $activeIndex = max(0, min($activeIndex, max(0, $count - 1)));
        $prevIndex = max(0, min($prevIndex, max(0, $count - 1)));

        $grossByLabel = array_fill(0, $count, 0);
        foreach (['massage', 'nails', 'facials', 'wellness', 'addons', 'mobile'] as $k) {
            $arr = $series['revenue'][$k] ?? [];
            foreach ($grossByLabel as $i => $v) {
                $grossByLabel[$i] += (float) ($arr[$i] ?? 0);
            }
        }

        $gross = array_sum($grossByLabel);
        $grossDeltaPct = 0;
        if (($grossByLabel[$prevIndex] ?? 0) > 0) {
            $grossDeltaPct = (($grossByLabel[$activeIndex] - $grossByLabel[$prevIndex]) / $grossByLabel[$prevIndex]) * 100;
        } elseif (($grossByLabel[$activeIndex] ?? 0) > 0) {
            $grossDeltaPct = 100;
        }

        $massageCount = array_sum($series['counts']['massage'] ?? []);
        $addonsCount = array_sum($series['counts']['addons'] ?? []);
        $checkins = array_sum($series['bookings']['completed'] ?? []);

        $massageDelta = (int) (($series['counts']['massage'][$activeIndex] ?? 0) - ($series['counts']['massage'][$prevIndex] ?? 0));
        $addonsDelta = (int) (($series['counts']['addons'][$activeIndex] ?? 0) - ($series['counts']['addons'][$prevIndex] ?? 0));
        $checkinDelta = (int) (($series['bookings']['completed'][$activeIndex] ?? 0) - ($series['bookings']['completed'][$prevIndex] ?? 0));

        return [
            'gross' => round((float) $gross, 2),
            'grossDeltaPct' => round((float) $grossDeltaPct, 1),
            'massages' => (int) $massageCount,
            'addonsCount' => (int) $addonsCount,
            'checkins' => (int) $checkins,
            'massageDelta' => $massageDelta,
            'addonsDelta' => $addonsDelta,
            'checkinsDelta' => $checkinDelta,
            'grossByLabel' => $grossByLabel,
        ];
    }

    /**
     * Compute operational metrics and insights from sales and series.
     *
     * @param \Illuminate\Support\Collection $salesData
     * @param array $series
     * @param array $kpis
     * @return array
     */
    private function computeOps($salesData, array $series, array $kpis): array
    {
        $serviceCategories = [
            'Massage Services' => array_sum($series['counts']['massage'] ?? []),
            'Nail Services' => array_sum($series['counts']['nails'] ?? []),
            'Facial Services' => array_sum($series['counts']['facials'] ?? []),
            'Wellness Treatments' => array_sum($series['counts']['wellness'] ?? []),
            'Service Add-Ons' => array_sum($series['counts']['addons'] ?? []),
            'Mobile Services' => array_sum($series['counts']['mobile'] ?? []),
        ];

        $serviceRevenue = [
            'Massage Services' => round((float) array_sum($series['revenue']['massage'] ?? []), 2),
            'Nail Services' => round((float) array_sum($series['revenue']['nails'] ?? []), 2),
            'Facial Services' => round((float) array_sum($series['revenue']['facials'] ?? []), 2),
            'Wellness Treatments' => round((float) array_sum($series['revenue']['wellness'] ?? []), 2),
            'Service Add-Ons' => round((float) array_sum($series['revenue']['addons'] ?? []), 2),
            'Mobile Services' => round((float) array_sum($series['revenue']['mobile'] ?? []), 2),
        ];

        $addonRevenue = $serviceRevenue['Service Add-Ons'];
        $mobileRevenue = $serviceRevenue['Mobile Services'];

        $noShowCount = array_sum($series['bookings']['canceled'] ?? []);

        $avgTicket = count($salesData) > 0 ? ((float) $kpis['gross'] / max(1, count($salesData))) : 0;

        $revTop = collect($serviceRevenue)->sortDesc()->keys()->first();

        return [
            'serviceCategories' => $serviceCategories,
            'serviceRevenue' => $serviceRevenue,
            'addonRevenue' => $addonRevenue,
            'mobileRevenue' => $mobileRevenue,
            'noShowCount' => (int) $noShowCount,
            'avgRating' => 4.8,
            'quick' => [
                'addonsSold' => (int) array_sum($series['counts']['addons'] ?? []),
                'mobileBookings' => (int) array_sum($series['counts']['mobile'] ?? []),
                'avgTicket' => round((float) $avgTicket, 2),
                'repeatRate' => 42,
                'coordinator' => '—',
                'nextAvail' => 0,
                'nextEta' => 'Tomorrow',
            ],
            'revTop' => $revTop,
        ];
    }
}
