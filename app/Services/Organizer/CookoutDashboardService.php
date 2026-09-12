<?php

namespace App\Services\Organizer;

use App\Models\EventCategory;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\TicketSale;
use Carbon\Carbon;

/**
 * Service class for generating cookout dashboard data.
 */
class CookoutDashboardService
{
    /**
     * Build cookout dashboard payload for both web and mobile clients.
     *
     * Returns:
     * - period series (labels/revenue/counts/ops)
     * - computed KPIs/deltas/overview (no client-side calculations required)
     * - all-time totals for the selected scope
     *
     * Note:
     * - Revenue includes both "confirmed" and "served" sales so totals don't disappear when orders are served.
     * - Food collected is based on ticket_status="served".
     *
     * @param OrganizerProfile $organizer
     * @param string $period
     * @param mixed $eventId
     * @return array
     */
    public function getDashboardData(OrganizerProfile $organizer, string $period = 'hourly', $eventId = 'all')
    {
        $events = $this->getCookoutEvents($organizer, null);

        $ticketsQuery = $this->getOrganizerTicketsQuery($organizer, $eventId);
        $startDate = $this->getPeriodStartDate($period);

        $complimentaryQuery = (clone $ticketsQuery)
            ->where('ticket_status','confirmed')
            ->where(function ($q) {
                $q->where('total', 0)
                    ->orWhere('stripe_price', 0);
            });

        $complimentaryTickets = (int) (clone $complimentaryQuery)->sum('no_of_tickets');
        $complimentaryOrders = (int) (clone $complimentaryQuery)->count();

        $periodSales = $this->getPeriodSales($ticketsQuery, $startDate);
        $series = $this->buildSeries($periodSales, $period, $organizer->organizer_name ?? null);

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

        $kpiData = $this->computePeriodKpis($series);
        $overview = $this->computeOverview($kpiData, $series);

        return [
            'events' => $events,
            'currentPeriod' => $period,
            'currentView' => $eventId,
            'kpis' => [
                'totalRevenue' => $kpiData['totalRevenuePeriod'],
                'totalPlates' => $kpiData['totalPlatesPeriod'],
                'totalAddOns' => $kpiData['totalAddonsPeriod'],
                'totalOrders' => $kpiData['totalOrdersPeriod'],
                'foodCollected' => $kpiData['totalCollectedPeriod'],
                'drinkUnitsSold' => $kpiData['totalDrinkUnitsPeriod'],
                'complimentaryTickets' => $complimentaryTickets,
                'complimentaryOrders' => $complimentaryOrders,
                'foodRevenue' => $kpiData['totalFoodRevenuePeriod'],
                'drinkRevenue' => $kpiData['totalDrinkRevenuePeriod'],
                'manualAddOnRevenue' => $kpiData['totalManualAddonRevenuePeriod'],
                'totalTaxes' => round((float) $totalTaxes, 2),
                'avgOrder' => $kpiData['avgOrderPeriod'],
                'revenueDeltaPct' => $kpiData['revenueDeltaPct'],
                'platesDelta' => $kpiData['platesDelta'],
                'addOnsDelta' => $kpiData['addonsDelta'],
                'foodCollectedDelta' => $kpiData['collectedDelta'],
                'posSalesRevenue' => round((float) $posSalesRevenue, 2),
                'posTicketsSold' => $posTicketsSold,
                'posOrdersCount' => $posOrdersCount,
            ],
            'labels' => $series['labels'],
            'grossByLabel' => $kpiData['grossByLabel'],
            'counts' => $series['counts'],
            'charts' => [
                'proteinMix' => [
                    'unitsByName' => $series['ops']['proteinMixUnits'] ?? [],
                ],
                'revenueOverview' => [
                    'foodRevenue' => $kpiData['totalFoodRevenuePeriod'] ?? 0,
                    'drinkRevenue' => $kpiData['totalDrinkRevenuePeriod'] ?? 0,
                    'manualAddOnRevenue' => $kpiData['totalManualAddonRevenuePeriod'] ?? 0,
                    'topBucketLabel' => $overview['topBucketLabel'],
                    'topBucketValue' => $overview['topBucketValue'],
                    'topMain' => $overview['topMain'],
                ],
                'revenueByStream' => [
                    'labels' => ['Food Revenue', 'Drink Revenue', 'Manual Add-On Revenue'],
                    'data' => [
                        $kpiData['totalFoodRevenuePeriod'] ?? 0,
                        $kpiData['totalDrinkRevenuePeriod'] ?? 0,
                        $kpiData['totalManualAddonRevenuePeriod'] ?? 0,
                    ],
                ],
                'topMenuItemsByRevenue' => [
                    'items' => $series['ops']['topItems'] ?? [],
                ],
                'quickStats' => [
                    'totalOrders' => $kpiData['totalOrdersPeriod'] ?? 0,
                    'drinkUnitsSold' => $kpiData['totalDrinkUnitsPeriod'] ?? 0,
                    'mostPopularMain' => $overview['topMain'],
                    'mostPopularDrink' => $series['ops']['popularDrink'] ?? null,
                    'organizer' => $series['ops']['organizer'] ?? null,
                ],
            ],
        ];
    }

