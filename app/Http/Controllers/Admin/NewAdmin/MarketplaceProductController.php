<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\MarketplaceProductRequest;
use App\Models\MarketplaceProduct;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MarketplaceProductController extends Controller
{
    public function index(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        $products = MarketplaceProduct::with(['seller', 'category'])
            ->filter($request->only(['search']))
            ->when($request->category, function($q, $cat) {
                $q->where('product_category_id', $cat);
            })
            ->when($request->status, function($q, $status) {
                if ($status !== 'All') {
                    $q->where('status', $status === 'Active');
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceProducts', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'products' => $products,
            'filters' => $request->only(['search', 'category', 'status']),
            'categories' => ProductCategory::all(),
        ]);
    }

    public function create(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceProductCreate', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'categories' => ProductCategory::all(),
            'sellers' => User::select('id', 'name')->get(), // In real app, filter by seller role
        ]);
    }

    public function store(MarketplaceProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image_file')) {
            $path = $request->file('cover_image_file')->store('marketplace/products', 'public');
            $data['cover_image'] = Storage::url($path);
        }

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('marketplace/products/gallery', 'public');
                $images[] = Storage::url($path);
            }
            $data['images'] = $images;
        }

        MarketplaceProduct::create($data);

        return redirect()->route('admin.commerce.marketplace.products')
            ->with('message', 'Product created successfully');
    }

    public function show(MarketplaceProduct $product, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        $product->load(['seller', 'category']);

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceProductView', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'product' => $product,
        ]);
    }

    public function edit(MarketplaceProduct $product, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceProductEdit', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'product' => $product,
            'categories' => ProductCategory::all(),
            'sellers' => User::select('id', 'name')->get(),
        ]);
    }

    public function update(MarketplaceProductRequest $request, MarketplaceProduct $product)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image_file')) {
            if ($product->cover_image) {
                // Optional: delete old image
                // Storage::disk('public')->delete(str_replace('/storage/', '', $product->cover_image));
            }
            $path = $request->file('cover_image_file')->store('marketplace/products', 'public');
            $data['cover_image'] = Storage::url($path);
        }

        if ($request->hasFile('images')) {
            $images = $product->images ?? [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('marketplace/products/gallery', 'public');
                $images[] = Storage::url($path);
            }
            $data['images'] = $images;
        }

        $product->update($data);

        return redirect()->route('admin.commerce.marketplace.products')
            ->with('message', 'Product updated successfully');
    }

    public function destroy(MarketplaceProduct $product)
    {
        $product->delete();
        return redirect()->back()->with('message', 'Product deleted successfully');
    }
}
