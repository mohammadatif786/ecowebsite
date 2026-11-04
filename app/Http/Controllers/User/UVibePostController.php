<?php

namespace App\Http\Controllers\User;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Http\Controllers\Controller;
use App\Models\UvibeApplication;
use App\Models\Vibe;
use App\Services\UVibePostActionService;
use App\Support\UVibeOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What a member can do with a U Vibe post in the feed: edit, delete, hide, report, repost, Boost,
 * and for Campus Gigs apply and pay. Registered on the web routes (session login) and on the mobile
 * API routes (Sanctum token) — the same endpoints and the same rules (UVibePostActionService).
 */
class UVibePostController extends Controller
{
    public function __construct(private readonly UVibePostActionService $actions) {}

    /** Edit your own post: title, words, and (not for gigs) price and the linked event. */
    public function update(Request $request, Vibe $vibe): JsonResponse
    {
        $user = $request->user();
        $this->uvibe($vibe);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:'.config('vibes.caption_max', 2200)],
            'price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'event_id' => ['nullable', 'integer', 'exists:link_up_events,id'],
        ], ['title.required' => 'Add a title.']);

        $this->actions->edit($user, $vibe->loadMissing('media', 'events'), $data);

        return response()->json(['message' => '✏️ Post updated', 'post' => $this->actions->present($vibe, $user)]);
    }

    /** Delete your own post. */
    public function destroy(Request $request, Vibe $vibe): JsonResponse
    {
        $this->uvibe($vibe);
        $this->actions->delete($request->user(), $vibe);

        return response()->json(['message' => '🗑️ Post deleted', 'id' => $vibe->id]);
    }

    /** Hide someone else's post from your feed. */
    public function hide(Request $request, Vibe $vibe): JsonResponse
    {
        $this->uvibe($vibe);
        $this->actions->hide($request->user(), $vibe);

        return response()->json(['message' => '🙈 Post hidden', 'id' => $vibe->id]);
    }

    /** Report someone else's post (`reason` is one of `report_reasons` in the options). */
    public function report(Request $request, Vibe $vibe): JsonResponse
    {
        $this->uvibe($vibe);

        $data = $request->validate([
            'reason' => ['required', 'string', Rule::in(UVibeOptions::reportReasonKeys())],
            'notes' => ['nullable', 'string', 'max:500'],
        ], ['reason.required' => 'Choose what’s wrong with the post.', 'reason.in' => 'Choose what’s wrong with the post.']);

        $result = $this->actions->report($request->user(), $vibe->loadMissing('media'), $data['reason'], $data['notes'] ?? null);

        return response()->json([
            'already' => $result['already'],
            'message' => $result['already']
                ? 'You’ve already reported this post — it’s with our team'
                : '🚩 Report sent — thank you. Our team will review it.',
        ]);
    }

    /** Repost someone else's post onto your own campus. */
    public function repost(Request $request, Vibe $vibe): JsonResponse
    {
        $user = $request->user();
        $this->uvibe($vibe);

        $result = $this->actions->repost($user, $vibe->loadMissing('media', 'products', 'events'));
        $copy = $this->actions->present($result['post'], $user);

        return response()->json([
            'message' => '🔁 Reposted to '.$copy['campus'],
            'post' => $copy,
            'original' => ['id' => $result['original']->id, 'reposts' => (int) $result['original']->reposts_count, 'reposted' => true],
        ], 201);
    }

    /** The Boosts a member can send and how many coins they have. */
    public function boosts(Request $request): JsonResponse
    {
        return response()->json([
            'coins' => (int) $request->user()->coins,
            'boosts' => UVibeOptions::all()['boosts'],
        ]);
    }

    /** Send a Boost (`gift` is one of `boosts[].key`). Coins only. */
    public function boost(Request $request, Vibe $vibe): JsonResponse
    {
        $this->uvibe($vibe);

        $data = $request->validate(['gift' => ['required', 'string']], ['gift.required' => 'Choose a Boost to send.']);

        $result = $this->actions->boost($request->user(), $vibe, $data['gift']);

        return response()->json([
            'message' => "⚡ Boost sent! -{$result['gift']['coins']} 🪙",
            'post_id' => $vibe->id,
            'coins' => $result['coins'],
            'boost_coins' => $result['boost_coins'],
        ]);
    }

    /** Apply to a Campus Gig. */
    public function apply(Request $request, Vibe $vibe): JsonResponse
    {
        $this->uvibe($vibe);

        $result = $this->actions->apply($request->user(), $vibe);

        return response()->json([
            'already' => $result['already'],
            'message' => $result['already'] ? 'Already applied' : '✅ Applied — get it done!',
            'status' => $result['application']->status,
        ], $result['already'] ? 200 : 201);
    }

    /** Who applied to your gig. */
    public function applicants(Request $request, Vibe $vibe): JsonResponse
    {
        $this->uvibe($vibe);

        return response()->json([
            'pay' => (float) $vibe->price,
            'applicants' => $this->actions->applicants($request->user(), $vibe),
        ]);
    }

    /** Pay a worker the gig's pay from your wallet (needs your 4-digit transaction PIN). */
    public function pay(Request $request, Vibe $vibe, UvibeApplication $application): JsonResponse
    {
        $this->uvibe($vibe);

        $data = $request->validate(
            ['pin' => ['required', 'string', 'digits:4']],
            ['pin.required' => 'Transaction PIN is required.', 'pin.digits' => 'Transaction PIN must be 4 digits.'],
        );

        $paid = $this->actions->pay($request->user(), $vibe, $application, $data['pin']);

        return response()->json([
            'message' => '🎉 Gig paid — $'.number_format((float) $paid->paid_amount, 2).' sent to '.($paid->user?->name ?? 'the worker'),
            'application' => [
                'id' => $paid->id,
                'status' => $paid->status,
                'paid_amount' => (float) $paid->paid_amount,
                'paid_at' => $paid->paid_at?->toISOString(),
            ],
        ]);
    }

    /** Only live U Vibe posts take part in these actions. */
    private function uvibe(Vibe $vibe): void
    {
        abort_unless($vibe->is_uvibe && $vibe->status === VibeStatus::Published, 404, 'Post not found.');
    }
}
