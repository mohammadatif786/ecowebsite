<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Shops\ProductCategoryRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginator = ProductCategory::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/shops/categories/Index', [
            'paginator' => $paginator,
            'filters' => $request->only('search'),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCategoryRequest $request)
    {
        ProductCategory::create($request->all());
        return redirect()->back()->withSuccess('Product Category Created Successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductCategoryRequest $request, ProductCategory $product_category)
    {
        $product_category->update($request->all());
        return redirect()->back()->withSuccess('Product Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $product_category)
    {
        $product_category->delete();
        return redirect()->back()->withSuccess('Product Category Removeds Successfully');
    }
}
