<?php

namespace App\Services\Admin;

use App\DTOs\Admin\AdminOverviewDataDTO;
use App\DTOs\Admin\BusinessUnitDTO;
use App\DTOs\Admin\CancelTicketsDataDTO;
use App\DTOs\Admin\CountryMetricDTO;
use App\DTOs\Admin\EventCategoriesDataDTO;
use App\DTOs\Admin\EventCouponsDataDTO;
use App\DTOs\Admin\OrganizerDirectoryDataDTO;
use App\DTOs\Admin\ScannersManagementDataDTO;
use App\DTOs\Admin\EventSponsorsDataDTO;
use App\DTOs\Admin\EventsDashboardDataDTO;
use App\DTOs\Admin\PayoutOpsDashboardDataDTO;
use App\DTOs\Admin\TicketSalesDataDTO;
use App\DTOs\Admin\UsersDashboardDataDTO;
use App\DTOs\Admin\WalletMissionControlDataDTO;
use App\Repositories\Admin\AdminOverviewRepository;
use Carbon\Carbon;

class AdminOverviewService
{
    /** Matches AsueService::PLATFORM_FEE_PERCENTAGE. */
    private const ASUE_FEE_PCT = 0.03;

    public function __construct(
        private AdminOverviewRepository $repository
    ) {}

    public function getOverviewData(): AdminOverviewDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();