    /**
     * Resolve the "Cookouts" event category.
     *
     * @return EventCategory|null
     */
    private function getCookoutCategory(): ?EventCategory
    {
        return EventCategory::whereIn('name', ['Cookouts/Food', 'Food', 'Cookouts'])->first();
    }

    /**
     * Get organizer events in the cookout category (used for the dashboard selector).
     *
     * @param OrganizerProfile $organizer
     * @param EventCategory|null $category
     * @return \Illuminate\Support\Collection
     */
    private function getCookoutEvents(OrganizerProfile $organizer, ?EventCategory $category)
    {
        // Get all categories from profile that look like food/cookout
        $targetNames = collect($organizer->categories ?? [])
            ->filter(fn($c) => str_contains(strtolower($c), 'food') || str_contains(strtolower($c), 'cookout'))
            ->toArray();

        if (empty($targetNames)) {
            $targetNames = ['Cookouts/Food', 'Food', 'Cookouts'];
        }

        return LinkUpEvent::where('organizer_id', $organizer->id)
            ->whereHas('category', function ($query) use ($targetNames) {
                $query->whereIn('name', $targetNames);
            })->get();
    }

    /**
     * Base TicketSale query for cookout sales scoped to organizer and (optionally) a single event.
     *
     * @param OrganizerProfile $organizer
     * @param mixed $eventId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getOrganizerTicketsQuery(OrganizerProfile $organizer, $eventId = 'all')
    {
        // Find categories that match the cookout/food profile
        $targetNames = collect($organizer->categories ?? [])
            ->filter(fn($c) => str_contains(strtolower($c), 'food') || str_contains(strtolower($c), 'cookout'))
            ->toArray();

        if (empty($targetNames)) {
            $targetNames = ['Cookouts/Food', 'Food', 'Cookouts'];
        }

        $query = TicketSale::query()
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->whereHas('ticket.event', function ($query) use ($organizer, $targetNames) {
                $query->where('organizer_id', $organizer->id)
                    ->whereHas('category', function($q) use ($targetNames) {
                        $q->whereIn('name', $targetNames);
                    });
            });

        if ($eventId !== 'all' && !empty($eventId)) {
            $query->where('link_up_event_id', (int) $eventId);
        }

        return $query;
    }

    /**
     * Compute the start date for the requested period.
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
        $startDate = $now->copy()->startOfDay();

        if ($period === 'daily') {
            return $now->copy()->subDays(6)->startOfDay();
        }

        if ($period === 'weekly') {
            return $now->copy()->subWeeks(3)->startOfWeek();
        }

        return $startDate;
    }

    /**
     * Fetch period sales records needed to build time series charts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $ticketsQuery
     * @param Carbon $startDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPeriodSales($ticketsQuery, Carbon $startDate)
    {
        return (clone $ticketsQuery)
            ->where('created_at', '>=', $startDate)
            ->withCount('checkins')
            ->get([
                'id',
                'created_at',
                'sub_total',
                'total',
                'event_tax',
                'stripe_id',
                'no_of_tickets',
                'cookout_total',
                'cookout_included_protein',
                'cookout_addons',
                'drink_addons',
                'ticket_name',
                'ticket_status'
            ]);
    }

    /**
     * Compute period KPIs and deltas from the built series.
     *
     * Deltas compare the "active" bucket vs previous bucket (e.g. current hour vs previous hour).
     *
     * @param array $series
     * @return array
     */
    private function computePeriodKpis(array $series): array
    {
        $grossByLabel = [];
        $labels = $series['labels'] ?? [];
        $foodSeries = $series['revenue']['food'] ?? [];
        $drinksSeries = $series['revenue']['drinks'] ?? [];
        $manualSeries = $series['revenue']['manualAddons'] ?? [];
        $platesSeries = $series['counts']['plates'] ?? [];
        $addonsSeries = $series['counts']['addons'] ?? [];
        $ordersSeries = $series['counts']['orders'] ?? [];
        $collectedSeries = $series['counts']['collected'] ?? [];
        $drinkUnitsSeries = $series['counts']['drinkUnits'] ?? [];

        foreach ($labels as $i => $label) {
            $grossByLabel[] =
                (float) ($foodSeries[$i] ?? 0)
                + (float) ($drinksSeries[$i] ?? 0)
                + (float) ($manualSeries[$i] ?? 0);
        }

        $activeIndex = (int) ($series['meta']['activeIndex'] ?? max(0, count($grossByLabel) - 1));
        $prevIndex = (int) ($series['meta']['prevIndex'] ?? max(0, count($grossByLabel) - 2));

        $activeIndex = max(0, min($activeIndex, max(0, count($grossByLabel) - 1)));
        $prevIndex = max(0, min($prevIndex, max(0, count($grossByLabel) - 1)));

        $totalRevenuePeriod = array_sum($grossByLabel);
        $totalOrdersPeriod = array_sum($ordersSeries);
        $avgOrderPeriod = $totalOrdersPeriod > 0 ? ($totalRevenuePeriod / $totalOrdersPeriod) : 0;

        $revenueDeltaPct = 0;
        if (($grossByLabel[$prevIndex] ?? 0) > 0) {
            $revenueDeltaPct = (($grossByLabel[$activeIndex] - $grossByLabel[$prevIndex]) / $grossByLabel[$prevIndex]) * 100;
        } elseif (($grossByLabel[$activeIndex] ?? 0) > 0) {
            $revenueDeltaPct = 100;
        }

        return [
            'grossByLabel' => $grossByLabel,
            'totalRevenuePeriod' => round((float) $totalRevenuePeriod, 2),
            'totalPlatesPeriod' => (int) array_sum($platesSeries),
            'totalAddonsPeriod' => (int) array_sum($addonsSeries),
            'totalOrdersPeriod' => (int) $totalOrdersPeriod,
            'totalCollectedPeriod' => (int) array_sum($collectedSeries),
            'totalDrinkUnitsPeriod' => (int) array_sum($drinkUnitsSeries),
            'totalFoodRevenuePeriod' => round((float) array_sum($foodSeries), 2),
            'totalDrinkRevenuePeriod' => round((float) array_sum($drinksSeries), 2),
            'totalManualAddonRevenuePeriod' => round((float) array_sum($manualSeries), 2),
            'avgOrderPeriod' => round((float) $avgOrderPeriod, 2),
            'revenueDeltaPct' => round((float) $revenueDeltaPct, 1),
            'platesDelta' => (int) (($platesSeries[$activeIndex] ?? 0) - ($platesSeries[$prevIndex] ?? 0)),
            'addonsDelta' => (int) (($addonsSeries[$activeIndex] ?? 0) - ($addonsSeries[$prevIndex] ?? 0)),
            'collectedDelta' => (int) (($collectedSeries[$activeIndex] ?? 0) - ($collectedSeries[$prevIndex] ?? 0)),
        ];
    }

