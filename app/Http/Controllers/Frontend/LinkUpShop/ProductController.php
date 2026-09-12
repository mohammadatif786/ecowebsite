<?php

namespace App\Http\Controllers\Frontend\LinkUpShop;

use App\Actions\CreateMerchantAction;
use App\Actions\CreateProductAction;
use App\DTOs\MerchantData;
use App\DTOs\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchantRquest;
use App\Http\Requests\StoreProductRequest;
use App\Models\Merchants;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Repositories\ShopFeeRepository;
use App\Jobs\SendNewProductToUsersJob;
use App\Models\User;

class ProductController extends Controller
{
    public function index(Request $request, ShopFeeRepository $feeRepo)
    {
        $query = Product::with(['category', 'merchant'])->where('status', 1);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category && $request->category !== '') {
            $query->where('product_category_id', $request->category);
        }

        if ($request->has('country') && $request->country && $request->country !== '') {
            $query->whereHas('merchant', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'priceAsc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'priceDesc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'nameAsc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'nameDesc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'rating':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->get();
        $categories = ProductCategory::where('status', 1)->get();
        $merchants = Merchants::where('user_id', Auth::id())->where('is_active', 1)->get();
        $userProducts = Product::withCount('orderItems')->where('user_id', Auth::id())->get();

        $countriesResponse = \Illuminate\Support\Facades\Http::get('https://countriesnow.space/api/v0.1/countries');
        $countriesData = $countriesResponse->json();
        $countriesArray = $countriesData['data'] ?? $countriesData;
        $countries = collect($countriesArray)->map(function ($country) {
            return [
                'name' => $country['name'] ?? $country['country'],
                'code' => $country['iso2']
            ];
        })->keyBy('code')->sortKeys()->values();

        $processFee = \App\Models\EventFeeSetting::first();
        $favoriteSellerIds = Auth::user()->favoriteSellers()->pluck('seller_id')->toArray();


        return Inertia::render('User/LinkUpShop/Products/Index', [
            'products' => $products,
            'userProducts' => $userProducts,
            'category' => $categories,
            'merchants' => $merchants,
            'countries' => $countries,
            'shopFee' => $feeRepo->getFee(),
            'processFee' => $processFee,
            'favoriteSellerIds' => $favoriteSellerIds,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'country' => $request->country,
                'sort' => $request->sort,
            ]
        ]);
    }
    public function search(Request $request)
    {
        $query = Product::where('status', 1)->with(['category', 'merchant']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category && $request->category !== '') {
            $query->where('product_category_id', $request->category);
        }

        if ($request->has('country') && $request->country && $request->country !== '') {
            $query->whereHas('merchant', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'priceAsc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'priceDesc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'nameAsc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'nameDesc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        $products = $query->get();

        return response()->json([
            'products' => $products
        ]);
    }

    public function store(StoreProductRequest $request, CreateProductAction $action)
    {

        $data = new ProductData(
            name: $request->name,
            description: $request->description,
            price: (float) $request->price,
            qty: $request->stock ?? 1,
            productCategoryId: $request->category_id,
            listingType: $request->listing_type,
            sellerOwner: $request->seller_owner,
            imageUrl: $request->image_url,
            coverImage: null,
            images: [],
            commMode: $request->input('commMode', 'pct'),
            commission: (float) $request->input('commission', 10),
            commFlat: (float) $request->input('commFlat', 0),
            collectTax: $request->boolean('collect_tax'),
            userId: Auth::id()
        );

        $product = $action->execute(
            $data,
            $request->file('cover_image'),
            $request->file('images')
        );

        if ($product?->id) {
            SendNewProductToUsersJob::dispatch((int) $product->id);
        }

        return back()->with('success', 'Product created successfully.');
    }

    public function update(StoreProductRequest $request, Product $product, \App\Repositories\ProductRepository $repository, \App\Services\FileUploadService $fileUploadService)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $data = new ProductData(
            name: $request->name,
            description: $request->description,
            price: (float) $request->price,
            qty: $request->stock ?? 1,
            productCategoryId: $request->category_id,
            listingType: $request->listing_type,
            sellerOwner: $request->seller_owner,
            imageUrl: $request->image_url,
            coverImage: null,
            images: [],
            commMode: $request->input('commMode', 'pct'),
            commission: (float) $request->input('commission', 10),
            commFlat: (float) $request->input('commFlat', 0),
            collectTax: $request->boolean('collect_tax')
        );

        // Handle cover image upload if provided
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $path = $file->store('products', 'public');
            $data->coverImage = '/storage/' . $path;
        }

        if ($request->hasFile('images')) {
            $data->images = array_merge(
                $product->images ?? [],
                $fileUploadService->uploadMultiple($request->file('images'), 'products'),
            );
        }

        $repository->update($product, $data);

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

    public function saveStore(StoreMerchantRquest $request, CreateMerchantAction $action, \App\Services\FileUploadService $fileUploadService)
    {
        $businessLicenseFile = $request->hasFile('business_license_file')
            ? $fileUploadService->uploadSingle($request->file('business_license_file'), 'merchant-documents')
            : null;
        $vatCertificateFile = $request->hasFile('vat_certificate_file')
            ? $fileUploadService->uploadSingle($request->file('vat_certificate_file'), 'merchant-documents')
            : null;

        $data = new MerchantData(
            country: $request->country,
            state: $request->state,
            city: $request->city,
            name: $request->st_name,
            ownerName: $request->st_owner,
            pickupLocations: $request->st_pickups,
            merchantType: $request->merchant_type,
            userId: Auth::id(),
            offersPickup: $request->boolean('offers_pickup'),
            offersDelivery: $request->boolean('offers_delivery'),
            businessLicense: $request->input('business_license'),
            vatCertificate: $request->input('vat_certificate'),
            businessLicenseFile: $businessLicenseFile,
            vatCertificateFile: $vatCertificateFile,
        );

        $action->execute($data);

        return back()->with(
            'success',
            $request->merchant_type === 'store'
                ? 'Store created successfully'
                : 'Group created successfully'
        );
    }
    public function updateStore(StoreMerchantRquest $request, $id, \App\Services\FileUploadService $fileUploadService)
    {
        $merchant = Merchants::findOrFail($id);

        if ($merchant->user_id !== Auth::id()) {
            abort(403);
        }

        $merchant->update([
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'name' => $request->st_name,
            'owner_name' => $request->st_owner,
            'pickup_locations' => $request->st_pickups,
            'merchant_type' => $request->merchant_type,
            'offers_pickup' => $request->boolean('offers_pickup'),
            'offers_delivery' => $request->boolean('offers_delivery'),
            'business_license' => $request->input('business_license'),
            'vat_certificate' => $request->input('vat_certificate'),
            'business_license_file' => $request->hasFile('business_license_file')
                ? $fileUploadService->uploadSingle($request->file('business_license_file'), 'merchant-documents')
                : $merchant->business_license_file,
            'vat_certificate_file' => $request->hasFile('vat_certificate_file')
                ? $fileUploadService->uploadSingle($request->file('vat_certificate_file'), 'merchant-documents')
                : $merchant->vat_certificate_file,
        ]);

        return back()->with(
            'success',
            $request->merchant_type === 'store'
                ? 'Store updated successfully'
                : 'Group updated successfully'
        );
    }
    public function destroyStore($id)
    {
        $merchant = Merchants::findOrFail($id);

        if ($merchant->user_id !== Auth::id()) {
            abort(403);
        }

        $merchant->delete();

        return back()->with('success', 'Store deleted successfully');
    }

    public function toggleFavorite(User $seller_id)
    {
        $user = Auth::user();

        if ($user->id === $seller_id->id) {
            return response()->json(['message' => 'You cannot follow yourself.', 'status' => false]);
        }

        $callback = $user->favoriteSellers()->toggle($seller_id->id);

        $attached = !empty($callback['attached']);

        return response()->json([
            'message' => $attached ? 'Seller followed' : 'Seller unfollowed',
            'status'  => $attached
        ]);
    }
}
