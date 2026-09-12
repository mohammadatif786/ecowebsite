<?php

namespace App\Http\Controllers\Admin\LiveStream;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $gifts = Gift::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/liveStream/gifts/Index', [
            'gifts' => $gifts,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
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
        $data = $request->validate([
            'name' => 'required',
            'category' => 'required',
            'coins' => 'required',
            'file_object' => 'required'
        ]);
        // dd($data);
        if ($data['file_object']) {
            $path = $request->file('file_object')->store('images/gifts', 'public');
            $data['file_object'] = $path;
        }
        Gift::create($data);
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
    public function update(Request $request, Gift $gift)
    {
        $data = $request->validate([
            'name' => 'required',
            'category' => 'required',
            'coins' => 'required',
            'file_object' => 'required',
        ]);
        if (file_exists($request->file_object)) {
            $path = $request->file('file_object')->store('images/gifts', 'public');
            $data['file_object'] = $path;
        } else {
            unset($data['file_object']);
        }

        $gift->update($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gift $gift)
    {
        $gift->delete();
        return redirect()->back()->withSuccess('Deleted Successfully');
    }
}