    /**
     * Compute non-series summary insights.
     *
     * @param array $kpiData
     * @param array $series
     * @return array
     */
    private function computeOverview(array $kpiData, array $series): array
    {
        $topBucket = [
            ['Food Revenue', (float) ($kpiData['totalFoodRevenuePeriod'] ?? 0)],
            ['Drink Revenue', (float) ($kpiData['totalDrinkRevenuePeriod'] ?? 0)],
            ['Manual Add-On Revenue', (float) ($kpiData['totalManualAddonRevenuePeriod'] ?? 0)],
        ];
        usort($topBucket, fn($a, $b) => $b[1] <=> $a[1]);
        $topBucket = $topBucket[0] ?? ['—', 0];

        $proteinMix = $series['ops']['proteinMixUnits'] ?? [];
        $topMain = '—';
        if (is_array($proteinMix) && !empty($proteinMix)) {
            $entries = $proteinMix;
            arsort($entries);
            $topMain = array_key_first($entries) ?? '—';
        }

        return [
            'topBucketLabel' => $topBucket[0],
            'topBucketValue' => round((float) $topBucket[1], 2),
            'topMain' => $topMain,
        ];
    }

    /**
     * Compute all-time totals for the selected scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $ticketsQuery
     * @return array
     */
    private function computeAllTimeTotals($ticketsQuery): array
    {
        $totalRevenue = (clone $ticketsQuery)->sum('stripe_price');
        $totalPlates = (clone $ticketsQuery)->sum('no_of_tickets');
        $avgOrder = $totalPlates > 0 ? (clone $ticketsQuery)->avg('total') : 0;

        $allAddons = $this->getAddonsData($ticketsQuery);
        $totalAddons = $allAddons->count();

        $baseFoodRevenue = (clone $ticketsQuery)->sum('sub_total');
        $foodAddonRevenue = $allAddons
            ->filter(fn($addon) => !in_array(($addon['category'] ?? null), ['cookout_drink', 'cookout_extra'], true))
            ->sum(fn($addon) => (float) ($addon['total_price'] ?? 0));

        $foodRevenue = (float) $baseFoodRevenue + (float) $foodAddonRevenue;
        $drinkRevenue = $allAddons
            ->filter(fn($addon) => ($addon['category'] ?? null) === 'cookout_drink')
            ->sum(fn($addon) => (float) ($addon['total_price'] ?? 0));

        $manualAddOnsRevenue = $allAddons
            ->filter(fn($addon) => ($addon['category'] ?? null) === 'cookout_extra')
            ->sum(fn($addon) => (float) ($addon['total_price'] ?? 0));

        $foodCollected = (int) (clone $ticketsQuery)
            ->where('ticket_status', 'confirmed')
            ->join('ticket_checkins as tc', 'tc.ticket_sale_id', '=', 'ticket_sales.id')
            ->count('tc.id');

        return [
            'total_revenue' => $totalRevenue,
            'total_plates' => $totalPlates,
            'total_addons' => $totalAddons,
            'food_collected' => $foodCollected,
            'food_revenue' => $foodRevenue,
            'drink_revenue' => $drinkRevenue,
            'manual_add_ons_revenue' => $manualAddOnsRevenue,
            'avg_order' => $avgOrder ? round($avgOrder, 2) : 0,
        ];
    }

