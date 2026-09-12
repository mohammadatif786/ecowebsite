<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Swipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SwipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $swipes = Swipe::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/swipes/SwipesPage');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/swipes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $swipe =  Swipe::create([
            'title' => $request->title,
            'designation' => $request->designation,
            'description' => $request->description,
        ]);

        $this->saveImages($request, $swipe);

        return back()->withSuccess("Created");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Swipe $swipe)
    {
        return Inertia::render('admin/swipes/Edit', [
            'swipe' => $swipe,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Swipe $swipe)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $swipe->update([
            'title' => $request->title,
            'designation' => $request->designation,
            'description' => $request->description,
        ]);

        $this->saveImages($request, $swipe);

        return back()->withSuccess("Updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Swipe $swipe)
    {
        if ($swipe->image) {
            Storage::disk('public')->delete($swipe->image);
        }
        
        $swipe->delete();

        return back()->withSuccess("Deleted");
    }

    private function saveImages($request, $swipe)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/swipes', 'public');
            if ($swipe->image) {
                Storage::disk('public')->delete($swipe->image);
            }
            $swipe->image = $path;
            $swipe->save();
        }
    }
}
