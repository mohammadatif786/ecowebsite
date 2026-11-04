<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Services\UniversityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * U Vibe campuses. Registered on the web routes (session login) and on the mobile API
 * routes (Sanctum token), so the website and the app use the same endpoints and rules.
 * Every change returns the member's updated `state` (own universities + followed campuses).
 */
class UniversityController extends Controller
{
    public function __construct(private readonly UniversityService $universities) {}

    /** Campus list for sign-up and the Campus Explorer. Works signed in or not. */
    public function catalog(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($this->universities->catalog(
            $request->user('sanctum') ?? $request->user(),
            $request->input('q'),
            $request->input('country'),
        ));
    }

    /** The member's own universities and followed campuses. */
    public function state(Request $request): JsonResponse
    {
        return response()->json(['state' => $this->universities->state($request->user())]);
    }

    /**
     * Add a school to My universities: { university_id } or { name } for a new campus.
     * A new campus takes `country` (name or ISO code) when given, otherwise the member's own country.
     */
    public function addMine(Request $request): JsonResponse
    {
        $data = $request->validate([
            'university_id' => ['nullable', 'integer', 'exists:universities,id', 'required_without:name'],
            'name' => ['nullable', 'string', 'min:3', 'max:80', 'required_without:university_id'],
            'status' => ['nullable', 'in:student,alumni'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $uni = $this->universities->addMine($request->user(), $data['university_id'] ?? null, $data['name'] ?? null, $data['status'] ?? null, $data['country'] ?? null);

        return $this->ok($request, "🎓 {$uni->name} added to your universities");
    }

    public function removeMine(Request $request, University $university): JsonResponse
    {
        $this->universities->removeMine($request->user(), $university);

        return $this->ok($request, "Removed {$university->name}");
    }

    public function setStatus(Request $request, University $university): JsonResponse
    {
        $data = $request->validate(['status' => ['required', 'in:student,alumni']]);
        $this->universities->setStatus($request->user(), $university, $data['status']);

        return $this->ok($request, 'Saved');
    }

    public function makeMain(Request $request, University $university): JsonResponse
    {
        $this->universities->makeMain($request->user(), $university);

        return $this->ok($request, "🏠 {$university->name} is now your main university");
    }

    public function follow(Request $request, University $university): JsonResponse
    {
        $this->universities->follow($request->user(), $university);

        return $this->ok($request, "⭐ Following {$university->name}");
    }

    public function unfollow(Request $request, University $university): JsonResponse
    {
        $this->universities->unfollow($request->user(), $university);

        return $this->ok($request, "Unfollowed {$university->name}");
    }

    private function ok(Request $request, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'state' => $this->universities->state($request->user()),
        ]);
    }
}
