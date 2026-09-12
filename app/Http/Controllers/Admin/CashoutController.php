<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashOut;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cashouts = CashOut::query()->with('user')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/cashout/Index', [
            'cashouts' => $cashouts,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organizer = CashOut::with('user')->findOrFail($id);
        return Inertia::render('admin/cashout/Show', [
            'organizer' => $organizer,
        ]);
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
        //         //✅ What is a single-file component (.vue file)?
        // ✅ How do you pass props between components?
        // ✅ How do you lift state up or use a global store (Pinia or Vuex)?
        // ✅ What are slots (default slots, named slots, scoped slots)?
        // ✅ How do you handle conditional rendering?
    }
}
