<?php

namespace App\Services;

use App\Models\TaxApiSetting;
use App\Models\Tax;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TaxRateService
{
    protected string $apiBase;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiBase = 'https://api.sandbox.taxjar.com/v2';
        $this->apiKey  = null;
    }

    /**
     * Get the active API endpoint from database
     */
    protected function getActiveApiEndpoint(): ?string
    {
        try {
            $activeSetting = TaxApiSetting::where('is_active', true)->first();
            return $activeSetting?->end_point_url;
        } catch (\Throwable $e) {
            // Fallback to sandbox endpoint if database fails
            return 'https://api.sandbox.taxjar.com/v2';
        }
    }

    /**
     * Get the active API key from database
     */
    protected function getActiveApiKey(): ?string
    {
        try {
            $activeSetting = TaxApiSetting::where('is_active', true)->first();
            return $activeSetting?->api_key;
        } catch (\Throwable $e) {
            // Return null if database fails - no fallback to env
            return null;
        }
    }

    /**
     * Ensure API config is loaded (safe lazy-load with encryption errors handled)
     */
    protected function ensureConfigLoaded(): void
    {
        if ($this->apiKey !== null && $this->apiBase !== 'https://api.sandbox.taxjar.com/v2') {
            return;
        }
        try {
            $activeSetting = TaxApiSetting::where('is_active', true)->first();
            if ($activeSetting) {
                // Accessing may decrypt encrypted casts; wrap in try/catch
                $this->apiBase = $activeSetting->end_point_url ?: $this->apiBase;
                $this->apiKey  = $activeSetting->api_key ?: null;
            }
        } catch (\Throwable $e) {
            // Leave defaults; actual method will report configuration issue if needed
            $this->apiKey = $this->apiKey ?? null;
            $this->apiBase = $this->apiBase ?? 'https://api.sandbox.taxjar.com/v2';
        }
    }

    /**
     * Fetch tax rate from TaxJar API.
     *
     * @param array $params Supported keys: zip, city, state, country, street
     * @return array{success:bool, rate:float, data:array|null, status?:int, message?:string, query?:array}
     */
    public function getRate(array $params): array
    {
        // Lazy-load configuration to avoid decrypting during construction
        $this->ensureConfigLoaded();

        $country = $params['country'] ?? null;

        if (!$country) {
            return $this->errorResponse($params, 'Country is required', 400);
        }

        // The configured API is authoritative. The admin-managed country tax
        // table is used only when the API cannot provide a country/rate.
        $zip = $params['zip'] ?? null;
        if (empty($zip)) {
            return $this->getLocalTaxRateOrError($params, 'ZIP code is required for the configured tax API', 400);
        }

        if (empty($this->apiKey)) {
            return $this->getLocalTaxRateOrError($params, 'No active tax API key configured', 500);
        }

        try {
            $query = $params;
            unset($query['zip']);

            $resp = Http::withToken($this->apiKey)->get("{$this->apiBase}/rates/{$zip}", $query);

            if ($resp->successful()) {
                $data = $resp->json();
                $rateData = $data['rate'] ?? [];
                $rate = (float)($rateData['combined_rate'] ?? 0);
                $apiCountry = $rateData['country'] ?? $rateData['country_code'] ?? null;

                if ($rate > 0 && filled($apiCountry)) {
                    return [
                        'success' => true,
                        'rate' => $rate,
                        'data' => array_merge($data, [
                            'source' => 'tax_api',
                            'tax_type' => 'percentage',
                        ]),
                        'query' => $params,
                    ];
                }

                return $this->getLocalTaxRateOrError(
                    $params,
                    'The configured tax API returned no country tax rate',
                    404,
                    $data
                );
            }

            $respData = $resp->json();
            return $this->getLocalTaxRateOrError(
                $params,
                $respData['detail'] ?? 'Failed to fetch tax rate',
                $resp->status(),
                $respData
            );
        } catch (\Throwable $e) {
            return $this->getLocalTaxRateOrError($params, $e->getMessage(), 500);
        }
    }

    /**
     * Check if we have a local tax rate for the given country
     */

    protected function hasLocalTaxRate(string $country): bool
    {
        return $this->findLocalTaxRate($country) !== null;
    }

    protected function findLocalTaxRate(string $country): ?Tax
    {
        $country = strtolower(trim($country));

        return Tax::query()
            ->whereRaw('LOWER(country) = ?', [$country])
            ->orWhereRaw('LOWER(country_label) = ?', [$country])
            ->first();
    }

    /**
     * Get tax rate from local database
     */
    protected function getLocalTaxRate(array $params): array
    {
        $country = $params['country'];
        $taxRecord = $this->findLocalTaxRate($country);

        if (!$taxRecord) {
            return $this->errorResponse($params, "No tax rate found for country: {$country}", 404);
        }

        $rate = (float)$taxRecord->tax;

        return [
            'success' => true,
            'rate'    => $rate,
            'data'    => [
                'source'        => 'local_database',
                'country'       => $taxRecord->country,
                'country_label' => $taxRecord->country_label,
                'tax_type'      => $taxRecord->tax_type,
                'tax'           => $taxRecord->tax,
            ],
            'query'   => $params,
        ];
    }

    protected function getLocalTaxRateOrError(
        array $params,
        string $message,
        int $status,
        ?array $data = null
    ): array {
        $local = $this->getLocalTaxRate($params);

        return $local['success']
            ? $local
            : $this->errorResponse($params, $message, $status, $data);
    }

    /**
     * Get tax rules for a state (combines TaxJar API with local tax rules)
     *
     * @param string $state Two-letter state code
     * @param string $zip ZIP code (optional but recommended for TaxJar)
     * @param string $city City name (optional)
     * @param string $country Country code (default: US)
     * @return array{success:bool, rate:float, tax_fees:bool, no_state_sales_tax:bool, data:array|null}
     */
    public function getStateTaxRules(string $state, string $zip = null, string $city = null, string $country = 'US'): array
    {
        // Ensure configuration is loaded (for potential API call below)
        $this->ensureConfigLoaded();
        // Get local tax rules from database
        $taxRulesSetting = DB::table('settings')->where('key', 'tax_rules')->first();
        $taxRules = $taxRulesSetting ? json_decode($taxRulesSetting->tax_rules, true) : null;
        
        // Default tax rules
        $defaultRules = [
            'tax_fees' => false,
            'no_state_sales_tax' => false,
            'tax_ticket' => true
        ];
        
        $stateRules = $defaultRules;
        if ($country === 'US' && $taxRules && isset($taxRules['US'][$state])) {
            $stateRules = $taxRules['US'][$state];
        }
        
        // If no state sales tax, return 0% rate
        if ($stateRules['no_state_sales_tax'] ?? false) {
            return [
                'success' => true,
                'rate' => 0.0,
                'tax_fees' => false,
                'no_state_sales_tax' => true,
                'data' => array_merge($stateRules, ['source' => 'local_rules_no_tax'])
            ];
        }

        // Check for base_rate in local rules (User override)
        if (isset($stateRules['base_rate'])) {
             return [
                'success' => true,
                'rate' => (float)$stateRules['base_rate'],
                'tax_fees' => $stateRules['tax_fees'] ?? false,
                'no_state_sales_tax' => false,
                'data' => array_merge($stateRules, ['source' => 'local_rules_base_rate'])
            ];
        }

        // Check for state_tax + county_tax (User override)
        if (isset($stateRules['state_tax']) || isset($stateRules['county_tax'])) {
            $rate = (float)($stateRules['state_tax'] ?? 0) + (float)($stateRules['county_tax'] ?? 0);
            return [
                'success' => true,
                'rate' => $rate,
                'tax_fees' => $stateRules['tax_fees'] ?? false,
                'no_state_sales_tax' => false,
                'data' => array_merge($stateRules, ['source' => 'local_rules_calculated'])
            ];
        }
        
        // Try to get real-time rate from TaxJar
        if ($zip && $this->apiKey) {
            $params = [
                'zip' => $zip,
                'state' => $state,
                'country' => $country
            ];
            
            if ($city) {
                $params['city'] = $city;
            }
            
            $taxJarResponse = $this->getRate($params);
            
            if ($taxJarResponse['success']) {
                return [
                    'success' => true,
                    'rate' => $taxJarResponse['rate'],
                    'tax_fees' => $stateRules['tax_fees'] ?? false,
                    'no_state_sales_tax' => false,
                    'data' => array_merge($stateRules, $taxJarResponse['data'] ?? [], ['source' => 'taxjar_api'])
                ];
            }
        }
        
        // Fallback to default rate (could be stored in event or use a reasonable default)
        $fallbackRate = $this->getFallbackRate($state);
        
        return [
            'success' => true,
            'rate' => $fallbackRate,
            'tax_fees' => $stateRules['tax_fees'] ?? false,
            'no_state_sales_tax' => false,
            'data' => array_merge($stateRules, ['source' => 'fallback_rate'])
        ];
    }
    
    /**
     * Get fallback tax rate for a state when API fails
     */
    protected function getFallbackRate(string $state): float
    {
        // Common fallback rates by state (you can expand this)
        $fallbackRates = [
            'AL' => 0.04,  // Alabama ~4%
            'AK' => 0.00,  // Alaska - no state tax
            'AZ' => 0.056, // Arizona ~5.6%
            'AR' => 0.065, // Arkansas ~6.5%
            'CA' => 0.0725, // California ~7.25%
            'CO' => 0.072, // Colorado ~7.2%
            'CT' => 0.0635, // Connecticut ~6.35%
            'DE' => 0.00,  // Delaware - no state tax
            'FL' => 0.07,  // Florida 6% + avg local 1%
            'GA' => 0.04,  // Georgia ~4%
            'HI' => 0.04,  // Hawaii ~4%
            'IA' => 0.06,  // Iowa ~6%
            'ID' => 0.06,  // Idaho ~6%
            'IL' => 0.0625, // Illinois ~6.25%
            'IN' => 0.07,  // Indiana ~7%
            'KS' => 0.065, // Kansas ~6.5%
            'KY' => 0.06,  // Kentucky ~6%
            'LA' => 0.0445, // Louisiana ~4.45%
            'MA' => 0.0625, // Massachusetts ~6.25%
            'MD' => 0.06,  // Maryland ~6%
            'ME' => 0.055, // Maine ~5.5%
            'MI' => 0.06,  // Michigan ~6%
            'MN' => 0.0688, // Minnesota ~6.875%
            'MO' => 0.0423, // Missouri ~4.225%
            'MS' => 0.07,  // Mississippi ~7%
            'MT' => 0.00,  // Montana - no state tax
            'NC' => 0.0475, // North Carolina ~4.75%
            'ND' => 0.05,  // North Dakota ~5%
            'NE' => 0.055, // Nebraska ~5.5%
            'NH' => 0.00,  // New Hampshire - no state tax
            'NJ' => 0.066, // New Jersey ~6.625%
            'NM' => 0.0513, // New Mexico ~5.125%
            'NV' => 0.0685, // Nevada ~6.85%
            'NY' => 0.08,  // New York ~8%
            'OH' => 0.0575, // Ohio ~5.75%
            'OK' => 0.045, // Oklahoma ~4.5%
            'OR' => 0.00,  // Oregon - no state tax
            'PA' => 0.06,  // Pennsylvania ~6%
            'RI' => 0.07,  // Rhode Island ~7%
            'SC' => 0.06,  // South Carolina ~6%
            'SD' => 0.045, // South Dakota ~4.5%
            'TN' => 0.07,  // Tennessee ~7%
            'TX' => 0.0625, // Texas ~6.25%
            'UT' => 0.0595, // Utah ~5.95%
            'VA' => 0.053, // Virginia ~5.3%
            'VT' => 0.06,  // Vermont ~6%
            'WA' => 0.065, // Washington ~6.5%
            'WI' => 0.05,  // Wisconsin ~5%
            'WV' => 0.06,  // West Virginia ~6%
            'WY' => 0.04,  // Wyoming ~4%
            'DC' => 0.06,  // District of Columbia ~6%
        ];
        
        return $fallbackRates[strtoupper($state)] ?? 0.05; // Default 5% if unknown
    }

    protected function errorResponse(array $params, string $message, int $status = 400, array $data = null): array
    {
        return [
            'success' => false,
            'rate'    => 0.0,
            'data'    => $data,
            'status'  => $status,
            'message' => $message,
            'query'   => $params,
        ];
    }
}
