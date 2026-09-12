<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\MarketplaceCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceCategoryController extends Controller
{
    public function index(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        $categories = ProductCategory::query()
            ->filter($request->only('search'))
            ->withCount('products')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceCategories', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'categories' => $categories,
            'filters' => $request->only('search'),
            'totalProducts' => \App\Models\Product::count(),
            'totalCountries' => \App\Models\User::whereNotNull('country')->distinct('country')->count(),
            'totalFeatured' => ProductCategory::where('is_featured', true)->count(),
        ]);
    }

    public function store(MarketplaceCategoryRequest $request)
    {
        ProductCategory::create($request->validated());

        return redirect()->route('admin.commerce.marketplace.categories')
            ->with('message', 'Category created successfully');
    }

    public function update(MarketplaceCategoryRequest $request, ProductCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.commerce.marketplace.categories')
            ->with('message', 'Category updated successfully');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with associated products');
        }

        $category->delete();

        return redirect()->back()->with('message', 'Category deleted successfully');
    }
}