    /**
     * Build time series arrays used for charts.
     *
     * Revenue model (cookouts):
     * - food revenue starts with TicketSale.sub_total
     * - cookout_addons are categorized into food/drinks/manual
     *
     * Ops:
     * - proteinMixUnits + topItems + popularDrink
     *
     * Counts:
     * - plates: no_of_tickets
     * - collected: only when ticket_status="served"
     *
     * @param array $salesData
     * @param string $period
     * @param string|null $organizerName
     * @return array
     */
    private function buildSeries($salesData, string $period, ?string $organizerName = null): array
    {
        [$buckets, $labels] = $this->getPeriodBuckets($period);
        $count = count($labels);

        $activeIndex = $this->getActiveIndex($period, $count);
        $prevIndex = max(0, $activeIndex - 1);

        $revenue = [
            'food' => array_fill(0, $count, 0),
            'drinks' => array_fill(0, $count, 0),
            'manualAddons' => array_fill(0, $count, 0),
        ];

        $counts = [
            'plates' => array_fill(0, $count, 0),
            'addons' => array_fill(0, $count, 0),
            'orders' => array_fill(0, $count, 0),
            'collected' => array_fill(0, $count, 0),
            'drinkUnits' => array_fill(0, $count, 0),
        ];

        $keyToIndex = array_flip(array_values($buckets));
        $dateToLabel = $buckets;

        $proteinMixUnits = [];
        $topItemRevenue = [];
        $drinkUnitsByName = [];

        $ordersSeen = [];
        for ($i = 0; $i < $count; $i++) {
            $ordersSeen[$i] = [];
        }

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

            $orderKey = !empty($sale->stripe_id) ? $sale->stripe_id : $sale->id;
            if (!isset($ordersSeen[$idx][$orderKey])) {
                $ordersSeen[$idx][$orderKey] = true;
                $counts['orders'][$idx] += 1;
            }

            $plates = (int) ($sale->no_of_tickets ?? 0);
            $counts['plates'][$idx] += $plates;

            $protein = $sale->cookout_included_protein ?: ($sale->ticket_name ?? 'Unknown');
            $proteinMixUnits[$protein] = ($proteinMixUnits[$protein] ?? 0) + $plates;

            $baseFood = (float) ($sale->sub_total ?? 0);
            if ($baseFood <= 0) {
                $baseFood = (float) ($sale->total ?? 0);
            }
            $revenue['food'][$idx] += $baseFood;

            if (($sale->ticket_status ?? null) === 'confirmed') {
                $counts['collected'][$idx] += (int) ($sale->checkins_count ?? 0);
            }

            $addons = is_array($sale->cookout_addons) ? $sale->cookout_addons : (is_string($sale->cookout_addons) ? (json_decode($sale->cookout_addons, true) ?: []) : []);
            foreach ($addons as $addon) {
                if (!is_array($addon)) {
                    continue;
                }
                $cat = $addon['category'] ?? null;
                $totalPrice = (float) ($addon['total_price'] ?? 0);
                $qty = (int) ($addon['quantity'] ?? $addon['qty'] ?? 0);
                $name = $addon['name'] ?? 'Addon';

                if ($cat === 'cookout_drink') {
                    $revenue['drinks'][$idx] += $totalPrice;
                    $counts['drinkUnits'][$idx] += $qty;
                    $drinkUnitsByName[$name] = ($drinkUnitsByName[$name] ?? 0) + $qty;
                } elseif ($cat === 'cookout_extra') {
                    $revenue['manualAddons'][$idx] += $totalPrice;
                    $counts['addons'][$idx] += $qty > 0 ? $qty : 1;
                } else {
                    $revenue['food'][$idx] += $totalPrice;
                    $counts['addons'][$idx] += $qty > 0 ? $qty : 1;
                }

                $topItemRevenue[$name] = ($topItemRevenue[$name] ?? 0) + $totalPrice;
            }

            if (!empty($sale->drink_addons) && is_array($sale->drink_addons)) {
                foreach ($sale->drink_addons as $d) {
                    if (!is_array($d)) {
                        continue;
                    }
                    $name = $d['name'] ?? 'Drink';
                    $qty = (int) ($d['quantity'] ?? 0);
                    $totalPrice = (float) ($d['total_price'] ?? 0);
                    $revenue['drinks'][$idx] += $totalPrice;
                    $counts['drinkUnits'][$idx] += $qty;
                    $drinkUnitsByName[$name] = ($drinkUnitsByName[$name] ?? 0) + $qty;
                    $topItemRevenue[$name] = ($topItemRevenue[$name] ?? 0) + $totalPrice;
                }
            }
        }

