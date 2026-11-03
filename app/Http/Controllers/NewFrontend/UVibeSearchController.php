<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Services\UVibeSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The U Vibe search window: people and campuses. Registered on the web routes (session login) and
 * on the mobile API routes (Sanctum token) — one endpoint, one set of rules (UVibeSearchService).
 */
class UVibeSearchController extends Controller
{
    public function __construct(private readonly UVibeSearchService $search) {}

    /**
     * `GET uvibe/search?q=` → { q, people:[{user_id,name,handle,avatar,campus,posts,orgs}], campuses:[…] }.
     * With no `q` it lists the people who post on U Vibe (A–Z), so the window is useful before typing.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:80']]);

        return response()->json($this->search->search($request->user(), $data['q'] ?? null));
    }
}
