<?php

namespace App\Http\Controllers\Frontend;

use Stripe\Stripe;
use Inertia\Inertia;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\TicketSaleAddon;
use App\Services\TaxRateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EventFeeSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Http\Controllers\Controller;
use App\Mail\BuyTicketEmailMail;
use App\Mail\BuyWellnessTicketEmailMail;
use App\Mail\BuyCookoutTicketEmailMail;
use App\Models\LinkUpEvent;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Models\Sponsor;
use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class StripeController extends Controller
{

    private function filterFeeBreakdownByTicketType($ticket, ?array $feeBreakdown): ?array
    {
        if (!$feeBreakdown || !$ticket) {
            return $feeBreakdown;
        }

        $type = strtolower(trim((string)($ticket->type ?? '')));
        $ticketType = strtolower(trim((string)($ticket->ticket_type ?? '')));

        $isDrinkType = str_contains($type, 'drink') || str_contains($ticketType, 'drink');

        if ($isDrinkType) {
            return [
                'drink_fee_pct_rate' => $feeBreakdown['drink_fee_pct_rate'] ?? 0,
                'drink_fee_amount' => $feeBreakdown['drink_fee_amount'] ?? 0,
                'bottle_fee_pct_rate' => $feeBreakdown['bottle_fee_pct_rate'] ?? 0,
                'bottle_fee_amount' => $feeBreakdown['bottle_fee_amount'] ?? 0,
            ];
        }

        return [
            'service_fee_pct_rate' => $feeBreakdown['service_fee_pct_rate'] ?? 0,
            'service_fee_pct_amount' => $feeBreakdown['service_fee_pct_amount'] ?? 0,
            'service_fee_fixed' => $feeBreakdown['service_fee_fixed'] ?? 0,
            'processing_fee_pct_rate' => $feeBreakdown['processing_fee_pct_rate'] ?? 0,
            'processing_fee_pct_amount' => $feeBreakdown['processing_fee_pct_amount'] ?? 0,
            'processing_fee_fixed' => $feeBreakdown['processing_fee_fixed'] ?? 0,
        ];
    }

    public function buyTicket(Request $request)
    {
        $cart = $request->validate([
            'items' => 'array',
            'items.*.ticketId' => 'integer',
            'items.*.qty' => 'integer|min:0',
            'items.*.addons' => 'array|nullable',
            'items.*.addons.*.addon_id' => 'sometimes|integer|nullable',
            'items.*.addons.*.name' => 'sometimes|string|nullable',
            'items.*.addons.*.quantity' => 'integer',
            'items.*.addons.*.category' => 'sometimes|string|nullable',
            'items.*.addons.*.section' => 'sometimes|string|nullable',
            'items.*.cookout' => 'sometimes|array|nullable',
            'items.*.cookout.includedProtein' => 'sometimes|string|nullable',
            'items.*.cookout.proteins' => 'sometimes|array|nullable',
            'items.*.cookout.proteins.*.name' => 'required_with:items.*.cookout.proteins|string',
            'items.*.cookout.proteins.*.qty' => 'required_with:items.*.cookout.proteins|integer|min:0',
            'items.*.cookout.drinks' => 'sometimes|array|nullable',
            'items.*.cookout.drinks.*.name' => 'required_with:items.*.cookout.drinks|string',
            'items.*.cookout.drinks.*.qty' => 'required_with:items.*.cookout.drinks|integer|min:0',
            'items.*.cookout.manualAddons' => 'sometimes|array|nullable',
            'items.*.cookout.manualAddons.*.name' => 'required_with:items.*.cookout.manualAddons|string',
            'items.*.cookout.manualAddons.*.qty' => 'required_with:items.*.cookout.manualAddons|integer|min:0',
            'items.*.wellness' => 'sometimes|array|nullable',
            'items.*.wellness.serviceMode' => 'sometimes|string|in:mobile,inhouse|nullable',
            'items.*.wellness.selectedSlot' => 'sometimes|string|nullable',
            'items.*.wellness.selectedSlotDate' => 'sometimes|string|nullable',
            'items.*.wellness.holdExpiresAt' => 'sometimes|numeric|nullable',
            'items.*.wellness.includedService' => 'sometimes|string|nullable',
            'items.*.wellness.services' => 'sometimes|array|nullable',
            'items.*.wellness.services.*.name' => 'required_with:items.*.wellness.services|string',
            'items.*.wellness.services.*.qty' => 'required_with:items.*.wellness.services|integer|min:0',
            'items.*.wellness.manualAddons' => 'sometimes|array|nullable',
            'items.*.wellness.manualAddons.*.name' => 'required_with:items.*.wellness.manualAddons|string',
            'items.*.wellness.manualAddons.*.qty' => 'required_with:items.*.wellness.manualAddons|integer|min:0',
            'items.*.wellness.contactPhone' => 'sometimes|string|nullable',
            'appliedCoupons' => 'array',
            'appliedCoupons.*' => 'string',
        ]);

        $ticketIds = array_column($cart['items'], 'ticketId');
        $tickets = Ticket::whereIn('id', $ticketIds)->with(['drinkPackage', 'extraSetting'])->get();

        if ($tickets->count() !== count($ticketIds)) {
            return back()->withErrors(['cart' => 'One or more tickets are invalid']);
        }

        $eventId = $tickets->first()->event_id;

        $eventFeeSettings = null;
        if (Schema::hasColumn('event_fee_settings', 'link_up_event_id')) {
            $eventFeeSettings = EventFeeSetting::where('link_up_event_id', $eventId)->first();
        }
        if (!$eventFeeSettings) {
            $eventFeeSettings = EventFeeSetting::first();
        }

        $event = LinkUpEvent::with('eventDetails')->find($eventId);
        $taxIncluded = $event?->eventDetails?->tax_included === 'yes';

        $total = 0;
        $lineItems = [];
        $ticketLineIndexes = [];

        $cartSubtotal = 0;
        $vipTicketSubtotal = 0;
        $standardTicketSubtotal = 0;
        $totalServiceFeePercent = 0;
        $totalServiceFeeFixed = 0;
        $totalProcessingFeePercent = 0;
        $totalProcessingFeeFixed = 0;
        $totalDrinkFees = 0;
        $totalBottleFees = 0;
        $totalVipFees = 0;
        $event_tax_total = 0;
        $totalMobileFee = 0;

        $eventTaxRate = 0;
        $shouldTaxFees = false;
        $hasNoSalesTax = false;

        // Build new cart items array to avoid reference issues
        $newCartItems = [];
        foreach ($cart['items'] as $item) {
            $ticket = $tickets->firstWhere('id', $item['ticketId']);
            if (!$ticket) {
                return back()->withErrors(["{$item['ticketId']}" => 'Ticket not found']);
            }
            if ($item['qty'] > 0 && $ticket->quantity < $item['qty']) {
                return back()->withErrors(["{$ticket->name}" => 'Ticket is sold out or insufficient quantity']);
            }
            $event = LinkUpEvent::with('eventDetails')->find($ticket->event_id);
            if (!$event) {
                return back()->withErrors(['cart' => 'Event not found for ticket']);
            }
            // get stripe currency value from helper file 
            $country = $event?->organizer?->contacts?->country;
            $currency = country_to_currency($country);

            $cookoutConfig = $ticket->cookout;
            if (is_string($cookoutConfig)) {
                $decodedCookout = json_decode($cookoutConfig, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $cookoutConfig = $decodedCookout;
                }
            }
            // Don't set $ticket->cookout - it causes save() to try to persist to DB
            $isCookoutTicket = is_array($cookoutConfig) && (($cookoutConfig['includeFood'] ?? null) === 'yes');

            $item['ticket'] = $ticket;
            $base = floatval($ticket->price ?? 0);
            $promo = floatval($ticket->promo_price ?? 0);
            $discounted = $base - (is_nan($promo) ? 0 : $promo);
            if ($discounted < 0) {
                $discounted = 0;
            }
            $item['price'] = $discounted;

            $cookoutTotal = 0;
            $cookoutAddonsJson = [];
            $cookoutSelectionJson = null;
            $cookoutInput = $item['cookout'] ?? null;
            if (is_array($cookoutConfig) && (($cookoutConfig['includeFood'] ?? null) === 'yes') && is_array($cookoutInput)) {
                $allowedProteins = [];
                $cookoutProteinSources = [];
                if (isset($cookoutConfig['proteins']) && is_array($cookoutConfig['proteins'])) {
                    $cookoutProteinSources = array_merge($cookoutProteinSources, $cookoutConfig['proteins']);
                }
                if (isset($cookoutConfig['customProteins']) && is_array($cookoutConfig['customProteins'])) {
                    $cookoutProteinSources = array_merge($cookoutProteinSources, $cookoutConfig['customProteins']);
                }
                if (isset($cookoutConfig['custom_proteins']) && is_array($cookoutConfig['custom_proteins'])) {
                    $cookoutProteinSources = array_merge($cookoutProteinSources, $cookoutConfig['custom_proteins']);
                }

                foreach ($cookoutProteinSources as $p) {
                    if (!is_array($p)) {
                        continue;
                    }
                    if (($p['mode'] ?? null) !== 'addon') {
                        continue;
                    }
                    $name = trim((string) ($p['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $key = strtolower($name);
                    $allowedProteins[$key] = [
                        'name' => $name,
                        'price' => floatval($p['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $p) ? intval($p['qty'] ?? 0) : null,
                    ];
                }

                $allowedManual = [];
                foreach (($cookoutConfig['manualAddons'] ?? []) as $a) {
                    if (!is_array($a)) {
                        continue;
                    }
                    $name = trim((string) ($a['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $key = strtolower($name);
                    $allowedManual[$key] = [
                        'name' => $name,
                        'price' => floatval($a['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $a) ? intval($a['qty'] ?? 0) : null,
                    ];
                }

                $allowedDrinks = [];
                foreach (($cookoutConfig['drinks'] ?? []) as $d) {
                    if (is_string($d)) {
                        continue;
                    }
                    if (!is_array($d)) {
                        continue;
                    }
                    if (($d['mode'] ?? null) !== 'addon') {
                        continue;
                    }
                    $name = trim((string) ($d['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $key = strtolower($name);
                    $allowedDrinks[$key] = [
                        'name' => $name,
                        'price' => floatval($d['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $d) ? intval($d['qty'] ?? 0) : null,
                    ];
                }

                $selectedProteins = [];
                foreach (($cookoutInput['proteins'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedProteins[$key])) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon selected']);
                    }
                    $maxQty = $allowedProteins[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon quantity']);
                    }
                    $unitPrice = floatval($allowedProteins[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $cookoutTotal += $lineTotal;
                    $cookoutAddonsJson[] = [
                        'name' => $allowedProteins[$key]['name'],
                        'category' => 'cookout_protein',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                    $selectedProteins[] = ['name' => $allowedProteins[$key]['name'], 'qty' => $qty, 'price' => $unitPrice];
                }

                $selectedManual = [];
                foreach (($cookoutInput['manualAddons'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedManual[$key])) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon selected']);
                    }
                    $maxQty = $allowedManual[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon quantity']);
                    }
                    $unitPrice = floatval($allowedManual[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $cookoutTotal += $lineTotal;
                    $cookoutAddonsJson[] = [
                        'name' => $allowedManual[$key]['name'],
                        'category' => 'cookout_extra',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                    $selectedManual[] = ['name' => $allowedManual[$key]['name'], 'qty' => $qty, 'price' => $unitPrice];
                }

                $selectedDrinks = [];
                foreach (($cookoutInput['drinks'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedDrinks[$key])) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon selected']);
                    }
                    $maxQty = $allowedDrinks[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon quantity']);
                    }
                    $unitPrice = floatval($allowedDrinks[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $cookoutTotal += $lineTotal;
                    $cookoutAddonsJson[] = [
                        'name' => $allowedDrinks[$key]['name'],
                        'category' => 'cookout_drink',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                    $selectedDrinks[] = ['name' => $allowedDrinks[$key]['name'], 'qty' => $qty, 'price' => $unitPrice];
                }

                $cookoutSelectionJson = [
                    'includedProtein' => $cookoutInput['includedProtein'] ?? null,
                    'includedSides' => (is_array($cookoutConfig) && isset($cookoutConfig['sides']) && is_array($cookoutConfig['sides'])) ? $cookoutConfig['sides'] : [],
                    'proteins' => $selectedProteins,
                    'drinks' => $selectedDrinks,
                    'manualAddons' => $selectedManual,
                ];
            }
            $item['cookout_total'] = $cookoutTotal;
            $item['cookout_addons_json'] = $cookoutAddonsJson;
            $item['cookout_selection'] = $cookoutSelectionJson;

            $wellnessTotal = 0;
            $wellnessAddonsJson = [];
            $wellnessConfig = $ticket->wellness;
            $wellnessInput = $item['wellness'] ?? null;
            $wellnessIncludedService = null;
            $wellnessIncludedServiceType = null;
            $wellnessSelectedSlot = null;

            if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                $booking = $wellnessConfig['booking'] ?? null;
                $selectedMode = is_array($wellnessInput) ? ($wellnessInput['serviceMode'] ?? 'inhouse') : 'inhouse';
                if (is_array($booking) && (isset($booking['mobileFee']))) {
                    $fee = floatval($booking['mobileFee'] ?? 0);
                    if ($fee > 0 && $selectedMode === 'mobile') {
                        $totalMobileFee += $fee * $item['qty'];
                    }
                }
            }

            if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes') && is_array($wellnessInput)) {
                $wellnessSelectedSlot = $wellnessInput['selectedSlot'] ?? null;
                $wellnessSelectedSlotDate = $wellnessInput['selectedSlotDate'] ?? null;
                $wellnessHoldExpiresAt = $wellnessInput['holdExpiresAt'] ?? null;

                if ($wellnessSelectedSlot && $wellnessHoldExpiresAt) {
                    $now = round(microtime(true) * 1000);
                    if ($now > $wellnessHoldExpiresAt) {
                        $wellnessSelectedSlot = null;
                        $wellnessSelectedSlotDate = null;
                    }
                }

                if ($wellnessSelectedSlot && $wellnessSelectedSlotDate) {
                    $times = explode('-', $wellnessSelectedSlot);
                    if (count($times) === 2) {
                        $start = trim($times[0]) . ':00';
                        $end = trim($times[1]) . ':00';
                        $block = \App\Models\WellnessSlotBlock::where([
                            'ticket_id' => $ticket->id,
                            'slot_date' => $wellnessSelectedSlotDate,
                            'start_time' => $start,
                            'end_time' => $end,
                        ])->first();

                        if (!$block || $block->count <= 0) {
                            $wellnessSelectedSlot = null;
                            $wellnessSelectedSlotDate = null;
                        } else {
                            $item['wellness_slot_block_id'] = $block->id;
                        }
                    }
                }

                $includedName = trim((string) ($wellnessInput['includedService'] ?? ''));
                if ($includedName !== '') {
                    $allowedIncluded = [];
                    foreach (($wellnessConfig['services'] ?? []) as $s) {
                        if (!is_array($s)) {
                            continue;
                        }
                        if (($s['mode'] ?? null) === 'addon') {
                            continue;
                        }
                        $name = trim((string) ($s['name'] ?? ''));
                        if ($name === '') {
                            continue;
                        }
                        $allowedIncluded[strtolower($name)] = [
                            'name' => $name,
                            'type' => $s['type'] ?? null,
                        ];
                    }
                    $key = strtolower($includedName);
                    if (!isset($allowedIncluded[$key])) {
                        return back()->withErrors(['wellness' => 'Invalid included wellness service selected']);
                    }
                    $wellnessIncludedService = $allowedIncluded[$key]['name'] ?? null;
                    $wellnessIncludedServiceType = $allowedIncluded[$key]['type'] ?? null;
                }

                $allowedServices = [];
                foreach (($wellnessConfig['services'] ?? []) as $s) {
                    if (!is_array($s)) {
                        continue;
                    }
                    if (($s['mode'] ?? null) !== 'addon') {
                        continue;
                    }
                    $name = trim((string) ($s['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $allowedServices[strtolower($name)] = [
                        'name' => $name,
                        'price' => floatval($s['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $s) ? intval($s['qty'] ?? 0) : null,
                    ];
                }

                $allowedManual = [];
                foreach (($wellnessConfig['manualAddons'] ?? []) as $a) {
                    if (!is_array($a)) {
                        continue;
                    }
                    $name = trim((string) ($a['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $allowedManual[strtolower($name)] = [
                        'name' => $name,
                        'price' => floatval($a['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $a) ? intval($a['qty'] ?? 0) : null,
                    ];
                }

                foreach (($wellnessInput['services'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedServices[$key])) {
                        return back()->withErrors(['wellness' => 'Invalid wellness service selected']);
                    }
                    $maxQty = $allowedServices[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['wellness' => 'Invalid wellness service quantity']);
                    }
                    $unitPrice = floatval($allowedServices[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $wellnessTotal += $lineTotal;
                    $wellnessAddonsJson[] = [
                        'name' => $allowedServices[$key]['name'],
                        'category' => 'wellness_service',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }

                foreach (($wellnessInput['manualAddons'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedManual[$key])) {
                        return back()->withErrors(['wellness' => 'Invalid wellness addon selected']);
                    }
                    $maxQty = $allowedManual[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['wellness' => 'Invalid wellness addon quantity']);
                    }
                    $unitPrice = floatval($allowedManual[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $wellnessTotal += $lineTotal;
                    $wellnessAddonsJson[] = [
                        'name' => $allowedManual[$key]['name'],
                        'category' => 'wellness_manual',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }
            }

            $item['wellness_total'] = $wellnessTotal;
            if ($wellnessSelectedSlot) {
                $formattedDate = $wellnessSelectedSlotDate ? \Carbon\Carbon::parse($wellnessSelectedSlotDate)->format('M d, Y') : null;
                $wellnessAddonsJson[] = [
                    'name' => 'Selected Slot: ' . $wellnessSelectedSlot . ($formattedDate ? ' (' . $formattedDate . ')' : ''),
                    'category' => 'wellness_slot',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                    'slot' => $wellnessSelectedSlot,
                    'slot_date' => $wellnessSelectedSlotDate,
                ];
            }
            if (is_array($wellnessInput) && isset($wellnessInput['serviceMode'])) {
                $modeLabel = $wellnessInput['serviceMode'] === 'mobile' ? 'Mobile' : 'In-house';
                $wellnessAddonsJson[] = [
                    'name' => 'Service mode: ' . $modeLabel,
                    'category' => 'wellness_mode',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                ];
                if ($wellnessInput['serviceMode'] === 'mobile') {
                    $contactPhone = trim((string) ($wellnessInput['contactPhone'] ?? ''));
                    if ($contactPhone !== '') {
                        $wellnessAddonsJson[] = [
                            'name' => 'Contact phone: ' . $contactPhone,
                            'category' => 'wellness_contact_phone',
                            'quantity' => 1,
                            'unit_price' => 0,
                            'total_price' => 0,
                        ];
                    }
                }
            }
            if ($wellnessIncludedService) {
                $wellnessAddonsJson[] = [
                    'name' => $wellnessIncludedService,
                    'category' => 'wellness_included_service',
                    'service_type' => $wellnessIncludedServiceType,
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                ];
            }
            $item['wellness_addons_json'] = $wellnessAddonsJson;
            $item['wellness_included_service'] = $wellnessIncludedService;

            if ($eventTaxRate <= 0) {
                $eventTaxRate = floatval(preg_replace('/[^0-9.]/', '', $event->eventDetails->tax_rate ?? 0));
            }

            $taxRatePercent = $eventTaxRate;

            $taxService = new TaxRateService();
            $stateCode = strtoupper($event->state ?? '');
            $taxRulesResponse = $taxService->getStateTaxRules($stateCode, $event->zip ?? null, $event->city ?? null, $event->country ?? 'US');

            $hasNoSalesTax = $taxRulesResponse['no_state_sales_tax'] ?? false;
            $shouldTaxFees = $taxRulesResponse['tax_fees'] ?? false;

            if ($taxRulesResponse['success'] && $eventTaxRate <= 0) {
                $taxRatePercent = $taxRulesResponse['rate'] * 100;
            }

            if (!$hasNoSalesTax) {
                if ($shouldTaxFees) {
                    $item_event_tax = 0;
                } else {
                    $item_event_tax = (($item['price'] * $item['qty']) + $cookoutTotal) * ($taxRatePercent / 100);
                }
            } else {
                $item_event_tax = 0;
            }

            $event_tax_total += $item_event_tax;

            $tablesTotal = 0;
            $drinksTotal = 0;
            $drinkAddonsJson = [];
            $tableAddonsJson = [];
            $hasPackage = !empty($ticket->package_id) && !empty($ticket->drinkPackage) && ($ticket->has_table === 'yes');
            $hasExplicitTableAddon = false;
            $packageTableCharge = 0;
            $tableSections = [];

            if (isset($item['addons']) && is_array($item['addons']) && count($item['addons']) > 0) {
                foreach ($item['addons'] as $addonData) {
                    if (isset($addonData['category']) && $addonData['category'] === 'table') {
                        $secLabel = $addonData['section'] ?? '-';
                        $secQty = intval($addonData['quantity'] ?? 0);
                        if ($secQty > 0) {
                            $tableSections[] = ['section' => $secLabel, 'quantity' => $secQty];
                        }
                        if ($hasPackage) {
                            $hasExplicitTableAddon = true;
                        }
                        $hasExplicitTableAddon = true;
                        $tableQty = intval($addonData['quantity'] ?? 0);
                        $tableUnit = floatval($ticket->table_price ?? 0);
                        $ticketUnit = floatval($item['price'] ?? 0);
                        if ($tableQty > 0 && $tableUnit > 0) {
                            $shouldCharge = ($tableUnit !== $ticketUnit);
                            $tableTotal = $shouldCharge ? ($tableUnit * $tableQty) : 0;
                            if ($shouldCharge) {
                                $tablesTotal += $tableTotal;
                            }
                            $tableAddonsJson[] = [
                                'section' => $secLabel,
                                'capacity' => $ticket->table_capacity ?? 0,
                                'quantity' => $tableQty,
                                'unit_price' => $tableUnit,
                                'total_price' => $tableTotal,
                                'table_seating' => $ticket->sections ?? null,
                            ];
                        }
                        continue;
                    }

                    $addon = null;
                    if (isset($addonData['addon_id'])) {
                        $addon = $this->findAddonInTicket($ticket, $addonData['addon_id']);
                    }
                    if (!$addon && isset($addonData['name']) && isset($addonData['category'])) {
                        $addon = $this->findAddonByNameAndCategory($ticket, $addonData['name'], $addonData['category']);
                    }
                    if (!$addon && isset($addonData['name'])) {
                        $addon = $this->findAddonByNameAnyCategory($ticket, $addonData['name']);
                    }
                    if (!$addon && isset($addonData['name'])) {
                        $addon = $this->findAddonInPackageByName($ticket, $addonData['name']);
                    }
                    if (!$addon && isset($addonData['addon_id'])) {
                        $addon = $this->findLegacyAddonById($ticket, intval($addonData['addon_id']));
                    }
                    if (!$addon && isset($addonData['name'])) {
                        $addon = $this->findLegacyAddonByName($ticket, $addonData['name']);
                    }
                    if (!$addon) {
                        return back()->withErrors(['addon' => 'Invalid addon selected']);
                    }
                    $drinkQty = $addonData['quantity'];
                    $drinkUnitPrice = isset($addon['cost']) ? floatval($addon['cost']) : floatval($addon['price'] ?? 0);
                    $drinkTotalPrice = $drinkUnitPrice * $drinkQty;
                    $drinksTotal += $drinkTotalPrice;

                    $drinkAddonsJson[] = [
                        'name' => $addon['name'],
                        'category' => $addon['category'],
                        'section' => $addonData['section'] ?? '-',
                        'quantity' => $drinkQty,
                        'unit_price' => $drinkUnitPrice,
                        'total_price' => $drinkTotalPrice,
                    ];

                    if ($eventFeeSettings) {
                        $drinkFeePct = floatval($eventFeeSettings->drink_fee_pct ?? 0);
                        $bottleFeePct = floatval($eventFeeSettings->bottle_fee_pct ?? 0);

                        $category = $addon['category'] ?? '';
                        $isBottleType = ($category === 'bottles');
                        $isRegularDrinkType = in_array($category, ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks']);

                        if ($isRegularDrinkType) {
                            $totalDrinkFees += $drinkTotalPrice * ($drinkFeePct / 100);
                        }
                        if ($isBottleType) {
                            $totalBottleFees += $drinkTotalPrice * ($bottleFeePct / 100);
                        }
                    }
                }
            }
            if ($hasPackage && !$hasExplicitTableAddon) {
                $tableUnit = floatval($ticket->table_price ?? 0);
                $ticketUnit = $discounted;
                $packageTableQty = intval($item['qty'] ?? 0);
                $shouldChargePackageTable = ($tableUnit > 0 && $tableUnit !== $ticketUnit);
                $packageTableCharge = $shouldChargePackageTable ? ($tableUnit * $packageTableQty) : 0;

                if ($tableUnit > 0 && $packageTableQty > 0) {
                    if (!empty($tableSections)) {
                        foreach ($tableSections as $ts) {
                            $qty = intval($ts['quantity'] ?? 0);
                            if ($qty <= 0) continue;
                            $tableAddonsJson[] = [
                                'section' => $ts['section'] ?? '-',
                                'capacity' => $ticket->table_capacity ?? 0,
                                'quantity' => $qty,
                                'unit_price' => $tableUnit,
                                'total_price' => $shouldChargePackageTable ? ($tableUnit * $qty) : 0,
                            ];
                        }
                    } else {
                        $tableAddonsJson[] = [
                            'section' => '-',
                            'capacity' => $ticket->table_capacity ?? 0,
                            'quantity' => $packageTableQty,
                            'unit_price' => $tableUnit,
                            'total_price' => $packageTableCharge,
                        ];
                    }
                }

                if ($shouldChargePackageTable) {
                    $tablesTotal += $packageTableCharge;
                }
            }

            $item['tables_total'] = $tablesTotal;
            $item['drinks_total'] = $drinksTotal;
            $item['addon_total'] = $tablesTotal + $drinksTotal + ($item['cookout_total'] ?? 0) + ($item['wellness_total'] ?? 0);
            $item['drink_addons_json'] = $drinkAddonsJson;
            $item['table_addons_json'] = $tableAddonsJson;
            $item['event_tax'] = $item_event_tax ?? 0;

            $itemSubtotal = ($item['price'] * $item['qty']) + $item['addon_total'];
            $total += $itemSubtotal;
            $cartSubtotal += $itemSubtotal;

            $isTableSelected = $hasExplicitTableAddon || $hasPackage;
            $typeStr = strtolower(trim((string)($ticket->type ?? '')));
            $ticketTypeStr = strtolower(trim((string)($ticket->ticket_type ?? '')));
            $isDrinkOnlyTicket = str_contains($typeStr, 'drink')
                || str_contains($ticketTypeStr, 'drink')
                || (intval($item['qty'] ?? 0) <= 0 && floatval($item['drinks_total'] ?? 0) > 0 && floatval($item['tables_total'] ?? 0) <= 0);

            if ($isDrinkOnlyTicket) {
                // Drink-only ticket types should not be included in service/processing/vip fee base
                // They only pay drink/bottle fees.
                continue;
            }
            if ($isTableSelected) {
                $tableUnit = floatval($ticket->table_price ?? 0);
                $ticketUnit = $item['price'];

                if ($tableUnit > 0 && $tableUnit !== $ticketUnit) {
                    $vipTicketSubtotal += $tableUnit * $item['qty'];
                } else {
                    $vipTicketSubtotal += $ticketUnit * $item['qty'];
                }
            } else {
                $standardTicketSubtotal += ($item['price'] * $item['qty']);
            }

            if ($item['qty'] > 0 || $item['addon_total'] > 0) {
                if ($item['qty'] > 0) {
                    $ticketName = $ticket->name;
                    if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                        $ticketName .= ' (Wellness)';
                        $selectedMode = isset($item['wellness']['serviceMode']) ? $item['wellness']['serviceMode'] : null;
                        if ($selectedMode) {
                            $ticketName .= ' • ' . ($selectedMode === 'mobile' ? 'Mobile' : 'In-house');
                        }
                    }

                    $lineItems[] = [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => $ticketName,
                            ],
                            'unit_amount' => (int) round($item['price'] * 100, 0),
                        ],
                        'quantity' => $item['qty'],
                    ];
                    $ticketLineIndexes[] = count($lineItems) - 1;

                    // Add cookout add-ons as separate line items (even if ticket qty is 0)
                    foreach ($item['cookout_addons_json'] ?? [] as $cookoutAddon) {
                        if ($cookoutAddon['quantity'] > 0) {
                            $lineItems[] = [
                                'price_data' => [
                                    'currency' => $currency,
                                    'product_data' => [
                                        'name' => $cookoutAddon['name'] . ' (' . Str::title(str_replace('_', ' ', $cookoutAddon['category'])) . ')',
                                    ],
                                    'unit_amount' => (int) round($cookoutAddon['unit_price'] * 100, 0),
                                ],
                                'quantity' => $cookoutAddon['quantity'],
                            ];
                        }
                    }

                    // Add wellness add-ons as separate line items
                    foreach ($item['wellness_addons_json'] ?? [] as $wellnessAddon) {
                        if (($wellnessAddon['quantity'] ?? 0) > 0) {
                            $lineItems[] = [
                                'price_data' => [
                                    'currency' => $currency,
                                    'product_data' => [
                                        'name' => $wellnessAddon['name'] . ' (' . Str::title(str_replace('_', ' ', $wellnessAddon['category'])) . ')',
                                    ],
                                    'unit_amount' => (int) round(floatval($wellnessAddon['unit_price'] ?? 0) * 100, 0),
                                ],
                                'quantity' => intval($wellnessAddon['quantity'] ?? 0),
                            ];
                        }
                    }

                    // Add drink add-ons as separate line items
                    foreach ($item['drink_addons_json'] ?? [] as $drinkAddon) {
                        if ($drinkAddon['quantity'] > 0) {
                            $lineItems[] = [
                                'price_data' => [
                                    'currency' => $currency,
                                    'product_data' => [
                                        'name' => $drinkAddon['name'] . ' (' . Str::title(str_replace('_', ' ', $drinkAddon['category'])) . ')',
                                    ],
                                    'unit_amount' => (int) round($drinkAddon['unit_price'] * 100, 0),
                                ],
                                'quantity' => $drinkAddon['quantity'],
                            ];
                        }
                    }

                    // Add table add-ons as separate line items
                    foreach ($item['table_addons_json'] ?? [] as $tableAddon) {
                        if (($tableAddon['total_price'] ?? 0) > 0 && ($tableAddon['quantity'] ?? 0) > 0) {
                            $unitCents = (int) round((floatval($tableAddon['total_price']) / intval($tableAddon['quantity'])) * 100, 0);
                            $lineItems[] = [
                                'price_data' => [
                                    'currency' => $currency,
                                    'product_data' => [
                                        'name' => 'Table for ' . ($tableAddon['capacity'] ?? 0) . ' — ' . ($tableAddon['section'] ?? '-'),
                                    ],
                                    'unit_amount' => $unitCents,
                                ],
                                'quantity' => intval($tableAddon['quantity']),
                            ];
                        }
                    }
                }

                if ($hasPackage) {
                    $package = $ticket->drinkPackage;
                    $packageDescription = [];

                    if ($package->bottles && is_array($package->bottles) && count($package->bottles) > 0) {
                        $bottlesList = array_map(function ($b) {
                            return $b['qty'] . '× ' . $b['name'];
                        }, $package->bottles);
                        $packageDescription[] = '🍹 Bottles: ' . implode(', ', $bottlesList);
                    }
                    if ($package->chasers && is_array($package->chasers) && count($package->chasers) > 0) {
                        $chasersList = array_map(function ($c) {
                            return $c['qty'] . '× ' . $c['name'];
                        }, $package->chasers);
                        $packageDescription[] = '🥤 Chasers: ' . implode(', ', $chasersList);
                    }
                    if ($package->waters && is_array($package->waters) && count($package->waters) > 0) {
                        $watersList = array_map(function ($w) {
                            return $w['qty'] . '× ' . $w['name'];
                        }, $package->waters);
                        $packageDescription[] = '💧 Waters: ' . implode(', ', $watersList);
                    }

                    $lineItems[] = [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => '📦 ' . $package->name,
                                'description' => !empty($packageDescription) ? implode(' | ', $packageDescription) : 'Bottle service package',
                            ],
                            'unit_amount' => 0,
                        ],
                        'quantity' => 1,
                    ];
                }
            }

            // Add the modified item to the new array
            $newCartItems[] = $item;
        }
        if ($standardTicketSubtotal > 0 && $eventFeeSettings) {
            $totalServiceFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->service_fee_pct ?? 0) / 100);
            $totalServiceFeeFixed = floatval($eventFeeSettings->service_fee_fixed ?? 0);
            $totalProcessingFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->processing_fee_pct ?? 0) / 100);
            $totalProcessingFeeFixed = floatval($eventFeeSettings->processing_fee_fixed ?? 0);
        }

        if ($vipTicketSubtotal > 0 && $eventFeeSettings) {
            $vipPct = floatval($eventFeeSettings->vip_fee_pct ?? 0) / 100;
            $totalVipFees = $vipTicketSubtotal * $vipPct;
        }

        $taxRatePercent = $eventTaxRate;

        $taxService = new TaxRateService();

        foreach ($newCartItems as $item) {
            $ticket = $tickets->firstWhere('id', $item['ticketId']);
            if ($ticket && $ticket->event->state) {
                $stateCode = strtoupper($ticket->event->state);
                $taxRulesResponse = $taxService->getStateTaxRules(
                    $stateCode,
                    $ticket->event->zip ?? null,
                    $ticket->event->city ?? null,
                    $ticket->event->country ?? 'US'
                );

                if ($taxRulesResponse['success']) {
                    if ($taxRulesResponse['no_state_sales_tax'] ?? false) {
                        $hasNoSalesTax = true;
                        break;
                    }
                    if ($taxRulesResponse['tax_fees'] ?? false) {
                        $shouldTaxFees = true;
                    }
                    if ($taxRatePercent <= 0) {
                        $taxRatePercent = $taxRulesResponse['rate'] * 100;
                    }
                }
            }
        }

        if (!$hasNoSalesTax && $eventFeeSettings && $taxIncluded) {
            $taxBase = $standardTicketSubtotal + $vipTicketSubtotal;
            // if ($shouldTaxFees) {
            if ($shouldTaxFees) {
                $taxBase += $totalServiceFeePercent + $totalServiceFeeFixed
                    + $totalProcessingFeePercent + $totalProcessingFeeFixed;
            }
            // }
            $calculatedTax = $taxBase * ($taxRatePercent / 100);
            $event_tax_total = $calculatedTax;
        } else {
            $event_tax_total = 0;
        }

        $cart['event_tax'] = $event_tax_total ?? 0;

        $total += $event_tax_total + $totalServiceFeePercent + $totalServiceFeeFixed + $totalProcessingFeePercent + $totalProcessingFeeFixed + $totalDrinkFees + $totalBottleFees + $totalVipFees + $totalMobileFee;

        if ($event_tax_total > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Tax',
                    ],
                    'unit_amount' => (int) round($event_tax_total * 100, 0),
                ],
                'quantity' => 1,
            ];
        }

        if ($totalServiceFeePercent > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Service Fee (%)',
                    ],
                    'unit_amount' => (int) round($totalServiceFeePercent * 100, 0),
                ],
                'quantity' => 1,
            ];
        }

        if ($totalServiceFeeFixed > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Service Fee (Fixed)',
                    ],
                    'unit_amount' => (int) round($totalServiceFeeFixed * 100, 0),
                ],
                'quantity' => 1,
            ];
        }

        if ($totalProcessingFeePercent > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Processing Fee (%)',
                    ],
                    'unit_amount' => (int) round($totalProcessingFeePercent * 100, 0),
                ],
                'quantity' => 1,
            ];
        }

        if ($totalProcessingFeeFixed > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Processing Fee (Fixed)',
                    ],
                    'unit_amount' => (int) round($totalProcessingFeeFixed * 100, 0),
                ],
                'quantity' => 1,
            ];
        }

        if ($totalDrinkFees > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Drink Fees',
                    ],
                    'unit_amount' => (int) round($totalDrinkFees * 100, 0),
                ],
                'quantity' => 1,
            ];
        }
        if ($totalBottleFees > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Bottles Fees',
                    ],
                    'unit_amount' => (int) round($totalBottleFees * 100, 0),
                ],
                'quantity' => 1,
            ];
        }
        if ($totalVipFees > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'VIP Package Fees (%)',
                    ],
                    'unit_amount' => (int) round($totalVipFees * 100, 0),
                ],
                'quantity' => 1,
            ];
        }
        if ($totalMobileFee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Mobile Fee',
                    ],
                    'unit_amount' => (int) round($totalMobileFee * 100, 0),
                ],
                'quantity' => 1,
            ];
        }

        $couponAmount = 0;
        if (isset($cart['appliedCoupons']) && is_array($cart['appliedCoupons']) && count($cart['appliedCoupons']) > 0) {
            $baseTicketSubtotal = 0;
            foreach ($newCartItems as $ci) {
                $baseTicketSubtotal += (floatval($ci['price'] ?? 0) * intval($ci['qty'] ?? 0));
            }

            foreach ($cart['appliedCoupons'] as $couponCode) {
                $eventId = $newCartItems[0]['ticket']->event_id;
                $coupon = \App\Models\Coupon::where('code', $couponCode)->where('link_up_event_id', $eventId)->first();
                if ($coupon) {
                    if ($coupon->discount_type === 'percentage') {
                        $discount = ($coupon->discount / 100) * $baseTicketSubtotal;
                    } else {
                        $discount = floatval($coupon->discount);
                    }
                    $discount = min($discount, $baseTicketSubtotal - $couponAmount);
                    if ($discount > 0) {
                        $couponAmount += $discount;
                    }
                }
            }

            if ($couponAmount > 0) {
                $total -= $couponAmount;
            }
        }

        if ($total < 0) {
            $total = 0;
        }

        if ($total > 0 && $couponAmount > 0 && !empty($lineItems) && !empty($ticketLineIndexes)) {
            $remainingCoupon = (int) round($couponAmount * 100, 0);

            $ticketCentsTotal = 0;
            foreach ($ticketLineIndexes as $idx) {
                $li = $lineItems[$idx];
                $ticketCentsTotal += ((int)$li['price_data']['unit_amount']) * ((int)($li['quantity'] ?? 1));
            }
            if ($remainingCoupon > $ticketCentsTotal) {
                $remainingCoupon = $ticketCentsTotal;
            }

            if ($remainingCoupon > 0) {
                foreach ($ticketLineIndexes as $idx) {
                    if ($remainingCoupon <= 0) break;
                    $unit = (int)$lineItems[$idx]['price_data']['unit_amount'];
                    $qty = (int)($lineItems[$idx]['quantity'] ?? 1);
                    $lineTotal = $unit * $qty;
                    if ($lineTotal <= 0) continue;

                    if ($remainingCoupon >= $lineTotal) {
                        $lineItems[$idx]['price_data']['unit_amount'] = 0;
                        $remainingCoupon -= $lineTotal;
                    } else {
                        $newLineTotal = $lineTotal - $remainingCoupon;
                        $newUnit = (int) floor($newLineTotal / max(1, $qty));
                        $lineItems[$idx]['price_data']['unit_amount'] = max(0, $newUnit);
                        $remainingCoupon = 0;
                    }
                }
            }
        }

        if ($total > 0 && empty($lineItems)) {
            return back()->withErrors(['payment' => 'No valid items found for payment. Please check your cart.']);
        }
        $stripeKey = config('services.stripe.secret');
        if (!$stripeKey) {
            return back()->withErrors(['payment' => 'Payment configuration error']);
        }

        Stripe::setApiKey($stripeKey);

        try {
            $eventId = $newCartItems[0]['ticket']->event_id;

            if (empty($lineItems) && $total > 0) {
                return back()->withErrors(['payment' => 'Payment configuration error. Please contact support.']);
            }

            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems ?? [],
                'mode' => 'payment',
                'success_url' => route('frontend.stripe.buy-ticket.success') . '?checkout_session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('frontend.event.tickets', ['event' => $eventId]),
                'client_reference_id' => Str::random(20),
                'metadata' => [
                    'user_id' => Auth::id(),
                    'event_id' => $eventId,
                    'coupon_amount' => $couponAmount,
                    'wellness_included_service' => $item['wellness_included_service'] ?? null,
                ],
            ]);

            // Replace cart items with the processed array
            $cart['items'] = $newCartItems;
            $cart['coupon_amount'] = $couponAmount;
            session()->put('ticket_cart', $cart);

            return Inertia::location($checkoutSession->url);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->withErrors(['payment' => 'Failed to initiate payment: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['payment' => 'Unexpected error during payment initiation: ' . $e->getMessage()]);
        }
    }

    public function buyTicketSuccess(Request $request)
    {
        $cart = session()->get('ticket_cart');

        if (!$cart) {
            return redirect('/')->withErrors(['cart' => 'Invalid session data']);
        }
        $event_tax = $cart['event_tax'] ?? 0;

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $checkoutSession = \Stripe\Checkout\Session::retrieve($request->checkout_session_id);

            if ($checkoutSession->payment_status !== 'paid') {

                return redirect()->route('frontend.event.tickets', ['event' => $cart['items'][0]['ticket']->event_id])
                    ->withErrors(['payment' => 'Payment not completed']);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {

            return redirect()->route('frontend.event.tickets', ['event' => $cart['items'][0]['ticket']->event_id])
                ->withErrors(['payment' => 'Payment verification failed']);
        }

        $eventFeeSettings = EventFeeSetting::first();

        $eventId = $cart['items'][0]['ticket']->event_id;
        $event = LinkUpEvent::with('eventDetails')->find($eventId);
        $taxIncluded = $event?->eventDetails?->tax_included === 'yes';

        $ticketIds = [];
        $couponAmount = $cart['coupon_amount'] ?? 0;

        $summaryServiceFee = 0;
        $summaryProcessingFee = 0;
        $summaryDrinkFees = 0;
        $summaryBottleFees = 0;
        $summaryVipFees = 0;
        $summaryCookoutFees = 0;

        DB::beginTransaction();
        try {
            $paidCartSubtotal = 0;
            $vipTicketSubtotal = 0;
            $standardTicketSubtotal = 0;

            $orderServiceFeePercent = 0;
            $orderServiceFeeFixed = 0;
            $orderProcessingFeePercent = 0;
            $orderProcessingFeeFixed = 0;
            $orderDrinkFees = 0;
            $orderBottleFees = 0;
            $orderVipFees = 0;

            $totalMobileFee = 0;
            foreach ($cart['items'] as $item) {
                $ticket = $item['ticket'] ?? null;
                if (!$ticket) continue;
                $wellnessConfig = $ticket->wellness;
                if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                    $booking = $wellnessConfig['booking'] ?? null;
                    $selectedMode = isset($item['wellness']['serviceMode']) ? $item['wellness']['serviceMode'] : 'inhouse';
                    if (is_array($booking) && isset($booking['mobileFee'])) {
                        $fee = floatval($booking['mobileFee'] ?? 0);
                        if ($fee > 0 && $selectedMode === 'mobile') {
                            $totalMobileFee += $fee * $item['qty'];
                        }
                    }
                }
            }

            foreach ($cart['items'] as $item) {
                $paidCartSubtotal += ($item['price'] * $item['qty']) + ($item['addon_total'] ?? 0);

                $ticket = $item['ticket'];
                $typeStr = strtolower(trim((string)($ticket->type ?? '')));
                $ticketTypeStr = strtolower(trim((string)($ticket->ticket_type ?? '')));
                $isDrinkOnlyTicket = str_contains($typeStr, 'drink')
                    || str_contains($ticketTypeStr, 'drink')
                    || (intval($item['qty'] ?? 0) <= 0 && floatval($item['drinks_total'] ?? 0) > 0 && floatval($item['tables_total'] ?? 0) <= 0);
                $cookoutConfig = $ticket->cookout;
                if (is_string($cookoutConfig)) {
                    $decodedCookout = json_decode($cookoutConfig, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $cookoutConfig = $decodedCookout;
                    }
                }
                // Don't set $ticket->cookout - it causes save() to try to persist to DB
                $wellnessConfig = $ticket->wellness ? (is_string($ticket->wellness) ? json_decode($ticket->wellness, true) : $ticket->wellness) : null;
                $hasPackage = !empty($ticket->package_id) && !empty($ticket->drinkPackage) && ($ticket->has_table === 'yes');

                $hasExplicitTableAddon = false;
                if (isset($item['addons']) && is_array($item['addons'])) {
                    foreach ($item['addons'] as $addonData) {
                        if (isset($addonData['category']) && $addonData['category'] === 'table') {
                            $hasExplicitTableAddon = true;
                            break;
                        }
                    }
                }

                $isTableSelected = $hasExplicitTableAddon || $hasPackage;
                if ($isDrinkOnlyTicket) {
                    // Drink-only ticket types should not be included in service/processing/vip fee base
                    // They only pay drink/bottle fees.
                    goto after_ticket_fee_base;
                }
                if ($isTableSelected) {
                    $tableUnit = floatval($ticket->table_price ?? 0);
                    $ticketUnit = $item['price'];

                    if ($tableUnit > 0 && $tableUnit !== $ticketUnit) {
                        $vipTicketSubtotal += $tableUnit * $item['qty'];
                    } else {
                        $vipTicketSubtotal += $ticketUnit * $item['qty'];
                    }
                } else {
                    $standardTicketSubtotal += ($item['price'] * $item['qty']);
                }

                after_ticket_fee_base:

                if (isset($item['drink_addons_json']) && is_array($item['drink_addons_json'])) {
                    foreach ($item['drink_addons_json'] as $drink) {
                        $drinkFeePct = floatval($eventFeeSettings->drink_fee_pct ?? 0);
                        $bottleFeePct = floatval($eventFeeSettings->bottle_fee_pct ?? 0);
                        $drinkTotalPrice = floatval($drink['total_price']);

                        $category = $drink['category'] ?? '';
                        $isBottleType = ($category === 'bottles');
                        $isRegularDrinkType = in_array($category, ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks']);

                        if ($isRegularDrinkType) {
                            $orderDrinkFees += $drinkTotalPrice * ($drinkFeePct / 100);
                        }
                        if ($isBottleType) {
                            $orderBottleFees += $drinkTotalPrice * ($bottleFeePct / 100);
                        }
                    }
                }

                // cookout_total is already included in addon_total; no extra platform fees are applied.
            }

            if ($standardTicketSubtotal > 0 && $eventFeeSettings) {
                $orderServiceFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->service_fee_pct ?? 0) / 100);
                $orderServiceFeeFixed = floatval($eventFeeSettings->service_fee_fixed ?? 0);
                $orderProcessingFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->processing_fee_pct ?? 0) / 100);
                $orderProcessingFeeFixed = floatval($eventFeeSettings->processing_fee_fixed ?? 0);
            }

            if ($vipTicketSubtotal > 0 && $eventFeeSettings) {
                $vipPct = floatval($eventFeeSettings->vip_fee_pct ?? 0) / 100;
                $orderVipFees = $vipTicketSubtotal * $vipPct;
            }

            $summaryServiceFee = $orderServiceFeePercent + $orderServiceFeeFixed;
            $summaryProcessingFee = $orderProcessingFeePercent + $orderProcessingFeeFixed;
            $summaryDrinkFees = $orderDrinkFees;
            $summaryBottleFees = $orderBottleFees;
            $summaryVipFees = $orderVipFees;
            $summaryCookoutFees = 0;
            $isFirstTicket = true;
            foreach ($cart['items'] as $item) {
                $addonTotal = $item['addon_total'] ?? 0;
                $typeStr = strtolower(trim((string)($item['ticket']->type ?? '')));
                $ticketTypeStr = strtolower(trim((string)($item['ticket']->ticket_type ?? '')));
                $isDrinkOnlyTicket = str_contains($typeStr, 'drink')
                    || str_contains($ticketTypeStr, 'drink')
                    || (intval($item['qty'] ?? 0) <= 0 && floatval($item['drinks_total'] ?? 0) > 0 && floatval($item['tables_total'] ?? 0) <= 0);
                $hasSelectedTable = false;
                if (!empty($item['table_addons_json']) && is_array($item['table_addons_json'])) {
                    foreach ($item['table_addons_json'] as $tableAddon) {
                        if (intval($tableAddon['quantity'] ?? 0) > 0) {
                            $hasSelectedTable = true;
                            break;
                        }
                    }
                }

                $packageData = null;
                $packageIdToStore = null;
                if ($hasSelectedTable && $item['ticket']->package_id && $item['ticket']->drinkPackage) {
                    $package = $item['ticket']->drinkPackage;
                    $packageIdToStore = $item['ticket']->package_id;
                    $packageData = [
                        'id' => $package->id,
                        'name' => $package->name,
                        'bottles' => $package->bottles ?? [],
                        'chasers' => $package->chasers ?? [],
                        'waters' => $package->waters ?? [],
                        'notes' => $package->notes ?? null,
                        'table_seating' => $item['ticket']->sections ?? null
                    ];
                }

                $qty = intval($item['qty'] ?? 0);
                $createCount = $qty > 0 ? $qty : 1;

                for ($i = 0; $i < $createCount; $i++) {
                    $applyAddonsAndFees = ($i === 0);

                    $uniqueCode = Str::random(20);
                    $options = new QROptions(['eccLevel' => EccLevel::H, 'scale' => 10, 'outputType' => QRCode::OUTPUT_IMAGE_PNG, 'imageBase64' => false,]);
                    $qrcode = new QRCode($options);
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    $pngData = $qrcode->render($uniqueCode);
                    Storage::disk('public')->put('qr-codes/' . $uniqueCode . '.png', $pngData);
                    $qrPath = storage_path('app/public/qr-codes/' . $uniqueCode . '.png');
                    $logoPath = storage_path('app/public/events/logo_for_e_tickets.png');
                    $qr = imagecreatefrompng($qrPath);
                    if (file_exists($logoPath)) {
                        $logo = imagecreatefrompng($logoPath);
                        $qrWidth = imagesx($qr);
                        $qrHeight = imagesy($qr);
                        $logoWidth = imagesx($logo);
                        $logoHeight = imagesy($logo);
                        $newLogoWidth = $qrWidth * 0.60;
                        $newLogoHeight = ($logoHeight / $logoWidth) * $newLogoWidth;
                        $logoResized = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
                        imagealphablending($logoResized, false);
                        imagesavealpha($logoResized, true);
                        imagecopyresampled($logoResized, $logo, 0, 0, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);
                        $x = ($qrWidth - $newLogoWidth) / 2;
                        $y = ($qrHeight - $newLogoHeight) / 2;
                        imagecopy($qr, $logoResized, $x, $y, 0, 0, $newLogoWidth, $newLogoHeight);
                        imagepng($qr, $qrPath);
                        imagedestroy($logo);
                        imagedestroy($logoResized);
                    }
                    imagedestroy($qr);


                    $dbTicketName = $item['ticket']->name;
                    $wellnessConfig = $item['ticket']->wellness ? (is_string($item['ticket']->wellness) ? json_decode($item['ticket']->wellness, true) : $item['ticket']->wellness) : null;
                    if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                        $dbTicketName .= ' (Wellness)';
                    }

                    $fullFeeBreakdown = ($eventFeeSettings) ? [
                        'service_fee_pct_rate' => floatval($eventFeeSettings->service_fee_pct ?? 0),
                        'service_fee_pct_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderServiceFeePercent ?? 0) : 0,
                        'service_fee_fixed' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderServiceFeeFixed ?? 0) : 0,
                        'processing_fee_pct_rate' => floatval($eventFeeSettings->processing_fee_pct ?? 0),
                        'processing_fee_pct_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderProcessingFeePercent ?? 0) : 0,
                        'processing_fee_fixed' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderProcessingFeeFixed ?? 0) : 0,
                        'vip_package_fee_pct_rate' => floatval($eventFeeSettings->vip_fee_pct ?? 0),
                        'vip_package_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderVipFees ?? 0) : 0,
                        'mobile_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($totalMobileFee ?? 0) : 0,
                    ] : null;

                    $ticketFeeBreakdown = null;
                    if ($eventFeeSettings) {
                        if ($hasSelectedTable) {
                            $ticketFeeBreakdown = [
                                'vip_package_fee_pct_rate' => floatval($eventFeeSettings->vip_fee_pct ?? 0),
                                'vip_package_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderVipFees ?? 0) : 0,
                            ];
                        } elseif ($isDrinkOnlyTicket) {
                            $ticketFeeBreakdown = [
                                'drink_fee_pct_rate' => floatval($eventFeeSettings->drink_fee_pct ?? 0),
                                'drink_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderDrinkFees ?? 0) : 0,
                                'bottle_fee_pct_rate' => floatval($eventFeeSettings->bottle_fee_pct ?? 0),
                                'bottle_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderBottleFees ?? 0) : 0,
                            ];
                        } else {
                            $ticketFeeBreakdown = $this->filterFeeBreakdownByTicketType($item['ticket'], $fullFeeBreakdown);
                            if (floatval($item['drinks_total'] ?? 0) > 0) {
                                $ticketFeeBreakdown = [
                                    'drink_fee_pct_rate' => floatval($eventFeeSettings->drink_fee_pct ?? 0),
                                    'drink_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderDrinkFees ?? 0) : 0,
                                    'bottle_fee_pct_rate' => floatval($eventFeeSettings->bottle_fee_pct ?? 0),
                                    'bottle_fee_amount' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderBottleFees ?? 0) : 0,
                                ];
                            }
                        }
                    }
                    $ticketSale = TicketSale::create([
                        'ticket_id' => $item['ticket']->id,
                        'ticket_type' => $item['ticket']->type,
                        'ticket_name' => $dbTicketName,
                        'ticket_qrcode' => $uniqueCode,
                        'ticket_qrcode_id' => $uniqueCode,
                        'no_of_tickets' => $qty > 0 ? 1 : 0,
                        'user_id' => auth()->id(),
                        'link_up_event_id' => $item['ticket']->event_id,
                        'ticket_status' => 'confirmed',
                        'payment_method' => 'stripe',
                        'pay_type' => 'online',
                        'fee' => ($isFirstTicket && $applyAddonsAndFees && !$isDrinkOnlyTicket) ? ($orderServiceFeePercent + $orderServiceFeeFixed) : 0,
                        'discount' => ($isFirstTicket && $applyAddonsAndFees) ? $couponAmount : 0,
                        'tax' => ($isFirstTicket && $applyAddonsAndFees && !$isDrinkOnlyTicket) ? ($orderProcessingFeePercent + $orderProcessingFeeFixed) : 0,
                        'coupan_amount' => ($isFirstTicket && $applyAddonsAndFees) ? $couponAmount : 0,
                        'drink_fees' => ($isFirstTicket && $applyAddonsAndFees) ? ($orderDrinkFees + $orderBottleFees) : 0,
                        'drinks_total' => $applyAddonsAndFees ? ($item['drinks_total'] ?? 0) : 0,
                        'tables_total' => $applyAddonsAndFees ? ($item['tables_total'] ?? 0) : 0,
                        'drink_addons' => $applyAddonsAndFees && !empty($item['drink_addons_json']) ? $item['drink_addons_json'] : null,
                        'table_addons' => $applyAddonsAndFees && !empty($item['table_addons_json']) ? $item['table_addons_json'] : null,
                        'cookout_included_protein' => ($applyAddonsAndFees && !empty($item['cookout_selection']) && is_array($item['cookout_selection'])) ? ($item['cookout_selection']['includedProtein'] ?? null) : null,
                        'cookout_included_sides' => ($applyAddonsAndFees && !empty($item['cookout_selection']) && is_array($item['cookout_selection'])) ? ($item['cookout_selection']['includedSides'] ?? null) : null,
                        'cookout_addons' => $applyAddonsAndFees && !empty($item['cookout_addons_json']) ? $item['cookout_addons_json'] : null,
                        'cookout_total' => $applyAddonsAndFees ? floatval($item['cookout_total'] ?? 0) : 0,
                        'cookout_selection' => $applyAddonsAndFees && !empty($item['cookout_selection']) ? $item['cookout_selection'] : null,
                        'wellness_addons' => $applyAddonsAndFees && !empty($item['wellness_addons_json']) ? $item['wellness_addons_json'] : null,
                        'wellness_total' => $applyAddonsAndFees ? floatval($item['wellness_total'] ?? 0) : 0,
                        'wellness_slot_block_id' => $applyAddonsAndFees && !empty($item['wellness_slot_block_id']) ? $item['wellness_slot_block_id'] : null,
                        'package_id' => $packageIdToStore,
                        'package_data' => $packageData,
                        'sub_total' => ($qty > 0 ? $item['price'] : 0),
                        'total' => ($qty > 0 ? $item['price'] : 0) + ($applyAddonsAndFees ? $addonTotal : 0) + (($isFirstTicket && $applyAddonsAndFees) ? $event_tax : 0),
                        'stripe_id' => $checkoutSession->payment_intent,
                        'stripe_price' => ($qty > 0 ? $item['price'] : 0) + ($applyAddonsAndFees ? $addonTotal : 0) + (($isFirstTicket && $applyAddonsAndFees) ? ($orderServiceFeePercent + $orderServiceFeeFixed + $orderProcessingFeePercent + $orderProcessingFeeFixed + $orderDrinkFees + $orderBottleFees + $orderVipFees + $totalMobileFee + $event_tax - $couponAmount) : 0),
                        'stripe_status' => $checkoutSession->status,
                        'web_qrcode' => 'qr-codes/' . $uniqueCode . '.png',
                        'fee_breakdown' => $ticketFeeBreakdown,
                        'event_tax' => ($taxIncluded && !$isDrinkOnlyTicket) ? ($event_tax ?? 0) : 0,

                    ]);

                    // Decrement wellness slot count if applicable
                    if (!empty($item['wellness_slot_block_id'])) {
                        $block = \App\Models\WellnessSlotBlock::find($item['wellness_slot_block_id']);
                        if ($block && $block->count > 0) {
                            $block->decrement('count');
                        }
                    }

                    if ($applyAddonsAndFees && isset($item['addons']) && is_array($item['addons'])) {
                        foreach ($item['addons'] as $addonData) {
                            if (isset($addonData['category']) && $addonData['category'] === 'table') {
                                $section = $addonData['section'] ?? '-';
                                $unit = floatval($item['ticket']->table_price ?? 0);
                                $tableQty = intval($addonData['quantity'] ?? 0);

                                TicketSaleAddon::create([
                                    'ticket_sale_id' => $ticketSale->id,
                                    'addon_name' => 'Table - ' . $section,
                                    'quantity' => $tableQty,
                                    'unit_price' => $unit,
                                    'total_price' => $unit * $tableQty,
                                    'category' => 'table',
                                ]);

                                if ($tableQty > 0) {
                                    $item['ticket']->table_capacity = max(0, $item['ticket']->table_capacity - $tableQty);
                                    $item['ticket']->save();
                                }
                                continue;
                            }
                            if (!isset($addonData['addon_id'])) continue;
                            $addon = $this->findAddonInTicket($item['ticket'], $addonData['addon_id']);
                            if ($addon) {
                                $drinkFeePctRate = floatval($eventFeeSettings->drink_fee_pct ?? 0);
                                $bottleFeePctRate = floatval($eventFeeSettings->bottle_fee_pct ?? 0);
                                $vipFeePctRate = floatval($eventFeeSettings->vip_fee_pct ?? 0);
                                $lineTotal = floatval($addon['cost']) * $addonData['quantity'];
                                $isBottleType = isset($addon['category']) && in_array($addon['category'], ['wines', 'beers']);
                                $lineDrinkFee = $lineTotal * ($drinkFeePctRate / 100);
                                $lineBottleFee = $isBottleType ? ($lineTotal * ($bottleFeePctRate / 100)) : 0;
                                $lineVipFee = 0;

                                TicketSaleAddon::create([
                                    'ticket_sale_id' => $ticketSale->id,
                                    'addon_name' => $addon['name'],
                                    'quantity' => $addonData['quantity'],
                                    'unit_price' => floatval($addon['cost']),
                                    'total_price' => floatval($addon['cost']) * $addonData['quantity'],
                                    'category' => $addon['category'],
                                    'addon_fee_breakdown' => $eventFeeSettings ? [
                                        'drink_fee_pct_rate' => $drinkFeePctRate,
                                        'drink_fee_amount' => $lineDrinkFee,
                                        'bottle_fee_pct_rate' => $bottleFeePctRate,
                                        'bottle_fee_amount' => $lineBottleFee,
                                        'vip_package_fee_pct_rate' => $vipFeePctRate,
                                        'vip_package_fee_amount' => $lineVipFee,
                                    ] : null,
                                ]);
                                $this->updateAddonInventory($item['ticket'], $addonData['addon_id'], $addonData['quantity']);
                            }
                        }
                    }

                    if ($applyAddonsAndFees && !empty($item['cookout_addons_json']) && is_array($item['cookout_addons_json'])) {
                        foreach ($item['cookout_addons_json'] as $addon) {
                            $qty = intval($addon['quantity'] ?? 0);
                            $unit = floatval($addon['unit_price'] ?? 0);
                            if ($qty <= 0) {
                                continue;
                            }
                            TicketSaleAddon::create([
                                'ticket_sale_id' => $ticketSale->id,
                                'addon_name' => $addon['name'] ?? 'Cookout Add-on',
                                'quantity' => $qty,
                                'unit_price' => $unit,
                                'total_price' => $unit * $qty,
                                'category' => $addon['category'] ?? 'cookout',
                            ]);
                        }
                    }

                    if ($applyAddonsAndFees && !empty($item['wellness_addons_json']) && is_array($item['wellness_addons_json'])) {
                        foreach ($item['wellness_addons_json'] as $addon) {
                            $qty = intval($addon['quantity'] ?? 0);
                            $unit = floatval($addon['unit_price'] ?? 0);
                            if ($qty <= 0) {
                                continue;
                            }
                            TicketSaleAddon::create([
                                'ticket_sale_id' => $ticketSale->id,
                                'addon_name' => $addon['name'] ?? 'Wellness Add-on',
                                'quantity' => $qty,
                                'unit_price' => $unit,
                                'total_price' => $unit * $qty,
                                'category' => $addon['category'] ?? 'wellness',
                            ]);
                        }
                    }

                    if (!empty($item['wellness_included_service'])) {
                        TicketSaleAddon::create([
                            'ticket_sale_id' => $ticketSale->id,
                            'addon_name' => $item['wellness_included_service'] . ' (Included Service)',
                            'quantity' => 1,
                            'unit_price' => 0,
                            'total_price' => 0,
                            'category' => 'wellness_included_service',
                        ]);
                    }

                    if (
                        $applyAddonsAndFees
                        && !empty($item['cookout_selection'])
                        && is_array($item['cookout_selection'])
                        && !empty($item['cookout_selection']['includedProtein'])
                    ) {
                        TicketSaleAddon::create([
                            'ticket_sale_id' => $ticketSale->id,
                            'addon_name' => $item['cookout_selection']['includedProtein'] . ' (Included Plate)',
                            'quantity' => 1,
                            'unit_price' => 0,
                            'total_price' => 0,
                            'category' => 'cookout_included_plate',
                        ]);
                    }

                    $ticketIds[] = $ticketSale->id;
                    if ($isFirstTicket && $applyAddonsAndFees) {
                        $isFirstTicket = false;
                    }
                }
                if ($qty > 0) {
                    $item['ticket']->quantity = max(0, $item['ticket']->quantity - $qty);
                    $item['ticket']->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('frontend.event.tickets', ['event' => $cart['items'][0]['ticket']->event_id])
                ->withErrors(['cart' => 'Purchase failed: ' . $e->getMessage()]);
        }
        session()->forget('ticket_cart');

        $app_url = config('app.url');
        $ticketSales = TicketSale::whereIn('id', $ticketIds)->get();
        $event = LinkUpEvent::with('category', 'authUserFavorite', 'favourites', 'eventDetails', 'organizer', 'organizer.contacts')->findOrFail($cart['items'][0]['ticket']->event_id);
        $otherEvents = LinkUpEvent::with('category', 'authUserFavorite', 'favourites', 'eventDetails')->where('organizer_id', $event->organizer_id)->take(8)->get();
        $sponsor = Sponsor::where('link_up_event_id', $event->id)->get();
        $organizer = User::find($event->organizer_id);
        $user_data = Auth::user();

        $organizerUserId = null;
        if ($event?->organizer_id && User::whereKey($event->organizer_id)->exists()) {
            $organizerUserId = $event->organizer_id;
        }

        if (!$organizerUserId && $event?->organizer_id) {
            $organizerUserId = OrganizerProfile::whereKey($event->organizer_id)->value('user_id');
        }

        if (!$organizerUserId && $event?->user_id && User::whereKey($event->user_id)->exists()) {
            $organizerUserId = $event->user_id;
        }

        if ($organizerUserId && !User::whereKey($organizerUserId)->exists()) {
            $organizerUserId = null;
        }

        if ($event && $organizerUserId) {
            $totalTickets = (int) $ticketSales->sum('no_of_tickets');
            if ($totalTickets <= 0) {
                $totalTickets = (int) $ticketSales->count();
            }

            Notification::create([
                'title' => 'New ticket purchase',
                'message' => ($user_data?->name ?? 'Someone') . " purchased {$totalTickets} ticket(s) for \"{$event->title}\".",
                'send_by' => (string) (Auth::id() ?? ''),
                'user_id' => $organizerUserId,
                'type' => 'payment',
                'context' => 'ticket_purchase',
                'unread' => true,
                'avatar' => $user_data?->avatar ?? null,
                'metadata' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'ticket_sale_ids' => $ticketIds,
                    'tickets_count' => $totalTickets,
                    'amount_paid' => (float) $ticketSales->sum('stripe_price'),
                    'payment_method' => 'stripe',
                ],
            ]);
        }

        $wellnessTickets = collect();
        $cookoutTickets = collect();
        $standardTickets = collect();

        foreach ($ticketSales as $ticket) {
            $type = strtolower(trim((string)($ticket->ticket_type ?? '')));
            
            if (str_contains($type, 'wellness') || str_contains($type, 'spa')) {
                $wellnessTickets->push($ticket);
            }
            elseif (str_contains($type, 'cookouts/food') || str_contains($type, 'cookouts') || str_contains($type, 'cookout')) {
                $cookoutTickets->push($ticket);
            }
            else {
                $standardTickets->push($ticket);
            }
        }

        $buyerEmail = $user_data?->email ?? Auth::user()?->email;

        if ($wellnessTickets->isNotEmpty()) {
            if ($buyerEmail) {
                Mail::to($buyerEmail)->queue(new BuyWellnessTicketEmailMail($wellnessTickets, $event, $app_url, $user_data, $sponsor));
            }
        }
        
        if ($cookoutTickets->isNotEmpty()) {
            if ($buyerEmail) {
                Mail::to($buyerEmail)->queue(new BuyCookoutTicketEmailMail($cookoutTickets, $event, $app_url, $user_data, $sponsor));
            }
        }
        
        if ($standardTickets->isNotEmpty()) {
            if ($buyerEmail) {
                Mail::to($buyerEmail)->queue(new BuyTicketEmailMail($standardTickets, $event, $app_url, $user_data, $sponsor));
            }
        }

        return Inertia::render('User/Event/SuccessPurchase', [
            'event' => $event,
            'ticketSale' => $ticketSales,
            'otherEvents' => $otherEvents,
            'user_data' => $user_data,
            'app_url' => $app_url,
            'sponsor' => $sponsor,
            'organizer' => $organizer,
            'fees_summary' => [
                'service_fee_total' => $summaryServiceFee,
                'processing_fee_total' => $summaryProcessingFee,
                'drink_fees_total' => $summaryDrinkFees,
                'bottle_fees_total' => $summaryBottleFees,
                'vip_fees_total' => $summaryVipFees,
                'cookout_fees_total' => $summaryCookoutFees,
                'mobile_fee_total' => $totalMobileFee ?? 0,
                'coupon_amount' => $cart['coupon_amount'] ?? 0,
            ],
        ]);
    }

    /**
     * Find addon in ticket's drink_addons by ID
     */
    private function findAddonInTicket($ticket, $addonId)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }

        $categories = $ticket->drink_addons['items'];
        $idCounter = 1;

        foreach ($categories as $category => $items) {
            foreach ($items as $item) {
                if ($idCounter == $addonId) {
                    return [
                        'id' => $idCounter,
                        'name' => $item['name'],
                        'cost' => $item['cost'],
                        'qty' => $item['qty'],
                        'price' => floatval($item['cost']) / $item['qty'],
                        'category' => $category,
                    ];
                }
                $idCounter++;
            }
        }

        return null;
    }

    /**
     * Legacy resolver: search Ticket JSON columns main_bottles, chasers_or_mixers, water_options by ID
     */
    private function findLegacyAddonById($ticket, int $id)
    {
        $main = is_array($ticket->main_bottles)
            ? $ticket->main_bottles
            : (is_string($ticket->main_bottles) ? json_decode($ticket->main_bottles, true) : []);
        $chasers = is_array($ticket->chasers_or_mixers)
            ? $ticket->chasers_or_mixers
            : (is_string($ticket->chasers_or_mixers) ? json_decode($ticket->chasers_or_mixers, true) : []);
        $waters = is_array($ticket->water_options)
            ? $ticket->water_options
            : (is_string($ticket->water_options) ? json_decode($ticket->water_options, true) : []);

        if (!empty($main) && is_array($main)) {
            foreach ($main as $b) {
                if (isset($b['id']) && intval($b['id']) === $id) {
                    return [
                        'name' => $b['name'] ?? 'Bottle',
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($chasers) && is_array($chasers)) {
            foreach ($chasers as $c) {
                if (isset($c['id']) && intval($c['id']) === $id) {
                    return [
                        'name' => $c['name'] ?? 'Mixer',
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($waters) && is_array($waters)) {
            foreach ($waters as $w) {
                if (isset($w['id']) && intval($w['id']) === $id) {
                    return [
                        'name' => $w['name'] ?? 'Water',
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Legacy resolver: search Ticket JSON columns main_bottles, chasers_or_mixers, water_options by name
     */
    private function findLegacyAddonByName($ticket, string $name)
    {
        $search = trim($name);
        $main = is_array($ticket->main_bottles)
            ? $ticket->main_bottles
            : (is_string($ticket->main_bottles) ? json_decode($ticket->main_bottles, true) : []);
        $chasers = is_array($ticket->chasers_or_mixers)
            ? $ticket->chasers_or_mixers
            : (is_string($ticket->chasers_or_mixers) ? json_decode($ticket->chasers_or_mixers, true) : []);
        $waters = is_array($ticket->water_options)
            ? $ticket->water_options
            : (is_string($ticket->water_options) ? json_decode($ticket->water_options, true) : []);

        if (!empty($main) && is_array($main)) {
            foreach ($main as $b) {
                if (isset($b['name']) && strcasecmp(trim($b['name']), $search) === 0) {
                    return [
                        'name' => $b['name'],
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($chasers) && is_array($chasers)) {
            foreach ($chasers as $c) {
                if (isset($c['name']) && strcasecmp(trim($c['name']), $search) === 0) {
                    return [
                        'name' => $c['name'],
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($waters) && is_array($waters)) {
            foreach ($waters as $w) {
                if (isset($w['name']) && strcasecmp(trim($w['name']), $search) === 0) {
                    return [
                        'name' => $w['name'],
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Find addon in ticket's drink_addons by name and category key (e.g., mixDrinks, bottles)
     */
    private function findAddonByNameAndCategory($ticket, $name, $categoryKey)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }
        $items = $ticket->drink_addons['items'];
        if (!isset($items[$categoryKey]) || !is_array($items[$categoryKey])) {
            return null;
        }
        foreach ($items[$categoryKey] as $addon) {
            if (isset($addon['name']) && strcasecmp(trim($addon['name']), trim($name)) === 0) {
                return $addon;
            }
        }
        return null;
    }

    /**
     * Find addon by name across all categories in ticket->drink_addons
     */
    private function findAddonByNameAnyCategory($ticket, $name)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }
        $search = trim($name);
        foreach ($ticket->drink_addons['items'] as $category => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $addon) {
                if (isset($addon['name']) && strcasecmp(trim($addon['name']), $search) === 0) {
                    if (!isset($addon['category'])) {
                        $addon['category'] = $category;
                    }
                    return $addon;
                }
            }
        }
        return null;
    }

    /**
     * Fallback: find addon by name in ticket->drinkPackage (bottles/chasers/waters)
     */
    private function findAddonInPackageByName($ticket, $name)
    {
        if (empty($ticket->drinkPackage)) {
            return null;
        }
        $search = trim($name);
        $pkg = $ticket->drinkPackage;
        if (!empty($pkg->bottles) && is_array($pkg->bottles)) {
            foreach ($pkg->bottles as $b) {
                if (isset($b['name']) && strcasecmp(trim($b['name']), $search) === 0) {
                    return [
                        'name' => $b['name'],
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($pkg->chasers) && is_array($pkg->chasers)) {
            foreach ($pkg->chasers as $c) {
                if (isset($c['name']) && strcasecmp(trim($c['name']), $search) === 0) {
                    return [
                        'name' => $c['name'],
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($pkg->waters) && is_array($pkg->waters)) {
            foreach ($pkg->waters as $w) {
                if (isset($w['name']) && strcasecmp(trim($w['name']), $search) === 0) {
                    return [
                        'name' => $w['name'],
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Update addon inventory in ticket's drink_addons
     */
    private function updateAddonInventory($ticket, $addonId, $quantity)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return;
        }

        $drinkAddons = $ticket->drink_addons;
        $categories = $drinkAddons['items'];
        $idCounter = 1;
        $updated = false;

        foreach ($categories as $category => $items) {
            foreach ($items as $index => $item) {
                if ($idCounter == $addonId) {
                    $drinkAddons['items'][$category][$index]['qty'] = max(0, $item['qty'] - $quantity);
                    $updated = true;

                    $ticket->drink_addons = $drinkAddons;
                    $ticket->save();

                    return;
                }
                $idCounter++;
            }
        }
    }
}
