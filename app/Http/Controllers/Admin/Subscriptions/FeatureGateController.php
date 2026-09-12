<?php

namespace App\Http\Controllers\Admin\Subscriptions;

use App\Actions\Admin\SubscriptionPlans\CreateFeatureGateAction;
use App\Actions\Admin\SubscriptionPlans\DeleteFeatureGateAction;
use App\Actions\Admin\SubscriptionPlans\UpdateFeatureGateAction;
use App\Contracts\FeatureGateRepositoryInterface;
use App\DTOs\FeatureGateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureGateRequest;
use App\Http\Requests\UpdateFeatureGateRequest;
use App\Http\Resources\FeatureGateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FeatureGateController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly FeatureGateRepositoryInterface $repository,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:feature gate index', only: ['index']),
            new Middleware('permission:feature gate delete', only: ['delete']),
            new Middleware('permission:feature gate store', only: ['store']),
            new Middleware('permission:feature gate update', only: ['update']),
        ];
    }

    public function index(): JsonResponse
    {
        $gates = $this->repository->all();

        return FeatureGateResource::collection($gates)->response();
    }

    public function store(StoreFeatureGateRequest $request, CreateFeatureGateAction $action): JsonResponse
    {
        $data = FeatureGateData::fromArray($request->validated());
        $gate = $action->execute($data);

        return (new FeatureGateResource($gate))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateFeatureGateRequest $request, UpdateFeatureGateAction $action, int $gateId,): JsonResponse
    {
        $data = FeatureGateData::fromArray($request->validated());
        $gate = $action->execute($gateId, $data);

        return (new FeatureGateResource($gate))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(DeleteFeatureGateAction $action, int $gateId,): JsonResponse
    {
        $action->execute($gateId);

        return response()->json(['message' => 'Gate deleted.'], 200);
    }
}
