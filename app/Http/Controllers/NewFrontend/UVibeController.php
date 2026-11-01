<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Vibes\Actions\CreateVibeAction;
use App\Domain\Vibes\DTOs\CreateVibeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vibes\StoreUVibeRequest;
use App\Services\UniversityService;
use App\Services\UVibeFeedService;
use App\Support\UVibeOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * U Vibe campus feed and the "Post to U Vibe" button. Registered on the web routes (session
 * login) and on the mobile API routes (Sanctum token), so the website and the app use the same
 * endpoints and rules — like UniversityController.
 */
class UVibeController extends Controller
{
    public function __construct(
        private readonly UVibeFeedService $feed,
        private readonly UniversityService $universities,
    ) {}

    /** What the composer lets a member choose: post types, organization types, text-card looks. */
    public function options(): JsonResponse
    {
        return response()->json(UVibeOptions::all());
    }

    /**
     * The campus feed. Optional filters: university_id (one campus), category (events, parties,
     * campus-life, clubs, food, opportunity or all), scope (all | mine | following), q (search
     * across every campus), page, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'university_id' => ['nullable', 'integer', 'exists:universities,id'],
            'category' => ['nullable', Rule::in(['all', ...UVibeOptions::categoryIds()])],
            'scope' => ['nullable', Rule::in(['all', 'mine', 'following'])],
            'q' => ['nullable', 'string', 'max:80'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json($this->feed->feed($request->user(), $filters));
    }

    /**
     * Post to U Vibe. Needs a title, a type and either text or media. Goes to `university_id`
     * (must be one of the member's own universities) or their main one. Can be posted as the
     * member or as one of their organizations/groups (`publisher_type` + `publisher_id`).
     */
    public function store(StoreUVibeRequest $request, CreateVibeAction $action): JsonResponse
    {
        $user = $request->user();
        $vibe = $action->execute($user, CreateVibeData::fromRequest($request));
        $vibe->load(UVibeFeedService::RELATIONS);

        return response()->json([
            'message' => $vibe->show_on_vibes ? '🎓 Posted to U Vibe & Vibes' : '🎓 Posted to U Vibe',
            'post' => $this->feed->present($vibe, $user),
            'state' => $this->universities->state($user),
        ], 201);
    }
}
