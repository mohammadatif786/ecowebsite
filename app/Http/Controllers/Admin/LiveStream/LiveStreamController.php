<?php

namespace App\Http\Controllers\Admin\LiveStream;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\LiveStreamGumlet;
use App\Models\LiveStreamCategories;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiveStreamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $liveStreams = LiveStream::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/liveStream/liveStreams/Index', [
            'liveStreams' => $liveStreams,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function dashboard()
    {
        $currentBroadCasts = LiveStreamGumlet::where('status','live')->get();
        return Inertia::render('admin/liveStream/dashboard/Dashboard', [
            'currentBroadCasts' => $currentBroadCasts,
        ]);
    }

    public function feeAnalytics()
    {
        return Inertia::render('admin/liveStream/feeAnalytics');
    }

    public function liveAnalytics()
    {
        return Inertia::render('admin/liveStream/liveAnalytics');
    }

    public function moderation()
    {
        return Inertia::render('admin/liveStream/moderation');
    }

    public function categories()
    {
        $categories = LiveStreamCategories::query()
            ->orderBy('category')
            ->get();

        return Inertia::render('admin/liveStream/categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
        ]);

        LiveStreamCategories::firstOrCreate([
            'category' => trim($validated['category']),
        ]);

        return redirect()->back()->withSuccess('Category added successfully');
    }

    public function updateCategory(Request $request, LiveStreamCategories $category)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255|unique:live_stream_categories,category,' . $category->id,
        ]);

        $category->update([
            'category' => trim($validated['category']),
        ]);

        return redirect()->route('admin.live-streams.categories')->withSuccess('Category updated successfully');
    }

    public function destroyCategory(LiveStreamCategories $category)
    {
        $category->delete();

        return redirect()->back()->withSuccess('Category deleted successfully');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
