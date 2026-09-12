<?php

namespace App\Http\Controllers\Admin\Subscriptions;

use App\Actions\Admin\SubscriptionPlans\CreateSubscriptionPlanAction;
use App\Actions\Admin\SubscriptionPlans\DeleteSubscriptionPlanAction;
use App\Actions\Admin\SubscriptionPlans\UpdateSubscriptionPlanAction;
use App\Actions\Admin\SubscriptionPlans\GetDashboardStatsAction;
use App\Actions\Admin\SubscriptionPlans\GetSubscribedUsersAction;
use App\Actions\Admin\SubscriptionPlans\GetSubscriptionRevenueAction;
use App\Actions\Admin\SubscriptionPlans\GetGoLiveStatsAction;
use App\Actions\Admin\SubscriptionPlans\GetClubRestaurantStatsAction;
use App\Actions\Admin\SubscriptionPlans\GetMarketplaceStatsAction;
use App\Actions\Admin\SubscriptionPlans\GetEventsStatsAction;
use App\Actions\Admin\SubscriptionPlans\GetLabStatsAction;
use App\DTOs\SubscriptionPlanData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionPlanRequest;
use App\Http\Resources\SubscriptionPlanResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;
use App\Contracts\FeatureGateRepositoryInterface;
use App\Contracts\SubscriptionPlanRepositoryInterface;
use App\Http\Resources\FeatureGateResource;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserPlanController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly FeatureGateRepositoryInterface $repository,
        private readonly SubscriptionPlanRepositoryInterface $subRepository,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:subscription dashboard', only: ['dashboard']),
            new Middleware('permission:subscription index', only: ['index']),
            new Middleware('permission:subscription create', only: ['store']),
            new Middleware('permission:subscription update', only: ['update']),
            new Middleware('permission:subscription delete', only: ['delete']),
        ];
    }

    public function dashboard(GetDashboardStatsAction $action)
    {
        $data = $action->execute();

        return Inertia::render('admin/subscriptions/Dashboard', [
            'stats' => $data['stats'],
            'planDistribution' => $data['planDistribution'],
            'recentSignups' => $data['recentSignups'],
        ]);
    }

    public function subscribedUser(Request $request, GetSubscribedUsersAction $action)
    {
        $formattedUsers = $action->execute();

        return Inertia::render('admin/subscriptions/UsersPage', [
            'users' => $formattedUsers
        ]);
    }

    public function subscriptionGoLive(GetGoLiveStatsAction $action)
    {
        $data = $action->execute();

        return Inertia::render('admin/subscriptions/GoLive', [
            'stats' => $data['stats'],
            'planAccess' => $data['planAccess'],
        ]);
    }

    public function subscriptionClubRestaurant(GetClubRestaurantStatsAction $action)
    {
        $data = $action->execute();

        return Inertia::render('admin/subscriptions/ClubAndRestaurant', [
            'stats' => $data['stats'],
            'planAccess' => $data['planAccess'],
        ]);
    }

    public function subscriptionMarketPlace(GetMarketplaceStatsAction $action)
    {
        $data = $action->execute();
        return Inertia::render('admin/subscriptions/Marketplace', $data);
    }

    public function subscriptionEvents(GetEventsStatsAction $action)
    {
        $data = $action->execute();
        return Inertia::render('admin/subscriptions/Events', $data);
    }

    public function subscriptionLab(GetLabStatsAction $action)
    {
        $data = $action->execute();
        return Inertia::render('admin/subscriptions/Lab', $data);
    }

    public function index(Request $request)
    {
        $plans = $this->subRepository->all();
        $gates = $this->repository->all();

        return Inertia::render('admin/subscriptions/SubscriptionsPage', [
            'plans' => $plans,
            'featureGates' => FeatureGateResource::collection($gates),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionPlanRequest $request, CreateSubscriptionPlanAction $action)
    {
        $data = SubscriptionPlanData::fromArray($request->validated());

        try {
            $plan = $action->execute($data);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'stripe_product_id' => $e->getMessage(),
                ],
            ], 422);
        }

        return (new SubscriptionPlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSubscriptionPlanRequest $request, UpdateSubscriptionPlanAction $action, int $planId)
    {
        $data = SubscriptionPlanData::fromArray($request->validated());

        try {
            $updated = $action->execute($planId, $data);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['stripe_product_id' => $e->getMessage()],
            ], 422);
        }

        return (new SubscriptionPlanResource($updated))
            ->response()
            ->setStatusCode(200);
    }

    public function delete(DeleteSubscriptionPlanAction $action, int $planId)
    {
        $action->execute($planId);

        return response()->json(['message' => 'Plan deleted.'], 200);
    }

    /**
     * get subscriptions revenue
     */
    public function subscriptionRevenue(GetSubscriptionRevenueAction $action)
    {
        $data = $action->execute();

        return Inertia::render('admin/subscriptions/RevenuePage', [
            'stats' => $data['stats'],
            'planBreakdown' => $data['planBreakdown'],
            'monthlyData' => $data['monthlyData'],
            'totalMRR' => $data['totalMRR'],
        ]);
    }
}
