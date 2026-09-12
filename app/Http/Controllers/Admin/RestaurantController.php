<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allrestaurant = Restaurant::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return Inertia::render('admin/restaurant/Index', [
            'allrestaurant' => $allrestaurant,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/restaurant/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'video' => 'nullable|file|mimes:mp4,avi,mov|max:20480',
            'www' => 'nullable|url|max:255',
            'cost' => 'required|numeric|min:0',
            'paid' => 'nullable',
            'is_paid' => 'required|boolean',
            'status' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $tempName = uniqid('ad_img_') . '.' . $image->getClientOriginalExtension();
            $data['image'] = $image->storeAs('restaurant/images', $tempName, 'public');
        }
        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('restaurant/videos', 'public');
        }
        Restaurant::create($data);
        return redirect()->route('admin.restaurant.index')->with('message', 'Restaurant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return Inertia::render('admin/restaurant/Show', [
            'restaurant' => $restaurant,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return Inertia::render('admin/restaurant/Edit', [
            'restaurant' => $restaurant,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable',
            'video' => 'nullable',
            'www' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'paid' => 'nullable',
            'is_paid' => 'nullable|boolean',
            'status' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $tempName = uniqid('ad_img_') . '.' . $image->getClientOriginalExtension();
            $data['image'] = $image->storeAs('restaurant/images', $tempName, 'public');
        }
        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('restaurant/videos', 'public');
        }
        $restaurant->update($data);
        return redirect()->route('admin.restaurant.index')->with('message', 'Restaurant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        // Delete image if exists
        if ($restaurant->image && Storage::disk('public')->exists($restaurant->image)) {
            Storage::disk('public')->delete($restaurant->image);
        }
        // Delete video if exists
        if ($restaurant->video && Storage::disk('public')->exists($restaurant->video)) {
            Storage::disk('public')->delete($restaurant->video);
        }
        $restaurant->delete();
        return redirect()->route('admin.restaurant.index')->with('message', 'Restaurant deleted successfully.');
    }
}