        arsort($proteinMixUnits);
        $proteinMixUnits = array_slice($proteinMixUnits, 0, 8, true);

        arsort($topItemRevenue);
        $topMenuItems = [];
        foreach (array_slice($topItemRevenue, 0, 8, true) as $name => $val) {
            $topMenuItems[] = ['name' => $name, 'val' => round((float) $val, 2)];
        }

        arsort($drinkUnitsByName);
        $popularDrink = array_key_first($drinkUnitsByName);

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'counts' => $counts,
            'meta' => [
                'activeIndex' => $activeIndex,
                'prevIndex' => $prevIndex,
            ],
            'ops' => [
                'proteinMixUnits' => $proteinMixUnits,
                'topItems' => $topMenuItems,
                'popularDrink' => $popularDrink,
                'organizer' => $organizerName,
            ],
        ];
    }

    /**
     * Determine the active bucket index used for "last point" delta comparisons.
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
     * Build label buckets for the requested period.
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
     * Flatten cookout_addons JSON from TicketSale rows into a single collection.
     *
     * @param mixed $tickets
     * @param mixed|null $category
     * @return \Illuminate\Support\Collection
     */
    private function getAddonsData($tickets, $category = null)
    {
        $addonsData = $tickets
            ->pluck('cookout_addons')
            ->map(fn($addons) => is_string($addons) ? json_decode($addons, true) : ($addons ?? []))
            ->flatten(1);

        if ($category) {
            $addonsData = $addonsData->filter(fn($addon) => ($addon['category'] ?? null) === $category);
        }

        return $addonsData;
    }
}