        return new AdminOverviewDataDTO($units, $countries);
    }

    public function getUsersDashboardData(): UsersDashboardDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $users = $this->repository->getRecentUsers(100);
        $plans = $this->repository->getSubscriptionPlans();
        $growth = $this->repository->getUserGrowthStats(6);

        $userProfiles = $users->map(function ($user) {
            $age = $user->birthday ? Carbon::parse($user->birthday)->age : 0;
            $plan = $user->subscribedPlans->first()?->plan?->name ?? 'Free';
            return [
                'id' => $user->id,
                'name' => $user->name,
                'country' => $user->country ?? 'Unknown',
                'age' => $age,
                'gender' => $user->gender ?? 'Other',
                'status' => $user->status ? 'Active' : 'Inactive',
                'kyc' => $user->kyc_status === 'approved' ? 'Verified' : (empty($user->kyc_status) ? 'Pending' : $user->kyc_status),
                'plan' => $plan,
                'rpu' => 0, // Placeholder, can be joined from transactions if needed
                'joined' => $user->created_at->toIso8601String(),
                'avatar' => $user->avatar,
            ];
        })->toArray();

        $subscriptionPlans = $plans->map(function ($plan) {
            return [
                'name' => $plan->name,
                'emoji' => $plan->emoji ?? '⭐',
                'price' => (float)$plan->price,
                'subs' => $plan->subscriptions()->count(),
            ];
        })->toArray();

        $growthStats = $growth->map(function ($stat) {
            return [
                'month' => $stat->month,
                'count' => $stat->count,
                'active' => $stat->count > 0 ? round(($stat->active_count / $stat->count) * 100) : 0,
            ];
        })->toArray();

        return new UsersDashboardDataDTO($units, $countries, $userProfiles, $subscriptionPlans, $growthStats);
    }

    public function getEventsDashboardData(): EventsDashboardDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $eventsRaw = $this->repository->getRecentEvents(100);

        $events = $eventsRaw->map(function ($event) {
            $details = $event->eventDetails;
            $date = '—';
            $time = '—';

            if ($details) {
                if ($details->event_type === 'single') {
                    $date = $details->single_event_date?->format('Y-m-d') ?? '—';
                    $startTime = $details->single_start_time?->format('h:i A') ?? '—';
                    $endTime = $details->single_end_time?->format('h:i A') ?? '—';
                    $time = "$startTime - $endTime";
                } else if ($details->event_type === 'recurring') {
                    $start = $details->recurr_start_date?->format('Y-m-d') ?? '—';
                    $end = $details->recurr_end_date?->format('Y-m-d') ?? '—';
                    $date = "$start - $end";
                    $time = 'Recurring';
                }
            }

            return [
                'id' => $event->id,
                'event' => $event->title,
                'organizer' => $event->organizer_name ?? $event->organizer?->name ?? 'Unknown',
                'email' => $event->email ?? '—',
                'country' => $event->country ?? 'Unknown',
                'city' => $event->city ?? '—',
                'category' => $event->category?->name ?? 'Other',
                'type' => ucfirst($event->eventDetails?->event_type ?? 'single'),
                'date' => $date,
                'time' => $time,
                'status' => ucfirst($event->status ?? 'Live'),
                'image' => $event->eventDetails?->media,
                'ticketsSold' => (int)$event->ticketsSold,
                'ticketsTotal' => (int)$event->ticketsTotal,
                'ticketRevenue' => (float)$event->ticketRevenue,
                'drinkRevenue' => 0,
                'waterRevenue' => 0,
                'vipRevenue' => 0,
                'sponsorRevenue' => 0,
                'refunds' => 0,
                'feeRate' => 0.065,
                'featuredHome' => false,
            ];
        })->toArray();

        $stats = [
            'total' => $eventsRaw->count(),
            'live' => $eventsRaw->where('status', 'live')->count(),
            'single' => $eventsRaw->filter(fn($e) => $e->eventDetails?->event_type === 'single')->count(),
            'recurring' => $eventsRaw->filter(fn($e) => $e->eventDetails?->event_type === 'recurring')->count(),
        ];

        return new EventsDashboardDataDTO($units, $countries, $events, $stats);
    }

    public function getEventCategoriesData(): EventCategoriesDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $categoriesRaw = $this->repository->getEventCategories();

        $categories = $categoriesRaw->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'icon' => $cat->image_object ?? '🏷️',
                'events' => $cat->linkup_events_count,
                'featured' => (bool)$cat->is_featured,
                'status' => $cat->status ? 'Active' : 'Inactive',
                'image' => $cat->image_object ?? '🏷️',
            ];
        })->toArray();

        $stats = [
            'total' => $categoriesRaw->count(),
            'featured' => $categoriesRaw->where('is_featured', true)->count(),
            'active' => $categoriesRaw->where('status', true)->count(),
            'inactive' => $categoriesRaw->where('status', false)->count(),
        ];

        return new EventCategoriesDataDTO($units, $countries, $categories, $stats);
    }

    public function getTicketSalesData(): TicketSalesDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $recordsRaw = $this->repository->getTicketSalesRecords(100);

        $records = $recordsRaw->map(function ($sale) {
            $cat = $sale->event?->category?->name ?? 'Tickets / Passes';

            // Logic to determine a more specific category for the commerce ledger if applicable
            if ($sale->drinks_total > 0) $cat = 'Bars / Drinks';
            if ($sale->cookout_total > 0) $cat = 'Food / Cookout';
            if ($sale->wellness_total > 0) $cat = 'Spa / Beauty';
            if ($sale->tables_total > 0) $cat = 'Tables / VIP';

            $startTime = '—';
            $endTime = '—';
            $details = $sale->event?->eventDetails;
            if ($details) {
                if ($details->event_type === 'single') {
                    $startTime = $details->single_start_time?->format('h:i A') ?? '—';
                    $endTime = $details->single_end_time?->format('h:i A') ?? '—';
                } else if ($details->event_type === 'recurring') {
                    $startTime = 'Recurring';
                    $endTime = 'Recurring';
                }
            }

            return [
                'id' => 'TCK-' . $sale->id,
                'date' => $sale->created_at ? (is_string($sale->created_at) ? Carbon::parse($sale->created_at)->format('Y-m-d') : $sale->created_at->format('Y-m-d')) : '—',
                'buyer' => $sale->user?->name ?? 'Unknown',
                'email' => $sale->user?->email ?? '—',
                'event' => $sale->event?->title ?? '—',
                'country' => $sale->user?->country ?? 'Unknown',
                'category' => $cat,
                'itemType' => ucfirst($sale->ticket_type ?? 'General'),
                'item' => $sale->ticket_name ?? 'Admission',
                'qty' => (int)($sale->no_of_tickets ?? 1),
                'unitPrice' => (float)$sale->total / max(1, (int)$sale->no_of_tickets),
                'subtotal' => (float)$sale->sub_total,
                'fee' => (float)$sale->fee,
                'tax' => (float)$sale->tax,
                'discount' => (float)($sale->discount ?? 0),
                'refund' => (float)($sale->refund_amount ?? 0),
                'total' => (float)$sale->total,
                'status' => ucfirst($sale->ticket_status ?? 'Paid'),
                'payment' => ucfirst($sale->payment_method ?? 'Wallet'),
                'startTime' => $startTime,
                'endTime' => $endTime,
            ];
        })->toArray();

        $stats = [
            'totalItems' => $recordsRaw->sum('no_of_tickets'),
            'grossRevenue' => $recordsRaw->sum('total'),
            'vipCount' => $recordsRaw->where('ticket_type', 'VIP')->sum('no_of_tickets'),
            'economyCount' => $recordsRaw->where('ticket_type', 'economy')->sum('no_of_tickets'),
            'generalCount' => $recordsRaw->whereNotIn('ticket_type', ['VIP', 'economy'])->sum('no_of_tickets'),

            // Commerce Category Revenue (Real Data from DB fields)
            'foodRevenue' => (float)$recordsRaw->sum('cookout_total'),
            'spaRevenue' => (float)$recordsRaw->sum('wellness_total'),
            'barRevenue' => (float)$recordsRaw->sum('drinks_total'),
            'tableRevenue' => (float)$recordsRaw->sum('tables_total'),

            // Counts (approximated based on presence of revenue in those fields)
            'foodCount' => $recordsRaw->where('cookout_total', '>', 0)->count(),
            'spaCount' => $recordsRaw->where('wellness_total', '>', 0)->count(),
            'barCount' => $recordsRaw->where('drinks_total', '>', 0)->count(),
            'addonCount' => $recordsRaw->where('tables_total', '>', 0)->count(),
        ];

        return new TicketSalesDataDTO($units, $countries, $records, $stats);
    }

    public function getEventSponsorsData(): EventSponsorsDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $sponsorsRaw = $this->repository->getSponsors();
        $eventsRaw = \App\Models\LinkUpEvent::select('id as value', 'title as label')->get();

        $sponsors = $sponsorsRaw->map(function ($sponsor) {
            return [
                'id' => $sponsor->id,
                'sponsor' => $sponsor->name,
                'name' => $sponsor->name,
                'description' => $sponsor->description,
                'event_name' => $sponsor->event?->title ?? '—',
                'event' => $sponsor->event?->title ?? '—',
                'country' => 'Bahamas', // Placeholder as per repo structure
                'package' => 'Sponsor Ad', // Placeholder
                'status' => $sponsor->status ? 'Active' : 'Inactive',
                'image_object' => $sponsor->image_object,
                'sponsor_image_object' => $sponsor->sponsor_image_object,
                'link_up_event_id' => $sponsor->link_up_event_id,
            ];
        })->toArray();

        $stats = [
            'total' => $sponsorsRaw->count(),
            'active' => $sponsorsRaw->where('status', true)->count(),
            'inactive' => $sponsorsRaw->where('status', false)->count(),
            'linkedEvents' => $sponsorsRaw->whereNotNull('link_up_event_id')->unique('link_up_event_id')->count(),
            'mediaCount' => $sponsorsRaw->whereNotNull('image_object')->count(),
        ];

        return new EventSponsorsDataDTO($units, $countries, $sponsors, $eventsRaw->toArray(), $stats, config('app.url'));
    }

    public function getEventCouponsData(): EventCouponsDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $couponsRaw = $this->repository->getCoupons();
        $eventsRaw = \App\Models\LinkUpEvent::select('id as value', 'title as label')->get();

        $coupons = $couponsRaw->map(function ($coupon) {
            $statusLabel = 'Active';
            if ($coupon->expiry_date && \Carbon\Carbon::parse($coupon->expiry_date)->isPast()) {
                $statusLabel = 'Expired';
            } elseif ($coupon->status === 0 || $coupon->status === false || $coupon->status === 'Inactive') {
                $statusLabel = 'Inactive';
            }

            return [
                'id' => $coupon->id,
                'name' => $coupon->title,
                'title' => $coupon->title,
                'code' => $coupon->code,
                'event' => $coupon->event?->title ?? '—',
                'discount_type' => $coupon->discount_type ?? 'percentage',
                'discount' => (float)$coupon->discount,
                'expiry_date' => $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : null,
                'expiry' => $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : 'No expiry',
                'uses' => (int)($coupon->uses ?? 0),
                'limit' => (int)($coupon->usage_limit ?? 0),
                'usage_limit' => (int)($coupon->usage_limit ?? 0),
                'status' => $statusLabel,
                'raw_status' => (int)$coupon->status,
                'description' => $coupon->description ?? '—',
                'link_up_event_id' => $coupon->link_up_event_id,
            ];
        })->toArray();

        $stats = [
            'total' => $couponsRaw->count(),
            'active' => collect($coupons)->where('status', 'Active')->count(),
            'inactive' => collect($coupons)->whereIn('status', ['Inactive', 'Expired'])->count(),
            'linkedEvents' => $couponsRaw->whereNotNull('link_up_event_id')->unique('link_up_event_id')->count(),
            'percentCount' => collect($coupons)->where('discount_type', 'percentage')->count(),
            'fixedCount' => collect($coupons)->where('discount_type', 'amount')->count(),
        ];

        return new EventCouponsDataDTO($units, $countries, $coupons, $eventsRaw->toArray(), $stats);
    }

    public function getCancelTicketsData(): CancelTicketsDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $requestsRaw = $this->repository->getPendingCancellationRequests();
        $ordersRaw = $this->repository->getCancellationOrders();

        $requests = $requestsRaw->map(function ($req) {
            return [
                'id' => 'CAN-' . $req->id,
                'ticket' => 'TCK-' . $req->id,
                'event' => $req->event?->title ?? '—',
                'buyer' => $req->user?->name ?? 'Unknown',
                'amount' => (float)$req->total,
                'reason' => 'Customer requested refund', // Default or derived
                'status' => 'Pending',
                'date' => $req->created_at ? (is_string($req->created_at) ? Carbon::parse($req->created_at)->format('Y-m-d') : $req->created_at->format('Y-m-d')) : '—',
                'raw_request' => $req->toArray()
            ];
        })->toArray();

        $orders = $ordersRaw->map(function ($order) {
            return [
                'id' => 'ORD-' . $order->id,
                'ticket' => 'TCK-' . $order->ticket_id,
                'event' => $order->event?->title ?? '—',
                'buyer' => $order->user?->name ?? 'Unknown',
                'amount' => (float)$order->refund_amount,
                'deduct' => (float)$order->deduct_ammount,
                'status' => $order->status,
                'date' => $order->created_at ? (is_string($order->created_at) ? Carbon::parse($order->created_at)->format('Y-m-d') : $order->created_at->format('Y-m-d')) : '—'
            ];
        })->toArray();

        $user_transactions = $ordersRaw->where('status', 'Approve + Refund')->groupBy('user_id');
        $users = $user_transactions->map(function ($data) {
            return [
                'name' => $data[0]?->user?->name,
                'email' => $data[0]?->user?->email,
                'amount' => $data->sum('refund_amount'),
                'ticket' => 'TCK-' . $data[0]?->ticket_id
            ];
        })->values()->toArray();

        return new CancelTicketsDataDTO($units, $countries, $requests, $orders, $users);
    }

    public function getOrganizerDirectoryData(): OrganizerDirectoryDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $organizersRaw = $this->repository->getOrganizers();

        $organizers = $organizersRaw->map(function ($o) {
            $kycStatus = 'Not Submitted';
            if ($o->kyc) {
                $kycStatus = $o->kyc->status === 'approved'
                    ? 'Verified'
                    : (empty($o->kyc->status) ? 'Pending Review' : ucfirst($o->kyc->status));
            }

            return [
                'id' => $o->id,
                'name' => $o->organizer_name,
                'email' => $o->contacts?->email ?? $o->user?->email ?? '—',
                'phone' => $o->contacts?->phone ?? $o->telephone ?? '—',
                'telephone' => $o->telephone ?? '',
                'country' => $o->contacts?->country ?? $o->nationality ?? 'Unknown',
                'city' => $o->contacts?->city ?? '—',
                'state' => $o->contacts?->state ?? '',
                'category' => $o->categories[0] ?? 'General',
                'kyc' => $kycStatus,
                'events' => $o->user?->linkup_events_count ?? 0, // Assuming count is available
                'revenue' => (float)($o->user?->ticket_sales_sum_total ?? 0),
                'tickets' => (int)($o->user?->ticket_sales_count ?? 0),
                'status' => $o->user?->status == 1 ? 'Active' : 'Inactive',
                'dob' => $o->date_of_birth ? $o->date_of_birth->format('Y-m-d') : '—',
                'placeBirth' => $o->place_of_birth ?? '—',
                'address' => $o->address ?? '—',
                'ssn' => $o->ssn ?? '—',
                'nationality' => $o->nationality ?? '—',
                'about' => $o->about_the_organizer ?? '—',
                'categories' => $o->categories ?? [],
                'website' => $o->contacts?->website ?? '—',
                'social' => [
                    'facebook' => $o->contacts?->facebook ?: null,
                    'twitter' => $o->contacts?->twitter ?: null,
                    'instagram' => $o->contacts?->instagram ?: null,
                    'linkedin' => $o->contacts?->linkedin ?: null,
                    'youtube' => $o->contacts?->youtube ?: null,
                ],
                'settings' => [
                    'show_venues_map' => (bool)($o->settings?->show_venues_map ?? false),
                    'show_followers' => (bool)($o->settings?->show_followers ?? false),
                    'show_reviews' => (bool)($o->settings?->show_reviews ?? false),
                ],
                'kycDocuments' => [
                    'passport_front' => $o->kyc?->passport_front ?: null,
                    'passport_back' => $o->kyc?->passport_back ?: null,
                    'proof_of_address' => $o->kyc?->proof_of_address ?: null,
                ],
                'bankAccounts' => $o->bankAccounts->map(fn($b) => [
                    'bank_name' => $b->bank_name,
                    'account_number' => $b->account_number,
                    'routing_number' => $b->routing_number,
                ])->toArray(),
                'bankName' => $o->bankAccounts->first()?->bank_name ?? '—',
                'acct' => $o->bankAccounts->first()?->account_number ?? '—',
                'routing' => $o->bankAccounts->first()?->routing_number ?? '—',
                'media' => $o->media?->toArray() ?? [],
                'user_id' => $o->user_id,
            ];
        })->toArray();

        $stats = [
            'total' => $organizersRaw->count(),
            'active' => $organizersRaw->filter(fn($o) => $o->user?->status == 1)->count(),
            'countries' => $organizersRaw->map(fn($o) => $o->nationality)->filter()->unique()->count(),
            'pendingKyc' => $organizersRaw->filter(fn($o) => $o->kyc?->status !== 'approved')->count(),
            'events' => $organizersRaw->sum(fn($o) => $o->user?->linkup_events_count ?? 0),
        ];

        return new OrganizerDirectoryDataDTO($units, $countries, $organizers, $stats, config('app.url'));
    }

    public function getScannersManagementData(): ScannersManagementDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $scannersRaw = $this->repository->getScanners();
        $organizersRaw = \App\Models\User::where('type', 'organizer')->get(['id', 'name']);

        $scanners = $scannersRaw->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->first_name . ' ' . $s->last_name,
                'first_name' => $s->first_name,
                'last_name' => $s->last_name,
                'email' => $s->email,
                'phone' => $s->telephone ?? '—',
                'address' => $s->address ?? '—',
                'organizer' => $s->user?->name ?? 'Unassigned',
                'org_id' => $s->user_id,
                'role' => 'Gate Scanner', // Placeholder as not in model
                'scansToday' => 0, // Need transaction tracking for real data
                'totalScans' => 0,
                'fraudAlerts' => 0,
                'status' => $s->status ?? 'Active',
                'image' => $s->scanner_image_object,
                'country' => $s->user?->country ?? 'Bahamas',
            ];
        })->toArray();

        $stats = [
            'total' => $scannersRaw->count(),
            'active' => $scannersRaw->where('status', 'Active')->count(),
            'offline' => $scannersRaw->where('status', 'Offline')->count(),
            'events' => 0, // Requires joining with event details
            'todayScans' => 0,
            'fraudAlerts' => 0,
        ];

        return new ScannersManagementDataDTO($units, $countries, $scanners, $organizersRaw->toArray(), $stats, config('app.url'));
    }

    public function getWalletMissionControlData(): WalletMissionControlDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();
        $movementsRaw = $this->repository->getWalletMovements(300);
        $withdrawalsRaw = $this->repository->getBankWithdrawals(300);

        $walletMovements = $movementsRaw->map(fn($tx) => $this->mapWalletMovement($tx))
            ->concat($withdrawalsRaw->map(fn($w) => $this->mapBankWithdrawal($w)))
            ->sortByDesc('date')
            ->values()
            ->toArray();

        [$walletUsers, $walletUserStats] = $this->getWalletUsersData();
        [$walletCountryBalances, $currencyExposure, $walletBalanceSummary] = $this->getWalletBalancesData();
        [$settlements, $settlementSummary] = $this->getSettlementCenterData();
        [$asueCircles, $asueSummary] = $this->getAsueDrawerData();
        [$payoutQueue, $payoutQueueSummary] = $this->getPayoutQueueData();

        return new WalletMissionControlDataDTO(
            $units,
            $countries,
            $walletMovements,
            $walletUsers,
            $walletUserStats,
            $walletCountryBalances,
            $currencyExposure,
            $walletBalanceSummary,
            $settlements,
            $settlementSummary,
            $asueCircles,
            $asueSummary,
            $payoutQueue,
            $payoutQueueSummary
        );
    }

    /**
     * ASUE (rotating savings "hand") circles — real Asue records. Fee/health/risk are
     * derived: the platform fee % reuses AsueService::PLATFORM_FEE_PERCENTAGE (3%), and
     * "next draw" is a projected estimate from start_date + frequency * turns elapsed,
     * since the real cycle advances by participant action (advanceCycle/acceptPayout),
     * not by a stored due-date. Fees collected come from real transaction meta, not
     * the fee-rate estimate, so they reflect what was actually paid out historically.
     */
    private function getAsueDrawerData(): array
    {
        $circlesRaw = $this->repository->getAsueCircles(200);
        $feesByCircle = $this->repository->getAsuePayoutFeesByCircle();

        $rows = $circlesRaw->map(function ($asue) use ($feesByCircle) {
            $memberCount = max(1, (int)$asue->invited_users_count);
            $pot = (float)$asue->hand_amount * $memberCount;
            $nextDraw = $this->estimateAsueNextDraw($asue);
            [$health, $risk] = $this->classifyAsueHealth($asue, $nextDraw);

            return [
                'id' => $asue->asue_unique_code ?: ('AS-' . $asue->id),
                'pot' => $pot,
                'memberCount' => $memberCount,
                'cycle' => $asue->current_turn . ' / ' . $memberCount,
                'nextDraw' => $nextDraw?->format('M j') ?? '—',
                'nextDrawDate' => $nextDraw,
                'fee' => $pot * self::ASUE_FEE_PCT,
                'collected' => (float)($feesByCircle[$asue->id] ?? 0),
                'health' => $health,
                'risk' => $risk,
                'payoutAccepted' => (bool)$asue->payout_accepted,
                'status' => $asue->status,
            ];
        });

        $totalCircles = $rows->count();
        $healthyCount = $rows->filter(fn($r) => $r['risk'] === 'Low')->count();
        $atRiskCount = $totalCircles - $healthyCount;

        $sevenDaysOut = now()->addDays(7);
        $pendingPayoutTotal = $rows
            ->filter(fn($r) => strtolower($r['status']) === 'active' && ! $r['payoutAccepted'] && $r['nextDrawDate'] && $r['nextDrawDate']->lte($sevenDaysOut))
            ->sum('pot');

        $asueSummary = [
            'activeCircles' => $rows->filter(fn($r) => strtolower($r['status']) === 'active')->count(),
            'pendingPayoutTotal' => (float)$pendingPayoutTotal,
            'feesCollected' => array_sum($feesByCircle),
            'healthyPct' => $totalCircles ? round($healthyCount / $totalCircles * 100) : 100,
            'atRiskCount' => $atRiskCount,
        ];

        // Drop the internal-only sort keys before handing rows to the frontend.
        $asueCircles = $rows->map(fn($r) => collect($r)->except(['nextDrawDate', 'payoutAccepted', 'status'])->all())->toArray();

        return [$asueCircles, $asueSummary];
    }

    private function estimateAsueNextDraw($asue): ?\Carbon\Carbon
    {
        if (! $asue->start_date) {
            return null;
        }

        $turnsElapsed = max(0, ((int)$asue->current_turn ?: 1) - 1);
        $frequency = strtolower((string)$asue->frequency);

        $date = \Carbon\Carbon::parse($asue->start_date);

        return $frequency === 'monthly' ? $date->addMonths($turnsElapsed) : $date->addWeeks($turnsElapsed);
    }

    private function classifyAsueHealth($asue, ?\Carbon\Carbon $nextDraw): array
    {
        $status = strtolower((string)$asue->status);

        if ($status === 'completed') {
            return ['Completed', 'Low'];
        }
        if ($status !== 'active') {
            return ['Pending', 'Low'];
        }
        if (! $nextDraw || ! $nextDraw->isPast() || $asue->payout_accepted) {
            return ['Healthy', 'Low'];
        }

        $frequency = strtolower((string)$asue->frequency);
        $intervalDays = $frequency === 'monthly' ? 30 : 7;
        $daysOverdue = $nextDraw->diffInDays(now());

        return $daysOverdue > $intervalDays ? ['Missed Draw', 'High'] : ['Late Payment', 'Medium'];
    }

    /**
     * Wallet Payout Queue = every real cash-out request across the three sources this
     * app actually has: user wallet withdrawals (BankWithdrawal), organizer/event
     * payouts (Payout, same records as Settlement Center), and ASU "hand" payouts
     * (Transaction rows with meta.type = asue_payout). There's no real merchant-wallet
     * payout model in this codebase yet, so that source isn't represented.
     */
    private function getPayoutQueueData(): array
    {
        $withdrawals = $this->repository->getBankWithdrawals(100);
        $payouts = $this->repository->getSettlements(100);
        $asuePayouts = $this->repository->getAsuePayoutTransactions(100);

        $rows = collect()
            ->concat($withdrawals->map(fn($w) => $this->mapPayoutQueueFromWithdrawal($w)))
            ->concat($payouts->map(fn($p) => $this->mapPayoutQueueFromPayout($p)))
            ->concat($asuePayouts->map(fn($tx) => $this->mapPayoutQueueFromAsue($tx)))
            ->sortByDesc('sortDate')
            ->values();

        $today = now()->toDateString();
        $payoutQueueSummary = [
            'pending' => $rows->where('status', 'Pending')->count(),
            'processing' => $rows->where('status', 'Processing')->count(),
            'completedToday' => $rows->filter(fn($r) => $r['status'] === 'Completed' && str_starts_with($r['sortDate'], $today))->count(),
            'failed' => $rows->where('status', 'Failed')->count(),
        ];

        $payoutQueue = $rows->take(150)->map(fn($r) => collect($r)->except('sortDate')->all())->values()->toArray();

        return [$payoutQueue, $payoutQueueSummary];
    }

    private function payoutActionLabel(string $status): string
    {
        return match ($status) {
            'Pending' => 'Review',
            'Processing' => 'Track',
            default => 'View',
        };
    }

    private function mapPayoutQueueFromWithdrawal($w): array
    {
        $statusMap = ['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Failed'];
        $status = $statusMap[$w->status] ?? 'Pending';

        return [
            'id' => 'WPO-UW-' . $w->id,
            'party' => $w->user?->name ?? 'Unknown',
            'source' => 'User Wallet',
            'country' => $w->user?->country ?? 'Unknown',
            'bankWallet' => $this->safeBankLabel($w),
            'gross' => (float)$w->amount,
            'fee' => (float)($w->fee_amount ?? 0),
            'net' => (float)($w->payout_amount ?? ((float)$w->amount - (float)($w->fee_amount ?? 0))),
            'status' => $status,
            'action' => $this->payoutActionLabel($status),
            'sortDate' => $w->created_at?->toDateTimeString() ?? '',
        ];
    }

    private function mapPayoutQueueFromPayout($p): array
    {
        $statusMap = ['pending' => 'Pending', 'processing' => 'Processing', 'approved' => 'Completed', 'ok' => 'Completed', 'verified' => 'Completed', 'rejected' => 'Failed'];
        $status = $statusMap[$p->status] ?? 'Pending';
        $user = $p->organizer?->user;

        return [
            'id' => $p->reference ?: ('WPO-OW-' . $p->id),
            'party' => $user?->name ?? $p->author,
            'source' => 'Organizer Wallet',
            'country' => $user?->country ?? 'Unknown',
            'bankWallet' => $p->destination ?: 'Not specified',
            'gross' => (float)$p->amount,
            'fee' => (float)$p->fee_amount,
            'net' => (float)$p->net_amount,
            'status' => $status,
            'action' => $this->payoutActionLabel($status),
            'sortDate' => $p->created_at?->toDateTimeString() ?? '',
        ];
    }

    private function mapPayoutQueueFromAsue($tx): array
    {
        $statusMap = [
            'success' => 'Completed', 'pending' => 'Pending', 'on_hold' => 'Pending',
            'in_progress' => 'Processing', 'awaiting_approval' => 'Pending', 'awaiting_payment' => 'Pending',
            'refunded' => 'Pending', 'failed' => 'Failed', 'canceled' => 'Failed', 'expired' => 'Failed',
        ];
        $rawStatus = is_object($tx->status) ? $tx->status->value : $tx->status;
        $status = $statusMap[$rawStatus] ?? 'Completed';
        $meta = $tx->meta ?? [];

        return [
            'id' => 'WPO-AS-' . $tx->id,
            'party' => $tx->to?->name ?? 'Unknown',
            'source' => 'ASU Drawer',
            'country' => $tx->to?->country ?? 'Unknown',
            'bankWallet' => 'LinkUp Wallet',
            'gross' => (float)($meta['total_pot'] ?? $tx->received),
            'fee' => (float)($meta['platform_fee'] ?? 0),
            'net' => (float)$tx->received,
            'status' => $status,
            'action' => $this->payoutActionLabel($status),
            'sortDate' => $tx->created_at?->toDateTimeString() ?? '',
        ];
    }

    /**
     * Settlement Queue = real event-organizer Payout records (the only real payout
     * model in this app — it doesn't cover merchant/bill-gateway/ASU payouts, so
     * every real row surfaces as type "Event"). LinkUp Fee / Bank Share reuse the
     * same 60/40 processing split used throughout the rest of this admin panel.
     */
    private function getSettlementCenterData(): array
    {
        $statusMap = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'approved' => 'Completed',
            'ok' => 'Completed',
            'verified' => 'Completed',
            'rejected' => 'Failed',
        ];

        $settlementsRaw = $this->repository->getSettlements(100);
        $settlements = $settlementsRaw->map(function ($payout) use ($statusMap) {
            $user = $payout->organizer?->user;
            $fee = (float)$payout->fee_amount;

            return [
                'id' => $payout->reference ?? ('PO-' . $payout->id),
                'type' => 'Event',
                'party' => $user?->name ?? $payout->author,
                'country' => $user?->country ?? 'Unknown',
                'gross' => (float)$payout->amount,
                'fees' => $fee * 0.6,
                'bankShare' => $fee * 0.4,
                'payout' => (float)$payout->net_amount,
                'status' => $statusMap[$payout->status] ?? 'Pending',
            ];
        })->toArray();

        $statusCounts = $this->repository->getSettlementStatusCounts();
        $today = $this->repository->getTodaySettlementTotals();
        $feeToday = (float)$today->feeToday;

        $settlementSummary = [
            'grossToday' => (float)$today->grossToday,
            'netRevenueToday' => $feeToday * 0.6,
            'pending' => (int)($statusCounts['pending'] ?? 0),
            'completed' => (int)($statusCounts['approved'] ?? 0) + (int)($statusCounts['ok'] ?? 0) + (int)($statusCounts['verified'] ?? 0),
            'failed' => (int)($statusCounts['rejected'] ?? 0),
            'split' => '60/40',
        ];

        return [$settlements, $settlementSummary];
    }

    /**
     * Real per-country stored value + a real currency-exposure breakdown, both from
     * the wallet package's `balances` table (not the mock country.wallet business-unit
     * field, which is cumulative transaction volume rather than a current balance).
     * Reserve figures reuse the same 110%/116% policy constants already established
     * in Mission Control's Treasury Report (required reserve / available reserves).
     */
    private function getWalletBalancesData(): array
    {
        $currencyMap = [
            'Bahamas' => 'BSD', 'Jamaica' => 'JMD', 'Trinidad & Tobago' => 'TTD', 'Barbados' => 'BBD',
            'Guyana' => 'GYD', 'Dominican Republic' => 'DOP', 'United States' => 'USD', 'Canada' => 'CAD',
            'Brazil' => 'BRL', 'Colombia' => 'COP', 'Haiti' => 'HTG',
        ];

        $countryRows = $this->repository->getWalletBalancesByCountry();

        $walletCountryBalances = $countryRows->map(function ($row) use ($currencyMap) {
            $storedValue = (float)$row->storedValue;

            return [
                'country' => $row->country,
                'walletUsers' => (int)$row->walletUsers,
                'balance' => $storedValue,
                'reserveRequired' => $storedValue * 1.1,
                'currency' => ($currencyMap[$row->country] ?? 'USD') . ' / USD',
                // No separate "actual reserve held" ledger exists in this system —
                // funding status isn't independently tracked, so this is a static label.
                'status' => 'Funded',
            ];
        })->sortByDesc('balance')->values()->toArray();

        $currencyRows = $this->repository->getCurrencyExposure();
        $currencyExposure = $currencyRows->map(fn($row) => [
            'currency' => $row->currency,
            'total' => (float)$row->total,
        ])->toArray();

        $storedValue = $countryRows->sum('storedValue');
        $requiredReserve = $storedValue * 1.1;
        $availableReserves = $storedValue * 1.16;

        $walletBalanceSummary = [
            'storedValue' => (float)$storedValue,
            'requiredReserve' => (float)$requiredReserve,
            'reserveCoveragePct' => $storedValue ? ($availableReserves / $storedValue) * 100 : 0,
            'currencyCount' => $currencyRows->count(),
        ];

        return [$walletCountryBalances, $currencyExposure, $walletBalanceSummary];
    }

    /**
     * Real "wallet users" directory: users with an initialized wallet balance row,
     * enriched with their latest KYC submission and default linked bank account.
     * "Linked Card" is derived from the presence of a Stripe customer id rather than
     * a live Stripe API lookup per row, which would be too costly for a bulk listing.
     */
    private function getWalletUsersData(): array
    {
        $usersRaw = $this->repository->getWalletUsers(200);
        $ids = $usersRaw->pluck('id')->toArray();

        $balances = $this->repository->getWalletBalancesByUserIds($ids)->keyBy('payable_id');
        $kyc = $this->repository->getLatestWalletKycByUserIds($ids)->keyBy('user_id');
        $bankAccounts = $this->repository->getDefaultBankAccountsByUserIds($ids)->keyBy('user_id');

        $isoMap = [
            'Bahamas' => 'BS', 'Jamaica' => 'JM', 'Trinidad & Tobago' => 'TT', 'Barbados' => 'BB',
            'Guyana' => 'GY', 'Dominican Republic' => 'DO', 'United States' => 'US', 'Canada' => 'CA',
            'Brazil' => 'BR', 'Colombia' => 'CO', 'Haiti' => 'HT',
        ];
        $kycLabelMap = ['approved' => 'Verified', 'pending' => 'Pending', 'rejected' => 'Rejected'];

        $walletUsers = $usersRaw->map(function ($user) use ($balances, $kyc, $bankAccounts, $isoMap, $kycLabelMap) {
            $kycStatus = $kyc->get($user->id)?->status;
            $kycLabel = $kycLabelMap[$kycStatus] ?? 'Not Submitted';
            $bank = $bankAccounts->get($user->id);

            $risk = 'Low';
            if ($kycStatus === 'pending' || $kycStatus === null) $risk = 'Medium';
            if ($kycStatus === 'rejected' || ! $user->status) $risk = 'High';

            $status = 'Active';
            if ($kycStatus === 'pending') $status = 'Review';
            if (! $user->status || $kycStatus === 'rejected') $status = 'Hold';

            $countryCode = $isoMap[$user->country] ?? (strtoupper(substr((string)$user->country, 0, 2)) ?: 'XX');

            return [
                'user' => $user->name,
                'country' => $user->country ?? 'Unknown',
                'walletId' => 'WAL-' . $countryCode . '-' . str_pad((string)$user->id, 4, '0', STR_PAD_LEFT),
                // Balance::$value is a custom O21\Numeric\Numeric object (not a plain scalar),
                // so it must go through a string cast (which it defines __toString for) before
                // a float cast — casting the object to float directly throws.
                'balance' => (float)(string)($balances->get($user->id)?->value ?? 0),
                'kyc' => $kycLabel,
                'linkedBank' => $this->safeBankLabel($bank),
                'linkedCard' => $user->stripe_id ? 'Card on File' : 'Not Linked',
                'risk' => $risk,
                'status' => $status,
            ];
        })->toArray();

        $kycCounts = $this->repository->getKycStatusCountsByUser();
        $walletUserStats = [
            'totalWalletUsers' => $this->repository->countWalletUsers(),
            'verifiedKyc' => (int)($kycCounts['approved'] ?? 0),
            'pendingKyc' => (int)($kycCounts['pending'] ?? 0),
            'suspended' => (int)\App\Models\User::where('status', false)->count(),
        ];

        return [$walletUsers, $walletUserStats];
    }

    /**
     * bank_name/account_number are `encrypted` casts on UserBankAccount. Legacy or
     * cross-environment rows (encrypted under a different/rotated APP_KEY, or
     * otherwise corrupted ciphertext) throw a DecryptException on access — that
     * shouldn't 500 the whole wallet users directory over one bad row.
     */
    private function safeBankLabel($bank): string
    {
        if (! $bank) {
            return 'Not Linked';
        }

        try {
            return $bank->bank_name . ' •••' . substr((string)$bank->account_number, -4);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return 'Linked (unreadable)';
        }
    }

    /**
     * Maps a raw ledger Transaction (o21/laravel-wallet) onto the Mission Control
     * ledger shape. `from` is treated as the wallet-owning subject of the row (same
     * convention as AdminOverviewRepository::getWalletStats()); type/channel/direction
     * are best-effort labels derived from processor_id since the package doesn't
     * store a human-readable transaction type itself.
     */
    private function mapWalletMovement($tx): array
    {
        $subject = $tx->from ?? $tx->to;
        $processor = $tx->processor_id ?? 'transfer';

        $typeMap = [
            'deposit' => 'Wallet Load',
            'charge' => 'Merchant Pay',
            'transfer' => 'Send Money',
            'conversion_credit' => 'Coin Purchase',
            'conversion_debit' => 'Coin Purchase',
            'live deposit' => 'LinkUp Live',
        ];
        $channelMap = [
            'deposit' => 'Wallet Deposit',
            'charge' => 'Merchant Payment',
            'transfer' => 'P2P Transfer',
            'conversion_credit' => 'LinkUp Coins',
            'conversion_debit' => 'LinkUp Coins',
            'live deposit' => 'Live Gift',
        ];
        $inboundProcessors = ['deposit', 'conversion_credit', 'live deposit'];

        $statusMap = [
            'success' => 'Completed',
            'pending' => 'Pending',
            'awaiting_approval' => 'Pending',
            'awaiting_payment' => 'Pending',
            'in_progress' => 'Pending',
            'on_hold' => 'Review',
            'refunded' => 'Review',
            'failed' => 'Failed',
            'canceled' => 'Failed',
            'expired' => 'Failed',
        ];

        return [
            'id' => $tx->uuid,
            'date' => $tx->created_at ? (is_string($tx->created_at) ? Carbon::parse($tx->created_at)->format('Y-m-d H:i') : $tx->created_at->format('Y-m-d H:i')) : '—',
            'country' => $subject?->country ?? 'Unknown',
            'user' => $subject?->name ?? 'Unknown',
            'type' => $typeMap[$processor] ?? ucfirst(str_replace(['_', '-'], ' ', $processor)),
            'direction' => in_array($processor, $inboundProcessors) ? 'In' : 'Out',
            'channel' => $channelMap[$processor] ?? ($tx->meta['description'] ?? ucfirst(str_replace(['_', '-'], ' ', $processor))),
            'amount' => (float)$tx->amount,
            'fee' => (float)($tx->commission ?? 0),
            'status' => $statusMap[is_object($tx->status) ? $tx->status->value : $tx->status] ?? 'Completed',
            'balanceAfter' => (float)($tx->meta['balance_after'] ?? $tx->received ?? $tx->amount),
        ];
    }

    /**
     * Maps a BankWithdrawal (the real "Cash Out" feature — separate from the
     * o21/laravel-wallet ledger, which has no withdrawal processor of its own)
     * onto the same Mission Control ledger shape as mapWalletMovement().
     */
    private function mapBankWithdrawal($w): array
    {
        $statusMap = [
            'pending' => 'Pending',
            'processing' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Failed',
        ];

        return [
            'id' => 'BWD-' . $w->id,
            'date' => $w->created_at ? (is_string($w->created_at) ? Carbon::parse($w->created_at)->format('Y-m-d H:i') : $w->created_at->format('Y-m-d H:i')) : '—',
            'country' => $w->user?->country ?? 'Unknown',
            'user' => $w->user?->name ?? 'Unknown',
            'type' => 'Cash Out',
            'direction' => 'Out',
            'channel' => 'Bank Withdrawal',
            'amount' => (float)$w->amount,
            'fee' => (float)($w->fee_amount ?? 0),
            'status' => $statusMap[$w->status] ?? 'Pending',
            // BankWithdrawal doesn't snapshot the wallet balance (unlike Transaction.meta),
            // so there's no real post-withdrawal balance to surface here.
            'balanceAfter' => 0,
        ];
    }

    private function getBusinessUnits(): array
    {
        $unitData = [
            ['key' => 'tickets', 'name' => 'Ticket Sales', 'icon' => 'ticket', 'platformRate' => 0.065, 'bankRate' => 0, 'costRate' => 0.01, 'desc' => 'Tickets, VIP, drink tickets, event add-ons'],
            ['key' => 'subscriptions', 'name' => 'Subscriptions', 'icon' => 'badge-dollar-sign', 'platformRate' => 1, 'bankRate' => 0, 'costRate' => 0.04, 'desc' => 'Premium users and paid plans'],
            ['key' => 'marketplace', 'name' => 'Marketplace', 'icon' => 'shopping-bag', 'platformRate' => 0.05, 'bankRate' => 0, 'costRate' => 0.01, 'desc' => 'Seller fees and commissions'],
            ['key' => 'eats', 'name' => 'LinkUp Eats', 'icon' => 'utensils', 'platformRate' => 0.075, 'bankRate' => 0, 'costRate' => 0.025, 'desc' => 'QR menus, ordering, pickup, delivery'],
            ['key' => 'merchantPay', 'name' => 'Merchant Pay', 'icon' => 'credit-card', 'platformRate' => 0.02, 'bankRate' => 0, 'costRate' => 0.007, 'desc' => 'QR payments, card, ACH, bill pay'],
            ['key' => 'wallet', 'name' => 'Wallet & Money Movement', 'icon' => 'wallet', 'platformRate' => 0.025, 'bankRate' => 0.0175, 'costRate' => 0.008, 'desc' => 'Cash-in (top-up), cash-out (withdrawal), transfers'],
            ['key' => 'live', 'name' => 'LinkUp Live', 'icon' => 'radio', 'platformRate' => 0.5, 'bankRate' => 0, 'costRate' => 0.08, 'desc' => 'Live coins, gifts, creators'],
            ['key' => 'ads', 'name' => 'Advertising Revenue', 'icon' => 'megaphone', 'platformRate' => 1, 'bankRate' => 0, 'costRate' => 0.12, 'desc' => 'Swipe ads, email ads, promoted posts'],
            ['key' => 'wellness', 'name' => 'Wellness & Spa', 'icon' => 'sparkles', 'platformRate' => 0.0675, 'bankRate' => 0, 'costRate' => 0.015, 'desc' => 'Bookings and appointment marketplace'],
            ['key' => 'cookouts', 'name' => 'Cookouts', 'icon' => 'flame', 'platformRate' => 0.0675, 'bankRate' => 0, 'costRate' => 0.015, 'desc' => 'Food events and vendor sales'],
            ['key' => 'linkup360', 'name' => 'LinkUp 360 News Ads', 'icon' => 'newspaper', 'platformRate' => 1, 'bankRate' => 0, 'costRate' => 0.15, 'desc' => 'Sponsored news and media placements'],
        ];

        return array_map(fn($data) => BusinessUnitDTO::fromArray($data), $unitData);
    }

    private function getCountryMetrics(): array
    {
        $countryNames = $this->repository->getCountryNames();

        if (empty($countryNames)) {
            return [];
        }

        $metrics = [];
        foreach ($countryNames as $name) {
            $metrics[$name] = new CountryMetricDTO($name, $this->inferRegion($name));
        }

        // Mapping helper to update metrics from repository collections
        $mapStats = function ($stats, $field) use (&$metrics) {
            foreach ($stats as $stat) {
                if (isset($metrics[$stat->country])) {
                    $metrics[$stat->country]->{$field} = (float)($stat->count ?? $stat->gtv ?? 0);
                }
            }
        };

        $mapStats($this->repository->getUserStats(), 'users');
        $mapStats($this->repository->getMerchantStats(), 'merchants');
        $mapStats($this->repository->getOrganizerStats(), 'organizers');
        $mapStats($this->repository->getTicketStats(), 'tickets');
        $mapStats($this->repository->getSubscriptionStats(), 'subscriptions');
        $mapStats($this->repository->getMarketplaceStats(), 'marketplace');
        $mapStats($this->repository->getLiveStats(), 'live');
        $mapStats($this->repository->getWalletStats(), 'wallet');
        $mapStats($this->repository->getAdStats(), 'ads');
        $mapStats($this->repository->getWellnessStats(), 'wellness');
        $mapStats($this->repository->getCookoutStats(), 'cookouts');

        return array_values($metrics);
    }

    public function getPayoutOpsDashboardData(): PayoutOpsDashboardDataDTO
    {
        $units = $this->getBusinessUnits();
        $countries = $this->getCountryMetrics();

        $organizerPayouts = $this->repository->getSettlements(50);
        $userWithdrawals = $this->repository->getBankWithdrawals(50);
        $sellerCashOuts = $this->repository->getSellerCashOutRequests(50);
        $creatorWithdrawals = $this->repository->getWithdrawRequests(50);
        $activityLogs = $this->repository->getPayoutActivityLogs(20);

        $normalizedRequests = collect()
            ->concat($organizerPayouts->map(fn($p) => $this->mapOrganizerPayout($p)))
            ->concat($userWithdrawals->map(fn($w) => $this->mapUserWithdrawal($w)))
            ->concat($sellerCashOuts->map(fn($s) => $this->mapSellerCashOut($s)))
            ->concat($creatorWithdrawals->map(fn($c) => $this->mapCreatorWithdrawal($c)))
            ->sortByDesc('date')
            ->values()
            ->toArray();

        $auditTrails = $activityLogs->map(fn($log) => [
            'date' => $log->created_at ? (is_string($log->created_at) ? Carbon::parse($log->created_at)->format('Y-m-d H:i') : $log->created_at->format('Y-m-d H:i')) : '—',
            'admin' => $log->user?->name ?? 'System',
            'action' => $log->title,
            'detail' => $log->description,
        ])->toArray();

        // Calculate totals for summary cards
        $totals = $this->calculatePayoutTotals($normalizedRequests);

        return new PayoutOpsDashboardDataDTO($units, $countries, $normalizedRequests, $auditTrails, $totals);
    }

    private function mapOrganizerPayout($p): array
    {
        $statusMap = [
            'pending' => 'Pending Review',
            'processing' => 'Processing',
            'approved' => 'Completed',
            'ok' => 'Completed',
            'verified' => 'Completed',
            'rejected' => 'Failed',
        ];

        return [
            'id' => $p->id,
            'requestId' => $p->reference ?? ('PO-' . $p->id),
            'date' => $p->created_at ? (is_string($p->created_at) ? Carbon::parse($p->created_at)->format('Y-m-d H:i') : $p->created_at->format('Y-m-d H:i')) : '—',
            'category' => 'Organizer Payout',
            'name' => $p->organizer?->user?->name ?? $p->author,
            'email' => $p->organizer?->user?->email ?? '—',
            'country' => $p->organizer?->user?->country ?? 'Unknown',
            'amount' => (float)$p->amount,
            'feePercent' => $p->amount > 0 ? (float)($p->fee_amount / $p->amount * 100) : 0,
            'bank' => $p->destination ?: '—',
            'account' => '—',
            'accountMasked' => '—',
            'routing' => '—',
            'method' => $p->method ?? 'Bank Transfer',
            'status' => $statusMap[strtolower($p->status)] ?? 'Pending Review',
            'risk' => 'Low', // Need risk logic or field
            'notes' => $p->notes ?? '',
            'proof' => '',
        ];
    }

    private function mapUserWithdrawal($w): array
    {
        $statusMap = [
            'pending' => 'Pending Review',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Failed',
        ];

        $bank = '—';
        $account = '—';
        $accountMasked = '—';

        try {
            $bank = $w->bank_name ?: '—';
            $account = $w->account_number ?: '—';
            $accountMasked = $w->account_number ? '••• ' . substr($w->account_number, -4) : '—';
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            $bank = 'Encrypted (unreadable)';
        }

        return [
            'id' => $w->id,
            'requestId' => 'UW-' . $w->id,
            'date' => $w->created_at ? (is_string($w->created_at) ? Carbon::parse($w->created_at)->format('Y-m-d H:i') : $w->created_at->format('Y-m-d H:i')) : '—',
            'category' => 'User Bank Withdrawal',
            'name' => $w->user?->name ?? 'Unknown',
            'email' => $w->user?->email ?? '—',
            'country' => $w->user?->country ?? 'Unknown',
            'amount' => (float)$w->amount,
            'feePercent' => (float)($w->fee_percent ?? 0),
            'bank' => $bank,
            'account' => $account,
            'accountMasked' => $accountMasked,
            'routing' => '—',
            'method' => 'Bank Withdrawal',
            'status' => $statusMap[strtolower($w->status)] ?? 'Pending Review',
            'risk' => 'Low',
            'notes' => $w->failure_reason ?? '',
            'proof' => '',
        ];
    }

    private function mapSellerCashOut($s): array
    {
        $statusMap = [
            'pending' => 'Pending Review',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'rejected' => 'Failed',
            'cancelled' => 'Failed',
        ];

        $bank = '—';
        $account = '—';
        $accountMasked = '—';

        if ($s->bankAccount) {
            try {
                $bank = $s->bankAccount->bank_name ?: '—';
                $account = $s->bankAccount->account_number ?: '—';
                $accountMasked = $s->bankAccount->account_number ? '••• ' . substr($s->bankAccount->account_number, -4) : '—';
            } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                $bank = 'Encrypted (unreadable)';
            }
        }

        return [
            'id' => $s->id,
            'requestId' => 'SEL-' . $s->id,
            'date' => $s->created_at ? (is_string($s->created_at) ? Carbon::parse($s->created_at)->format('Y-m-d H:i') : $s->created_at->format('Y-m-d H:i')) : '—',
            'category' => 'Seller Cash-Out',
            'name' => $s->user?->name ?? 'Unknown',
            'email' => $s->user?->email ?? '—',
            'country' => $s->user?->country ?? 'Unknown',
            'amount' => (float)$s->amount,
            'feePercent' => 0, // Need to verify if sellers have fees
            'bank' => $bank,
            'account' => $account,
            'accountMasked' => $accountMasked,
            'routing' => '—',
            'method' => 'Seller Payout',
            'status' => $statusMap[strtolower($s->status)] ?? 'Pending Review',
            'risk' => 'Low',
            'notes' => $s->rejection_reason ?? '',
            'proof' => '',
        ];
    }

    private function mapCreatorWithdrawal($c): array
    {
        $statusMap = [
            'pending' => 'Pending Review',
            'approved' => 'Completed',
            'rejected' => 'Failed',
        ];

        return [
            'id' => $c->id,
            'requestId' => 'CRE-' . $c->id,
            'date' => $c->created_at ? (is_string($c->created_at) ? Carbon::parse($c->created_at)->format('Y-m-d H:i') : $c->created_at->format('Y-m-d H:i')) : '—',
            'category' => 'Creator Cash-Out',
            'name' => $c->user?->name ?? 'Unknown',
            'email' => $c->user?->email ?? '—',
            'country' => $c->user?->country ?? 'Unknown',
            'amount' => (float)$c->amount,
            'feePercent' => 0,
            'bank' => '—',
            'account' => '—',
            'accountMasked' => '—',
            'routing' => '—',
            'method' => 'Live Creator Payout',
            'status' => $statusMap[strtolower($c->request_status)] ?? 'Pending Review',
            'risk' => 'Low',
            'notes' => $c->note ?? '',
            'proof' => '',
        ];
    }

    private function calculatePayoutTotals(array $requests): array
    {
        $totals = [
            'total' => 0, 'fees' => 0, 'pending' => 0, 'completed' => 0, 'failed' => 0,
            'user' => 0, 'seller' => 0, 'creator' => 0, 'organizer' => 0
        ];

        foreach ($requests as $p) {
            $fee = $p['amount'] * ($p['feePercent'] / 100);
            $totals['total'] += $p['amount'];
            $totals['fees'] += $fee;

            if (in_array($p['status'], ['Pending Review', 'Processing'])) $totals['pending'] += $p['amount'];
            if ($p['status'] === 'Completed') $totals['completed'] += $p['amount'];
            if ($p['status'] === 'Failed') $totals['failed']++;

            if ($p['category'] === 'User Bank Withdrawal') $totals['user'] += $p['amount'];
            if ($p['category'] === 'Seller Cash-Out') $totals['seller'] += $p['amount'];
            if ($p['category'] === 'Creator Cash-Out') $totals['creator'] += $p['amount'];
            if ($p['category'] === 'Organizer Payout') $totals['organizer'] += $p['amount'];
        }

        return $totals;
    }

    private function inferRegion(string $country): string
    {
        $caribbean = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Barbados', 'Guyana', 'Dominican Republic', 'Haiti', 'Saint Lucia', 'Grenada', 'Antigua & Barbuda'];
        if (in_array($country, $caribbean)) {
            return ($country === 'Bahamas') ? 'Local' : 'Regional';
        }
        return 'International';
    }
}
