<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceAffiliatePromotion;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketplaceAffiliateController extends Controller
{
    public function toggle(Request $request, Product $product)
    {
        abort_unless($product->status && $product->user_id !== $request->user()->id, 422, 'This product cannot be promoted.');
        abort_if($product->commMode === 'none' || ((float) $product->commission <= 0 && (float) $product->commFlat <= 0), 422, 'This product does not offer affiliate commission.');

        $promotion = MarketplaceAffiliatePromotion::query()
            ->where('user_id', $request->user()->id)->where('product_id', $product->id)->first();

        if ($promotion) {
            $promotion->delete();
            return response()->json(['promoting' => false]);
        }

        MarketplaceAffiliatePromotion::create(['user_id' => $request->user()->id, 'product_id' => $product->id, 'public_token' => Str::random(48)]);
        return response()->json(['promoting' => true]);
    }

    public function attribute(Request $request, string $token)
    {
        $promotion = MarketplaceAffiliatePromotion::query()->where('public_token', $token)->with('product')->firstOrFail();
        abort_unless($promotion->product?->status, 404);
        $request->session()->put('marketplace.affiliate_promotion_id', $promotion->id);
        return redirect()->route('new_frontend.marketplace');
    }
}
