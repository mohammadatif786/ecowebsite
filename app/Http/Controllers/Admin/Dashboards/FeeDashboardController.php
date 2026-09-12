<?php

namespace App\Http\Controllers\Admin\Dashboards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\TicketSale;

class FeeDashboardController extends Controller
{
    public function index()
    {
        $countries = LinkUpEvent::query()
            ->whereNotNull('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');


        $organizers = OrganizerProfile::query()
            ->select(['id', 'organizer_name'])
            ->orderBy('organizer_name')
            ->get();

        $initial = $this->aggregate(new Request([
            'country' => 'All',
            'organizer_id' => 'All',
            'view' => 'Monthly',
        ]));

        return Inertia::render('admin/Dashboards/Fee', [
            'filters' => [
                'countries' => $countries,
                'organizers' => $organizers,
            ],
            'initial' => $initial,
        ]);
    }

    public function data(Request $request)
    {
        $request->validate([
            'country' => ['nullable', 'string'],
            'organizer_id' => ['nullable', 'string'],
            'view' => ['nullable', 'in:Monthly,Quarterly,Yearly'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        return response()->json($this->aggregate($request));
    }

    protected function aggregate(Request $request): array
    {
        $country = $request->input('country', 'All');
        $organizerId = $request->input('organizer_id', 'All');
        $view = $request->input('view', 'Monthly');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Base query
        $base = TicketSale::query()
            ->join('link_up_events as e', 'ticket_sales.link_up_event_id', '=', 'e.id')
            ->select('ticket_sales.*', 'e.country', 'e.organizer_id');

        if ($country !== 'All') {
            $base->where('e.country', $country);
        }
        if ($organizerId !== 'All') {
            $base->where('e.organizer_id', $organizerId);
        }
        if ($dateFrom) {
            $base->whereDate('ticket_sales.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $base->whereDate('ticket_sales.created_at', '<=', $dateTo);
        }

        // Clone for reuse
        $clone = fn() => clone $base;

        // === 1. Stats (cards + bottom table) ===
        $stats = $this->calculateStats($clone());

        // === 2. Revenue Over Time ===
        $revenueOverTime = $this->revenueOverTime($clone(), $view);

        // === 3. Country Breakdown (for doughnut & top 5) ===
        $countryBreakdown = $this->countryBreakdown($clone());

        // === 4. Origin Breakdown (by fee type per country) ===
        $originBreakdown = $this->originBreakdown($clone());

        // === 5. Organizer Totals ===
        $organizers = $this->organizerTotals($clone());

        // === 6. Tax Summary ===
        $taxSummary = $this->taxSummary($countryBreakdown);

        return [
            'stats' => $stats,
            'revenueOverTime' => $revenueOverTime,
            'countryBreakdown' => $countryBreakdown,
            'originBreakdown' => $originBreakdown,
            'organizers' => $organizers,
            'taxSummary' => $taxSummary,
        ];
    }

    protected function calculateStats($query): array
    {
        $row = $query->select([
            // Non-VIP ticket component
            DB::raw("SUM(COALESCE(ticket_sales.total, 0)) as ticket_fee"),

            // Fees from JSON breakdown
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.drink_fee_amount')) AS DECIMAL(18,2)), 0)) as drink_fee"),
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.bottle_fee_amount')) AS DECIMAL(18,2)), 0)) as bottle_fee"),

            // VIP ticket component only
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.vip_package_fee_amount')) AS DECIMAL(18,2)), 0)) as package_tables_total"),

            // Processing percentage fee from JSON
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.processing_fee_pct_amount')) AS DECIMAL(18,2)), 0)) as processing_fee"),
        ])->first();

        $stats = [
            'ticket'     => (float) ($row->ticket_fee ?? 0),
            'drink'      => (float) ($row->drink_fee ?? 0),
            'bottle'     => (float) ($row->bottle_fee ?? 0),
            'vip'        => (float) ($row->package_tables_total ?? 0),
            'processing' => (float) ($row->processing_fee ?? 0),
        ];

        $stats['total'] = array_sum($stats);

        return $stats;
    }


    protected function revenueOverTime($query, string $view): array
    {
        $totalExpr = 'COALESCE(ticket_sales.total,0)';

        if ($view === 'Monthly') {
            $rows = $query->select([
                DB::raw('YEAR(ticket_sales.created_at) as y'),
                DB::raw('MONTH(ticket_sales.created_at) as m'),
                DB::raw("SUM($totalExpr) as total"),
            ])->groupBy('y', 'm')->orderBy('y')->orderBy('m')->get();

            return [
                'labels' => $rows->map(fn($r) => sprintf('%04d-%02d', $r->y, $r->m))->toArray(),
                'totalsPerPeriod' => $rows->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            ];
        }

        if ($view === 'Quarterly') {
            $rows = $query->select([
                DB::raw('YEAR(ticket_sales.created_at) as y'),
                DB::raw('QUARTER(ticket_sales.created_at) as q'),
                DB::raw("SUM($totalExpr) as total"),
            ])->groupBy('y', 'q')->orderBy('y')->orderBy('q')->get();

            return [
                'labels' => $rows->map(fn($r) => $r->y . '-Q' . $r->q)->toArray(),
                'totalsPerPeriod' => $rows->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            ];
        }

        // Yearly
        $rows = $query->select([
            DB::raw('YEAR(ticket_sales.created_at) as y'),
            DB::raw("SUM($totalExpr) as total"),
        ])->groupBy('y')->orderBy('y')->get();

        return [
            'labels' => $rows->pluck('y')->map(fn($v) => (string) $v)->toArray(),
            'totalsPerPeriod' => $rows->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];
    }

    protected function countryBreakdown($query): array
    {
        return $query->select([
            'e.country',
            DB::raw('SUM(COALESCE(ticket_sales.total,0)) as total')
        ])
            ->groupBy('e.country')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'country' => $r->country,
                'total' => (float) $r->total,
            ])
            ->toArray();
    }

    protected function originBreakdown($query): array
    {
        return $query->select([
            'e.country',
            DB::raw("SUM(COALESCE(ticket_sales.total,0)) as ticket"),

            // Fees by origin from JSON breakdown
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.drink_fee_amount')) AS DECIMAL(18,2)), 0)) as drink"),
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.bottle_fee_amount')) AS DECIMAL(18,2)), 0)) as bottle"),

            // VIP ticket component only
            DB::raw("SUM(CASE 
                    WHEN ticket_sales.package_id IS NOT NULL 
                    THEN COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.vip_package_fee_amount')) AS DECIMAL(18,2)), 0) 
                    ELSE 0 
                END) as package_tables_total"),
            DB::raw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(ticket_sales.fee_breakdown, '$.processing_fee_pct_amount')) AS DECIMAL(18,2)), 0)) as processing_fee"),
        ])
            ->groupBy('e.country')
            ->get()
            ->map(fn($r) => [
                'country' => $r->country,
                'ticket' => (float) $r->ticket,
                'drink' => (float) $r->drink,
                'bottle' => (float) $r->bottle,
                'vip' => (float) $r->package_tables_total,
                'processing' => (float) $r->processing_fee,
                'total' => (float) ($r->ticket + $r->drink + $r->bottle + $r->package_tables_total + $r->processing_fee),
            ])
            ->toArray();
    }

    protected function organizerTotals($query): array
    {
        return $query->join('organizer_profiles as o', 'e.organizer_id', '=', 'o.id')
            ->select([
                'o.id as organizer_id',
                'o.organizer_name',
                DB::raw('SUM(COALESCE(ticket_sales.total,0)) as total')
            ])
            ->groupBy('o.id', 'o.organizer_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'organizerId' => $r->organizer_id,
                'organizer' => $r->organizer_name,
                'total' => (float) $r->total,
            ])
            ->toArray();
    }

    protected function taxSummary(array $countryBreakdown): array
    {
        $rates = [
            'United States' => 0.07,
            'Antigua and Barbuda' => 0.15,
            'Bahamas' => 0.12,
            'Barbados' => 0.175,
            'Belize' => 0.125,
            'Cuba' => 0.20,
            'Dominica' => 0.15,
            'Dominican Republic' => 0.18,
            'Grenada' => 0.15,
            'Guyana' => 0.14,
            'Haiti' => 0.10,
            'Jamaica' => 0.15,
            'Saint Kitts and Nevis' => 0.17,
            'Saint Lucia' => 0.125,
            'Saint Vincent and the Grenadines' => 0.16,
            'Suriname' => 0.10,
            'Trinidad and Tobago' => 0.125,
            'Puerto Rico' => 0.115,
            'U.S. Virgin Islands' => 0.05,
            'Cayman Islands' => 0.00,
            'British Virgin Islands' => 0.00,
            'Aruba' => 0.03,
            'Curacao' => 0.06,
            'Mexico' => 0.16,
            'Argentina' => 0.21,
            'Bolivia' => 0.13,
            'Brazil' => 0.19,
            'Chile' => 0.19,
            'Colombia' => 0.19,
            'Costa Rica' => 0.13,
            'Ecuador' => 0.12,
            'El Salvador' => 0.13,
            'Guatemala' => 0.12,
            'Honduras' => 0.15,
            'Nicaragua' => 0.15,
            'Panama' => 0.07,
            'Paraguay' => 0.10,
            'Peru' => 0.18,
            'Uruguay' => 0.22,
            'Venezuela' => 0.16,
        ];

        $items = [];
        $totalTax = 0;

        foreach ($countryBreakdown as $row) {
            $rate = $rates[$row['country']] ?? 0.15;
            $tax = $row['total'] * $rate;
            $totalTax += $tax;

            $items[] = [
                'country' => $row['country'],
                'revenue' => $row['total'],
                'rate' => ($rate * 100) . '%',
                'tax' => $tax,
            ];
        }

        return [
            'items' => $items,
            'totalTax' => $totalTax,
        ];
    }
}
