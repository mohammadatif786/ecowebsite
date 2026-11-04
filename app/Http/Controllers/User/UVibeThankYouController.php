<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UVibeThankYouService;
use App\Support\UVibeOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Say Thank You" on U Vibe. Registered on the web routes (session login) and on the mobile API
 * routes (Sanctum token) — one endpoint, one set of rules (UVibeThankYouService).
 */
class UVibeThankYouController extends Controller
{
    public function __construct(private readonly UVibeThankYouService $thanks) {}

    /** Who is waiting for a thank-you: { senders:[{user_id,name,avatar,gift,emoji,coins,count,time}], template, max }. */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->thanks->senders($request->user()));
    }

    /** Send a thank-you: { user_id, message }. Resolves to the refreshed list. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'message' => ['required', 'string', 'max:'.UVibeOptions::THANK_YOU_MAX],
        ], [
            'user_id.required' => 'Choose who to thank.',
            'message.required' => 'Write a thank-you message first.',
            'message.max' => 'Keep it under '.UVibeOptions::THANK_YOU_MAX.' characters.',
        ]);

        $result = $this->thanks->thank($request->user(), (int) $data['user_id'], $data['message']);

        return response()->json([
            'message' => '🙏 Thanks sent to '.$result['name'],
            'senders' => $result['senders'],
        ]);
    }
}
