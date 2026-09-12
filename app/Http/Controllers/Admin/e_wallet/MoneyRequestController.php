<?php

namespace App\Http\Controllers\Admin\e_wallet;

use App\Models\User;
use Inertia\Inertia;
use App\Models\MoneyRequest;
use App\Models\RequestMoney;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class MoneyRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('walletBalances')->get();
        // $moneyrequests = MoneyRequest::query()
        //     ->filter($request->only('search'))->with('user')
        //     ->orderBy('created_at', 'DESC')
        //     ->paginate(10);
        dd($users);
        return Inertia::render('admin/e_wallet/moneyRequest', [
            'allTransaction' => $allTransaction,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function changeStatus(MoneyRequest $moneyrequest, Request $request)
    {

        $moneyrequest->update([
            'status' => (bool) $request->status
        ]);
        return response()->json(['message' => 'Status changed successfully']);
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
