<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventCategoryRequest;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allcategories = EventCategory::query()
            ->withCount('linkupEvents')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return Inertia::render('admin/categories/Index', [
            'allcategories' => $allcategories,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventCategoryRequest $request)
    {
        $data = $request->all();
        $category = EventCategory::create($data);
        $this->saveImage($request, $category);
        return redirect()->back()->withSuccess('Event Category Saved Successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventCategoryRequest $request, EventCategory $category)
    {
        $data = $request->all();
        $category->update($data);
        $this->saveImage($request, $category);
        return redirect()->back()->withSuccess('Event Category Saved Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventCategory $category)
    {
        $category->delete();
        return redirect()->back()->withSuccess('Event Category Saved Successfully');
    }

    private function saveImage(Request $request, EventCategory $category){
        if ($request->hasFile('category_image_file')) {
            $path = $request->file('category_image_file')->store('images/eventCategories', 'public');
            $url = Storage::url($path);
            $category->image_object = $url;
            $category->save();
        }
    }
}
