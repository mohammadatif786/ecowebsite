<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Jobs\SendNewProductToUsersJob;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginator = Product::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/shops/products/Index', [
            'paginator' => $paginator,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }


    public function create()
    {
        $product_categories = ProductCategory::select('id as value', 'name as label')->whereStatus(true)->get();
        return Inertia::render('admin/shops/products/Create', [
            'product_categories' => $product_categories
        ]);
    }

    public function edit(Product $product)
    {
        $product_categories = ProductCategory::select('id as value', 'name as label')->whereStatus(true)->get();
        return Inertia::render('admin/shops/products/Edit', [
            'product_categories' => $product_categories,
            'product' => $product
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $data = $request->all();
        $data['listing_type'] = 'Administrative';
        $data['seller_owner'] = null;
        $product = Product::create($data);
        $this->saveImage($request, $product);

        if ($product?->id) {
            SendNewProductToUsersJob::dispatch((int) $product->id);
        }

        return redirect()->route('admin.products.index')->withSuccess('Product Created Successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->all();
        $product->update($data);
        $this->saveImage($request, $product);
        return redirect()->route('admin.products.index')->withSuccess('Product Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->withSuccess('Product Deleted Successfully');
    }

    /**
     * Show the specific Resource
     */
    public function show(Product $product)
    {
        $product->load('category');
        return Inertia::render('admin/shops/products/View', [
            'product' => $product
        ]);
    }

    public function getImages(Product $product)
    {
        return response()->json(['images' => $product->images]);
    }


    public function saveExtraImage(Request $request, Product $product)
    {
        $request->validate([
            'image_file' => 'required|image'
        ]);
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('images/productImages', 'public');
            $url = Storage::url($path);
            $images = $product->images;
            if ($images) {
                array_push($images, $url);
            } else {
                $images = [$url];
            }
            $product->images = $images;
            $product->save();
        }
    }

    public function removeExtraImage(Request $request, Product $product)
    {
        $product->images = $request->get('images');
        $product->save();
    }

    private function saveImage(Request $request, Product $product)
    {
        if ($request->hasFile('cover_image_file')) {
            $path = $request->file('cover_image_file')->store('images/productImages', 'public');
            $url = Storage::url($path);
            $product->cover_image = $url;
            $product->save();
        }

        // Save multiple images
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('images/productImages', 'public');
                $paths[] = Storage::url($path);
            }

            // Store as comma-separated string
            $product->images = $paths;
            $product->save();
        }
    }
}
