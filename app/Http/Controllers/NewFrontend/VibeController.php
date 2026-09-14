<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Vibes\Actions\CreateVibeAction;
use App\Domain\Vibes\DTOs\CreateVibeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vibes\StoreVibeRequest;
use App\Http\Resources\VibeResource;
use Illuminate\Http\JsonResponse;

class VibeController extends Controller
{
    public function store(StoreVibeRequest $request, CreateVibeAction $action): JsonResponse
    {
        $vibe = $action->execute($request->user(), CreateVibeData::fromRequest($request));

        return (new VibeResource($vibe))->response()->setStatusCode(201);
    }
}
