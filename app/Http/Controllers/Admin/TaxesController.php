<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\LinkUpEvent;
use App\Models\Tax;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxesController extends Controller
{
    /**
     * Display the tax dashboard.
     */
    public function dashboard()
    {
        // Get all ticket sales with event data
        $taxCollection = TicketSale::with('event')->whereHas('event')->get();
        
        // Calculate tax statistics by country
        $taxByCountry = $taxCollection->groupBy(function($sale) {
            return $sale->event->country ?? 'Unknown';
        })->map(function($sales, $country) {
            return [
                'country' => $country,
                'amount' => $sales->sum('event_tax'),
                'transactions' => $sales->count(),
            ];
        })->values()->sortByDesc('amount')->values()->all();
        
        // Calculate tax statistics by state (for US events)
        $taxByState = $taxCollection->filter(function($sale) {
            return $sale->event->country === 'United States';
        })->groupBy(function($sale) {
            return $sale->event->state ?? 'Unknown';
        })->map(function($sales, $state) {
            return [
                'state' => $state,
                'amount' => $sales->sum('event_tax'),
                'transactions' => $sales->count(),
                'country' => 'United States',
            ];
        })->values()->sortByDesc('amount')->values()->all();
        
        // Get total tax summary
        $totalTaxCollected = $taxCollection->sum('event_tax');
        $totalTransactions = $taxCollection->count();
        $uniqueEvents = $taxCollection->pluck('link_up_event_id')->unique()->count();
        
        // Get countries list for filter
        $countries = $taxCollection->pluck('event.country')->unique()->filter()->sort()->values()->all();
        
        return Inertia::render('admin/taxes/Dashboard', [
            'taxCollection' => $taxCollection,
            'taxByCountry' => $taxByCountry,
            'taxByState' => $taxByState,
            'totalTaxCollected' => $totalTaxCollected,
            'totalTransactions' => $totalTransactions,
            'uniqueEvents' => $uniqueEvents,
            'countries' => $countries,
        ]);
    }

    public function index(Request $request)
    {
        $taxes = Tax::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        $countries = Country::all();

        return Inertia::render('admin/taxes/Index', [
            'taxes' => $taxes,
            'countries' => $countries,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }


    public function create() {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'country' => 'required',
            'tax_type' => 'required',
            'country_label' => 'nullable',
            'tax' => 'required'
        ]);

        Tax::create($data);
        return redirect()->route('admin.taxes-index')->with('messages', ['title' => 'Tax Created successfully.']);
    }

    public function edit($id) {}

    public function update(Request $request, $id)
    {
        $validated =  $request->validate([
            'country' => 'nullable',
            'tax_type' => 'nullable',
            'tax' => 'nullable'
        ]);
        Tax::where('id', $id)->update($validated);
    }

    public function destroy($id)
    {
        $data = Tax::where('id', $id)->first();
        $data->delete();
    }

    public function show($id)
    {
        $tax = Tax::with('country')->findOrFail($id);
        
        return Inertia::render('admin/taxes/Show', [
            'tax' => $tax,
            'countries' => Country::all()
        ]);
    }

    public function taxSetting()
    {
        return Inertia::render('admin/taxes/TaxSetting');
    }

    public function taxRemittanceSetting()
    {
        return Inertia::render('admin/taxes/TaxRemittanceSetting');
    }

    public function taxRemittanceCenter()
    {
        return Inertia::render('admin/taxes/TaxRemittanceCenter');
    }
}
