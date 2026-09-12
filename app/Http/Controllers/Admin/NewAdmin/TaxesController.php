<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Order;
use App\Models\Tax;
use App\Models\TaxApiSetting;
use App\Models\TaxRemittanceCenter;
use App\Models\TicketSale;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TaxesController extends Controller
{
    private function render(string $component, GetAdminOverviewDataAction $action, array $props = []): Response
    {
        $data = $action->execute()->toArray();

        return Inertia::render($component, array_merge([
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ], $props));
    }

    public function management(Request $request, GetAdminOverviewDataAction $action): Response
    {
        $countries = Country::query()
            ->select('name', 'code', 'subregion')
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country) => [
                'name' => $country->name,
                'code' => strtoupper($country->code ?: ''),
                'subregion' => $country->subregion ?: 'International',
            ])
            ->values();

        $countriesByCode = $countries->keyBy(fn (array $country) => strtoupper($country['code']));
        $countriesByName = $countries->keyBy(fn (array $country) => strtolower($country['name']));

        $taxes = Tax::query()
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('country', 'like', "%{$search}%")
                        ->orWhere('country_label', 'like', "%{$search}%")
                        ->orWhere('tax_type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->map(function (Tax $tax) use ($countriesByCode, $countriesByName) {
                $storedCountry = (string) $tax->country;
                $countryFromCode = $countriesByCode->get(strtoupper($storedCountry));
                $countryFromName = $countriesByName->get(strtolower($tax->country_label ?: $storedCountry));
                $country = $countryFromCode ?: $countryFromName;
                $countryLabel = $tax->country_label ?: ($country['name'] ?? $storedCountry);

                return [
                    'id' => $tax->id,
                    'country' => $this->resolveCountryCode($countryLabel, $country['code'] ?? $storedCountry),
                    'country_label' => $countryLabel,
                    'tax_type' => $tax->tax_type,
                    'tax' => (float) $tax->tax,
                ];
            })
            ->values();

        return $this->render('admin/taxes/TaxManagement', $action, [
            'taxes' => $taxes,
            'countries' => $countries,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Tax::create($this->validatedTaxData($request));

        return redirect()
            ->route('admin.finance.taxes.management')
            ->with('message', 'Tax created successfully.');
    }

    public function update(Request $request, Tax $tax): RedirectResponse
    {
        $tax->update($this->validatedTaxData($request));

        return redirect()
            ->route('admin.finance.taxes.management')
            ->with('message', 'Tax updated successfully.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $tax->delete();

        return redirect()
            ->route('admin.finance.taxes.management')
            ->with('message', 'Tax deleted successfully.');
    }

    public function dashboard(GetAdminOverviewDataAction $action): Response
    {
        $taxRules = Tax::query()
            ->latest()
            ->get()
            ->map(fn (Tax $tax) => [
                'country' => $tax->country_label ?: $tax->country,
                'code' => $this->resolveCountryCode($tax->country_label ?: $tax->country, $tax->country),
                'tax_type' => $tax->tax_type,
                'rate' => (float) $tax->tax,
            ])
            ->values();

        $taxRateByCountry = $taxRules->keyBy(fn (array $tax) => strtolower($tax['country']));

        $ticketSales = TicketSale::query()
            ->with(['event.user', 'event.organizer'])
            ->whereHas('event')
            ->get();

        $eventRows = $ticketSales
            ->groupBy('link_up_event_id')
            ->map(function ($sales) use ($taxRateByCountry) {
                $event = $sales->first()->event;
                $country = $event?->country ?: 'Unknown';
                $gross = (float) $sales->sum(fn (TicketSale $sale) => (float) $sale->total);
                $taxCollected = (float) $sales->sum(fn (TicketSale $sale) => (float) $sale->event_tax);
                $rule = $taxRateByCountry->get(strtolower($country));

                return [
                    'event' => $event?->title ?: 'Untitled Event',
                    'organizer' => $event?->organizer_name ?: ($event?->organizer?->organizer_name ?: ($event?->user?->name ?: 'Unknown')),
                    'country' => $country,
                    'gross' => round($gross, 2),
                    'rate' => $gross > 0 ? round(($taxCollected / $gross) * 100, 2) : (float) ($rule['rate'] ?? 0),
                    'tax_collected' => round($taxCollected, 2),
                    'transactions' => $sales->count(),
                    'status' => $taxCollected > 0 ? 'Collected' : 'Pending',
                ];
            })
            ->sortByDesc('tax_collected')
            ->values();

        $taxByCountry = $ticketSales
            ->groupBy(fn (TicketSale $sale) => $sale->event?->country ?: 'Unknown')
            ->map(fn ($sales, string $country) => [
                'country' => $country,
                'amount' => round((float) $sales->sum(fn (TicketSale $sale) => (float) $sale->event_tax), 2),
                'transactions' => $sales->count(),
            ])
            ->sortByDesc('amount')
            ->values();

        $taxByState = $ticketSales
            ->filter(fn (TicketSale $sale) => $sale->event?->country === 'United States')
            ->groupBy(fn (TicketSale $sale) => $sale->event?->state ?: 'Unknown')
            ->map(fn ($sales, string $state) => [
                'state' => $state,
                'amount' => round((float) $sales->sum(fn (TicketSale $sale) => (float) $sale->event_tax), 2),
                'transactions' => $sales->count(),
                'country' => 'United States',
            ])
            ->sortByDesc('amount')
            ->values();

        $orders = Order::query()->get();

        $marketplaceRows = $orders
            ->groupBy(fn (Order $order) => $order->country ?: 'Unknown')
            ->map(function ($orders, string $country) use ($taxRateByCountry) {
                $sales = (float) $orders->sum(fn (Order $order) => (float) ($order->subtotal_amount ?: $order->total));
                $taxCollected = (float) $orders->sum(fn (Order $order) => (float) $order->tax_amount);
                $rule = $taxRateByCountry->get(strtolower($country));

                return [
                    'country' => $country,
                    'rate' => $sales > 0 ? round(($taxCollected / $sales) * 100, 2) : (float) ($rule['rate'] ?? 0),
                    'sales' => round($sales, 2),
                    'tax_collected' => round($taxCollected, 2),
                    'transactions' => $orders->count(),
                    'status' => $taxCollected > 0 ? 'Collected' : 'Pending',
                ];
            })
            ->sortByDesc('tax_collected')
            ->values();

        $eventTax = (float) $eventRows->sum('tax_collected');
        $marketplaceTax = (float) $marketplaceRows->sum('tax_collected');
        $jurisdictions = collect([...$eventRows->pluck('country'), ...$marketplaceRows->pluck('country'), ...$taxRules->pluck('country')])
            ->filter()
            ->unique()
            ->count();

        return $this->render('admin/taxes/TaxDashboard', $action, [
            'eventTaxRows' => $eventRows,
            'marketplaceTaxRows' => $marketplaceRows,
            'taxByCountry' => $taxByCountry,
            'taxByState' => $taxByState,
            'taxRules' => $taxRules,
            'taxSummary' => [
                'event_tax' => round($eventTax, 2),
                'marketplace_tax' => round($marketplaceTax, 2),
                'total_tax' => round($eventTax + $marketplaceTax, 2),
                'jurisdictions' => $jurisdictions,
                'transactions' => $ticketSales->count() + $orders->count(),
                'events' => $ticketSales->pluck('link_up_event_id')->filter()->unique()->count(),
            ],
        ]);
    }

    public function authorityApis(GetAdminOverviewDataAction $action): Response
    {
        return $this->render('admin/taxes/TaxAuthorityApis', $action);
    }

    public function corporateTax(GetAdminOverviewDataAction $action): Response
    {
        return $this->render('admin/taxes/LinkUpCorporateTax', $action);
    }

    public function settings(GetAdminOverviewDataAction $action): Response
    {
        $settings = TaxApiSetting::query()
            ->latest()
            ->get()
            ->map(fn (TaxApiSetting $setting) => [
                'id' => $setting->id,
                'provider' => $setting->provider,
                'end_point_url' => $setting->end_point_url,
                'is_active' => $setting->is_active,
                'created_at' => $setting->created_at?->toDateTimeString(),
                'updated_at' => $setting->updated_at?->toDateTimeString(),
            ])
            ->values();

        return $this->render('admin/taxes/TaxSettings', $action, [
            'taxApiSettings' => $settings,
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:tax_api_settings,id'],
            'provider' => ['required', 'string', 'max:255'],
            'end_point_url' => ['required', 'string', 'max:1000'],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);

        $setting = TaxApiSetting::query()
            ->when($data['id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->first();

        if (! $setting) {
            $setting = TaxApiSetting::firstOrNew(['provider' => $data['provider']]);
        }

        if (! $setting->exists && blank($data['api_key'] ?? null)) {
            return back()->withErrors(['api_key' => 'The API key field is required for a new provider.']);
        }

        if ($setting->exists && blank($data['api_key'] ?? null)) {
            try {
                $setting->api_key;
            } catch (DecryptException) {
                return back()->withErrors([
                    'api_key' => 'The saved API key cannot be decrypted. Enter the API key again to replace it.',
                ]);
            }
        }

        DB::transaction(function () use ($setting, $data) {
            if ($data['is_active'] ?? false) {
                TaxApiSetting::query()->update(['is_active' => false]);
            }

            $payload = [
                'provider' => $data['provider'],
                'end_point_url' => $data['end_point_url'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ];

            if (filled($data['api_key'] ?? null)) {
                $payload['api_key'] = $setting->exists
                    ? Crypt::encryptString($data['api_key'])
                    : $data['api_key'];
            }

            if ($setting->exists) {
                // A legacy/cross-environment encrypted value may not decrypt with
                // the current APP_KEY. Updating via the query builder avoids
                // Eloquent decrypting the old cast during dirty checking.
                TaxApiSetting::query()->whereKey($setting->getKey())->update($payload);
            } else {
                $setting->fill($payload)->save();
            }
        });

        return redirect()
            ->route('admin.finance.taxes.settings')
            ->with('message', 'Tax settings saved successfully.');
    }

    public function remittanceSettings(GetAdminOverviewDataAction $action): Response
    {
        return $this->render('admin/taxes/TaxRemittanceSettings', $action);
    }

    public function remittanceCenter(GetAdminOverviewDataAction $action): Response
    {
        $centers = TaxRemittanceCenter::query()
            ->latest()
            ->get()
            ->map(fn (TaxRemittanceCenter $center) => [
                'id' => $center->id,
                'agency_name' => $center->agency_name,
                'jurisdiction' => $center->jurisdiction,
                'payment_method' => $center->payment_method,
                'routing_number' => $center->routing_number,
                'account_number' => $center->account_number ? '********' . substr($center->account_number, -4) : null,
                'api_base' => $center->api_base,
                'has_api_key' => filled($center->api_key),
                'has_api_secret' => filled($center->api_secret),
                'is_active' => $center->is_active,
                'updated_at' => $center->updated_at?->toDateTimeString(),
            ])
            ->values();

        $reports = TicketSale::query()
            ->with(['event.user', 'event.organizer'])
            ->whereHas('event')
            ->get()
            ->groupBy('link_up_event_id')
            ->map(function ($sales) use ($centers) {
                $event = $sales->first()->event;
                $country = $event?->country ?: 'Unknown';
                $state = $event?->state;
                $jurisdiction = $state ?: $country;
                $taxCollected = (float) $sales->sum(fn (TicketSale $sale) => (float) $sale->event_tax);
                $gross = (float) $sales->sum(fn (TicketSale $sale) => (float) $sale->total);
                $center = $centers->first(fn (array $item) => strcasecmp($item['jurisdiction'], $jurisdiction) === 0 || str_contains(strtolower($item['jurisdiction']), strtolower($country)));

                return [
                    'id' => 'TR-' . ($event?->id ?: $sales->first()->link_up_event_id),
                    'event' => $event?->title ?: 'Untitled Event',
                    'organizer' => $event?->organizer_name ?: ($event?->organizer?->organizer_name ?: ($event?->user?->name ?: 'Unknown')),
                    'jurisdiction' => $jurisdiction,
                    'agency_name' => $center['agency_name'] ?? 'Unassigned',
                    'gross' => round($gross, 2),
                    'tax' => round($taxCollected, 2),
                    'transactions' => $sales->count(),
                    'status' => $taxCollected > 0 && $center ? 'Pending' : 'Review',
                ];
            })
            ->sortByDesc('tax')
            ->values();

        return $this->render('admin/taxes/TaxRemittanceCenter', $action, [
            'remittanceCenters' => $centers,
            'remittanceReports' => $reports,
        ]);
    }

    public function saveRemittanceCenter(Request $request): RedirectResponse
    {
        $data = $this->validatedRemittanceCenterData($request);

        $center = TaxRemittanceCenter::query()
            ->when($data['id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->first();

        $payload = [
            'agency_name' => $data['agency_name'],
            'jurisdiction' => $data['jurisdiction'],
            'payment_method' => $data['payment_method'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (! $center || filled($data['routing_number'] ?? null)) {
            $payload['routing_number'] = $data['routing_number'] ?? null;
        }

        if (! $center || filled($data['account_number'] ?? null)) {
            $payload['account_number'] = $data['account_number'] ?? null;
        }

        if (! $center || filled($data['api_base'] ?? null)) {
            $payload['api_base'] = $data['api_base'] ?? null;
        }

        if (filled($data['api_key'] ?? null)) {
            $payload['api_key'] = $data['api_key'];
        }

        if (filled($data['api_secret'] ?? null)) {
            $payload['api_secret'] = $data['api_secret'];
        }

        if ($center) {
            $center->update($payload);
        } else {
            TaxRemittanceCenter::create($payload);
        }

        return redirect()
            ->route('admin.finance.taxes.remittance-center')
            ->with('message', 'Tax remittance destination saved successfully.');
    }

    public function deleteRemittanceCenter(TaxRemittanceCenter $taxRemittanceCenter): RedirectResponse
    {
        $taxRemittanceCenter->delete();

        return redirect()
            ->route('admin.finance.taxes.remittance-center')
            ->with('message', 'Tax remittance destination deleted successfully.');
    }

    private function validatedTaxData(Request $request): array
    {
        $data = $request->validate([
            'country' => ['nullable', 'string', 'max:255'],
            'country_label' => ['required', 'string', 'max:255'],
            'tax_type' => ['required', 'in:percentage,fixed'],
            'tax' => ['required', 'numeric', 'min:0'],
        ]);

        if (blank($data['country'] ?? null)) {
            $country = Country::query()
                ->where('name', $data['country_label'])
                ->first();

            $data['country'] = $country?->code;
        }

        $data['country'] = $this->resolveCountryCode($data['country_label'], $data['country'] ?? null);

        return $data;
    }

    private function validatedRemittanceCenterData(Request $request): array
    {
        return $request->validate([
            'id' => ['nullable', 'integer', 'exists:tax_remittance_centers,id'],
            'agency_name' => ['required', 'string', 'max:255'],
            'jurisdiction' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:ach,wire,eftps,state_api'],
            'routing_number' => ['nullable', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'api_base' => ['nullable', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'api_secret' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);
    }

    private function resolveCountryCode(?string $countryLabel, ?string $currentCode = null): string
    {
        $code = strtoupper(trim((string) $currentCode));

        if (strlen($code) === 2) {
            return $code;
        }

        $codes = [
            'algeria' => 'DZ',
            'andorra' => 'AD',
            'bahamas' => 'BS',
            'the bahamas' => 'BS',
            'pakistan' => 'PK',
            'jamaica' => 'JM',
            'barbados' => 'BB',
            'trinidad & tobago' => 'TT',
            'trinidad and tobago' => 'TT',
            'mexico' => 'MX',
            'brazil' => 'BR',
            'canada' => 'CA',
            'united states' => 'US',
            'united states of america' => 'US',
        ];

        $normalizedLabel = strtolower(trim((string) $countryLabel));

        return $codes[$normalizedLabel] ?? $code;
    }
}
