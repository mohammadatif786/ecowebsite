<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Vibe;
use App\Services\VibeShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Buy now" on a Vibe's tagged Marketplace item (web and app). Events are bought on the
 * event's own page (ticket types, add-ons) with `vibe_id` so the poster still earns.
 */
class VibeShopController extends Controller
{
    public function __construct(private readonly VibeShopService $shop) {}

    /** `POST vibes/{vibe}/buy/quote` → the server's price for this item, delivery or pickup. */
    public function quote(Request $request, Vibe $vibe): JsonResponse
    {
        abort_unless($vibe->isVisibleTo($request->user()), 404);

        return response()->json($this->shop->quote($request->user(), $vibe, $this->validated($request)));
    }

    /** `POST vibes/{vibe}/buy` → places a paid order from the wallet. */
    public function buy(Request $request, Vibe $vibe): JsonResponse
    {
        abort_unless($vibe->isVisibleTo($request->user()), 404);

        return response()->json($this->shop->buy($request->user(), $vibe, $this->validated($request, true)), 201);
    }

    private function validated(Request $request, bool $buying = false): array
    {
        // The website's old modal sent {kind, id}; accept that shape too.
        if (! $request->has('product_id') && $request->input('kind', 'product') === 'product' && $request->filled('id')) {
            $request->merge(['product_id' => $request->input('id')]);
        }
        if ($request->input('kind') === 'event') {
            abort(response()->json(['message' => 'Choose your tickets on the event page.'], 422));
        }

        return $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:'.VibeShopService::MAX_QTY],
            'shipping_method' => ['nullable', 'in:standard,pickup'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'expected_total' => $buying ? ['nullable', 'numeric', 'min:0'] : ['nullable'],
        ]);
    }
}
