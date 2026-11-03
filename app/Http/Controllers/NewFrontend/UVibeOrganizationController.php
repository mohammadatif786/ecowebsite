<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Services\UVibeOrganizationService;
use App\Support\UVibeOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Post as → An organization" on U Vibe: the organizations a member has added. Registered on the
 * web routes (session login) and the mobile API routes (Sanctum token), like the other U Vibe
 * controllers, so the website and the app behave the same.
 */
class UVibeOrganizationController extends Controller
{
    public function __construct(private readonly UVibeOrganizationService $organizations) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['organizations' => $this->organizations->list($request->user())]);
    }

    /** Add an organization: { name, org_type } — returns the existing one when the name is already on the list. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:'.UVibeOptions::ORG_NAME_MAX],
            'org_type' => ['nullable', 'string', Rule::in(UVibeOptions::orgTypeLabels())],
        ]);

        [$organization, $created] = $this->organizations->add($request->user(), $data['name'], $data['org_type'] ?? null);

        return response()->json([
            'message' => $created ? "✅ {$organization['name']} added" : "{$organization['name']} is already on your list",
            'organization' => $organization,
            'created' => $created,
        ], $created ? 201 : 200);
    }
}
