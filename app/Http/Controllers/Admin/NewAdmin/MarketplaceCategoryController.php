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
            ->when($request->input('status') === 'active', fn ($q) => $q->where('status', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('status', false))
            ->withCount('products')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceCategories', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'categories' => $categories,
            'filters' => $request->only('search', 'status'),
            'totalActive' => ProductCategory::where('status', true)->count(),
            'totalProducts' => \App\Models\Product::count(),
            'totalCountries' => \App\Models\User::whereNotNull('country')->distinct('country')->count(),
            'totalFeatured' => ProductCategory::where('is_featured', true)->count(),
        ]);
    }

    public function store(MarketplaceCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated() + ['is_featured' => false]);

        return back()->with('success', "Category \"{$category->name}\" created. It now shows in the member Marketplace.");
    }

    public function update(MarketplaceCategoryRequest $request, ProductCategory $category)
    {
        $category->update($request->validated());

        return back()->with('success', $category->status
            ? "Category \"{$category->name}\" saved."
            : "Category \"{$category->name}\" saved and hidden from the member Marketplace.");
    }

    public function destroy(ProductCategory $category)
    {
        $count = $category->products()->count();
        if ($count > 0) {
            return back()->with('error', "\"{$category->name}\" has {$count} product(s). Move them to another category or set it to Inactive instead.");
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', "Category \"{$name}\" deleted.");
    }
}
