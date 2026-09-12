<?php

namespace App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard;

use App\Actions\UpdateProductListingAction;
use App\Http\Controllers\Controller;
use App\Models\Merchants;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SellerStoreController extends Controller
{
    public function __construct(
        private UpdateProductListingAction $updateListingAction
    ) {
    }

    /**
     * Seller's listings + merchants + categories.
     */
    public function index()
    {
        $products = Product::with(['category', 'merchant'])
            ->withCount('orderItems')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $merchants = Merchants::where('user_id', Auth::id())->where('is_active', 1)->get();
        $categories = ProductCategory::where('status', 1)->get();

        return Inertia::render('User/LinkUpShop/SellerDashboard/Store', [
            'products' => $products,
            'merchants' => $merchants,
            'categories' => $categories,
        ]);
    }

    /**
     * Update listing price, stock and status.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ]);

        $this->updateListingAction->execute(
            $product,
            (float) $request->price,
            (int) $request->qty,
            (int) $request->boolean('status'),
        );

        return back()->with('success', 'Listing updated successfully.');
    }

    /**
     * Delete a listing.
     */
    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $product->delete();

        return back()->with('success', 'Product removed from your store.');
    }

}
