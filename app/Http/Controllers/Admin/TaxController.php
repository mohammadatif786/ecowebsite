<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\TaxRemittance;
use App\Models\TaxRemittanceCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TaxRateService;
class TaxController extends Controller
{
    protected $taxRateService;

    public function __construct(TaxRateService $taxRateService)
    {
        $this->taxRateService = $taxRateService;
    }

    public function getLocalTaxCountries()
    {
        $countries = Tax::pluck('country')->filter()->unique()->values();
        Log::info('Local tax countries fetched:', ['countries' => $countries]);
        return response()->json(['countries' => $countries]);
    }

    public function getApiSupportedCountries()
    {
        try {
            $countries = config('api_countries.taxjar_supported', []);
            
            return response()->json([
                'success' => true,
                'countries' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch API supported countries'
            ], 500);
        }
    }

    public function getTaxRate(Request $request, TaxRateService $taxRateService)
    {
        $validated = $request->validate([
            'zip' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'street' => 'nullable|string',
        ]);

        $result = $taxRateService->getRate($validated);
        return response()->json($result, $result['status'] ?? 200);
    }


    public function remittanceIndex()
    {
        $remittances = TaxRemittance::all();
        $remittances->each->makeVisible(['api_key', 'api_secret']);
        return response()->json(['data' => $remittances]);
    }

    public function remittanceStore(Request $request)
    {
      
        $requestData = $request->all();
        
        if (empty(trim($requestData['api_url'] ?? ''))) {
            $requestData['api_url'] = null;
        }
      
        if (empty(trim($requestData['api_headers'] ?? ''))) {
            $requestData['api_headers'] = '{}';
        }
        
        $validated = $request->validate([
            'agency_name' => 'required|string|max:255',
            'jurisdiction' => 'required|string|max:255',
            'payment_method' => 'required|in:ach,wire,eftps,state_api',
            'routing_number' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:100',
            'api_url' => 'nullable|url|max:255',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string',
            'api_headers' => 'nullable|json',
            'is_active' => 'sometimes|boolean',
        ]);
        
        if (isset($requestData['api_url'])) {
            $validated['api_url'] = $requestData['api_url'];
        }
        if (isset($requestData['api_headers'])) {
            $validated['api_headers'] = $requestData['api_headers'];
        }
        
        $remittance =TaxRemittance::create($validated);
        $remittance->makeVisible(['api_key', 'api_secret']);
        return response()->json(['data' => $remittance], 201);
    }

    public function remittanceShow(TaxRemittance $taxRemittance)
    {
        $taxRemittance->makeVisible(['api_key', 'api_secret']);
        return response()->json(['data' => $taxRemittance]);
    }

  public function remittanceUpdate(Request $request, TaxRemittance $taxRemittance)
{
 
    $validated = $request->validate([
        'agency_name'    => 'sometimes|required|string|max:255',
        'jurisdiction'   => 'sometimes|required|string|max:255',
        'payment_method' => 'sometimes|required|in:ach,wire,eftps,state_api',
        'routing_number' => 'nullable|string|max:50',
        'account_number' => 'nullable|string|max:100',
        'api_url'        => 'nullable|url|max:255',
        'api_key'        => 'nullable|string|max:255',
        'api_secret'     => 'nullable|string',
        'api_headers'    => 'nullable|json',
        'is_active'      => 'sometimes|boolean',
    ]);

    if (array_key_exists('api_url', $validated) && empty($validated['api_url'])) {
        $validated['api_url'] = null;
    }
    if (array_key_exists('api_headers', $validated) && empty($validated['api_headers'])) {
        $validated['api_headers'] = '{}';
    }
    try {
        $taxRemittance->fill($validated);
        $taxRemittance->save();

    } catch (\Exception $e) {
        Log::error("Remittance Update Error: " . $e->getMessage());
        return response()->json(['message' => 'Database update failed: ' . $e->getMessage()], 500);
    }

    $taxRemittance->makeVisible(['api_key', 'api_secret']);
    
    return response()->json([
        'message' => 'Updated successfully',
        'data' => $taxRemittance
    ]);
}

public function remittanceDestroy($id)
{
    $taxRemittance = TaxRemittance::findOrFail($id);
    
    $taxRemittance->delete();
    
    return response()->json(['message' => 'Deleted successfully']);
}

// --- Tax Remittance Center CRUD ---
public function remittanceCenterIndex()
{
    $centers = TaxRemittanceCenter::all();
    // Don't make api_key and api_secret visible - keep them hidden
    return response()->json(['data' => $centers]);
}

public function remittanceCenterStore(Request $request)
{
    $validated = $request->validate([
        'agency_name' => 'required|string|max:255',
        'jurisdiction' => 'required|string|max:255',
        'payment_method' => 'required|in:ach,wire,eftps,state_api',
        'routing_number' => 'nullable|string|max:50',
        'account_number' => 'nullable|string|max:100',
        'api_base' => 'nullable|url|max:255',
        'api_key' => 'nullable|string|max:255',
        'api_secret' => 'nullable|string',
        'is_active' => 'sometimes|boolean',
    ]);
    
    $center = TaxRemittanceCenter::create($validated);
    // Don't make api_key and api_secret visible - keep them hidden
    return response()->json(['data' => $center], 201);
}

public function remittanceCenterShow(TaxRemittanceCenter $taxRemittanceCenter)
{
    // Don't make api_key and api_secret visible - keep them hidden
    return response()->json(['data' => $taxRemittanceCenter]);
}


public function remittanceCenterUpdate(Request $request, $id) 
{
    $taxRemittanceCenter =TaxRemittanceCenter::findOrFail($id);

    $validated = $request->validate([
        'agency_name'    => 'sometimes|required|string|max:255',
        'jurisdiction'   => 'sometimes|required|string|max:255',
        'payment_method' => 'sometimes|required|in:ach,wire,eftps,state_api',
        'routing_number' => 'nullable|string|max:50',
        'account_number' => 'nullable|string|max:100',
        'api_base'       => 'nullable|url|max:255',
        'api_key'        => 'nullable|string|max:255',
        'api_secret'     => 'nullable|string',
        'is_active'      => 'sometimes|boolean',
    ]);
    
    $taxRemittanceCenter->fill($validated);
    $taxRemittanceCenter->save();
    // Don't make api_key and api_secret visible - keep them hidden
    
    return response()->json(['data' => $taxRemittanceCenter]);
}

public function remittanceCenterDestroy($id) 
{
    $center = TaxRemittanceCenter::findOrFail($id);
    
    $center->delete();
    
    return response()->json(['message' => 'Deleted successfully']);
}
}

