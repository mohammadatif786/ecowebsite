<?php

namespace App\Actions;

use App\Exceptions\TicketCartValidationException;
use App\Models\Coupon;
use App\Models\EventFeeSetting;
use App\Models\LinkUpEvent;
use App\Models\Ticket;
use App\Models\WellnessSlotBlock;
use App\Services\TaxRateService;
use App\Services\TicketAddonResolver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Validates and prices a ticket cart: resolves tickets/addons/cookout/wellness
 * selections, then computes the same 4-part fee structure (service,
 * processing, vip, mobile) + tax + coupon that Frontend\WalletController and
 * Frontend\StripeController each compute inline. Those two controllers had
 * drifted slightly (e.g. whether fees are taxed depends on tax_fees state
 * rule); this action follows StripeController's version, which is the more
 * complete of the two.
 *
 * Payment-method-specific work (wallet transfer, Stripe line items/session)
 * happens in the caller — this only prices the cart and throws
 * TicketCartValidationException for anything a real checkout would reject.
 */
class BuildTicketCartAction
{
    public function __construct(private readonly TicketAddonResolver $addons) {}

    public function handle(array $cart): array
    {
        $ticketIds = array_column($cart['items'], 'ticketId');
        $tickets = Ticket::whereIn('id', $ticketIds)->with(['drinkPackage', 'extraSetting'])->get();

        if ($tickets->count() !== count($ticketIds)) {
            throw new TicketCartValidationException('cart', 'One or more tickets are invalid');
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
        if (!$event) {
            throw new TicketCartValidationException('cart', 'Event not found for ticket');
        }
        $taxIncluded = $event->eventDetails?->tax_included === 'yes';

        $country = $event->organizer?->contacts?->country;
        $currency = function_exists('country_to_currency') ? country_to_currency($country) : 'usd';

        $cartSubtotal = 0;
        $vipTicketSubtotal = 0;
        $standardTicketSubtotal = 0;
        $totalDrinkFees = 0;
        $totalBottleFees = 0;
        $totalMobileFee = 0;
        $eventTaxRate = 0;
        $shouldTaxFees = false;
        $hasNoSalesTax = false;

        $newCartItems = [];

        foreach ($cart['items'] as $item) {
            $ticket = $tickets->firstWhere('id', $item['ticketId']);
            if (!$ticket) {
                throw new TicketCartValidationException((string) $item['ticketId'], 'Ticket not found');
            }
            if (($item['qty'] ?? 0) > 0 && $ticket->quantity < $item['qty']) {
                throw new TicketCartValidationException($ticket->name, 'Ticket is sold out or insufficient quantity');
            }

            $item['ticket'] = $ticket;
            $base = floatval($ticket->price ?? 0);
            $promo = floatval($ticket->promo_price ?? 0);
            $discounted = $base - (is_nan($promo) ? 0 : $promo);
            if ($discounted < 0) {
                $discounted = 0;
            }
            $item['price'] = $discounted;

            $cookoutConfig = $ticket->cookout;
            if (is_string($cookoutConfig)) {
                $decoded = json_decode($cookoutConfig, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $cookoutConfig = $decoded;
                }
            }

            [$cookoutTotal, $cookoutAddonsJson, $cookoutSelectionJson] = $this->resolveCookout(
                $cookoutConfig,
                $item['cookout'] ?? null
            );
            $item['cookout_total'] = $cookoutTotal;
            $item['cookout_addons_json'] = $cookoutAddonsJson;
            $item['cookout_selection'] = $cookoutSelectionJson;

            $wellnessConfig = $ticket->wellness;
            if (is_string($wellnessConfig)) {
                $decoded = json_decode($wellnessConfig, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $wellnessConfig = $decoded;
                }
            }
            $wellnessInput = $item['wellness'] ?? null;

            if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                $booking = $wellnessConfig['booking'] ?? null;
                $selectedMode = is_array($wellnessInput) ? ($wellnessInput['serviceMode'] ?? 'inhouse') : 'inhouse';
                if (is_array($booking) && isset($booking['mobileFee'])) {
                    $fee = floatval($booking['mobileFee'] ?? 0);
                    if ($fee > 0 && $selectedMode === 'mobile') {
                        $totalMobileFee += $fee * $item['qty'];
                    }
                }
            }

            [$wellnessTotal, $wellnessAddonsJson, $wellnessIncludedService, $wellnessSlotBlockId] = $this->resolveWellness(
                $ticket,
                $wellnessConfig,
                $wellnessInput
            );
            $item['wellness_total'] = $wellnessTotal;
            $item['wellness_addons_json'] = $wellnessAddonsJson;
            $item['wellness_included_service'] = $wellnessIncludedService;
            if ($wellnessSlotBlockId) {
                $item['wellness_slot_block_id'] = $wellnessSlotBlockId;
            }

            [$tablesTotal, $drinksTotal, $drinkAddonsJson, $tableAddonsJson, $itemDrinkFees, $itemBottleFees, $hasPackage, $hasExplicitTableAddon] =
                $this->resolveDrinksAndTables($ticket, $item, $discounted, $eventFeeSettings);
            $totalDrinkFees += $itemDrinkFees;
            $totalBottleFees += $itemBottleFees;

            $item['tables_total'] = $tablesTotal;
            $item['drinks_total'] = $drinksTotal;
            $item['addon_total'] = $tablesTotal + $drinksTotal + $cookoutTotal + $wellnessTotal;
            $item['drink_addons_json'] = $drinkAddonsJson;
            $item['table_addons_json'] = $tableAddonsJson;

            $itemSubtotal = ($item['price'] * $item['qty']) + $item['addon_total'];
            $cartSubtotal += $itemSubtotal;

            $typeStr = strtolower(trim((string) ($ticket->type ?? '')));
            $ticketTypeStr = strtolower(trim((string) ($ticket->ticket_type ?? '')));
            $isDrinkOnlyTicket = str_contains($typeStr, 'drink')
                || str_contains($ticketTypeStr, 'drink')
                || (intval($item['qty'] ?? 0) <= 0 && floatval($item['drinks_total'] ?? 0) > 0 && floatval($item['tables_total'] ?? 0) <= 0);
            $item['is_drink_only_ticket'] = $isDrinkOnlyTicket;

            if (!$isDrinkOnlyTicket) {
                $isTableSelected = $hasExplicitTableAddon || $hasPackage;
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
            }

            $newCartItems[] = $item;
        }

        $totalServiceFeePercent = 0;
        $totalServiceFeeFixed = 0;
        $totalProcessingFeePercent = 0;
        $totalProcessingFeeFixed = 0;
        $totalVipFees = 0;

        if ($standardTicketSubtotal > 0 && $eventFeeSettings) {
            $totalServiceFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->service_fee_pct ?? 0) / 100);
            $totalServiceFeeFixed = floatval($eventFeeSettings->service_fee_fixed ?? 0);
            $totalProcessingFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->processing_fee_pct ?? 0) / 100);
            $totalProcessingFeeFixed = floatval($eventFeeSettings->processing_fee_fixed ?? 0);
        }

        if ($vipTicketSubtotal > 0 && $eventFeeSettings) {
            $totalVipFees = $vipTicketSubtotal * (floatval($eventFeeSettings->vip_fee_pct ?? 0) / 100);
        }

        // An event can set its own explicit tax rate (event_details.tax_rate) that
        // overrides the state-lookup rate below — StripeController reads this;
        // WalletController doesn't, which is exactly the kind of drift this shared
        // action exists to eliminate.
        if ($eventTaxRate <= 0) {
            $eventTaxRate = floatval(preg_replace('/[^0-9.]/', '', $event->eventDetails->tax_rate ?? '0'));
        }
        $taxRatePercent = $eventTaxRate;
        $taxService = new TaxRateService();
        foreach ($newCartItems as $item) {
            $ticket = $item['ticket'];
            if ($ticket->event && $ticket->event->state) {
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
            if ($shouldTaxFees) {
                $taxBase += $totalServiceFeePercent + $totalServiceFeeFixed
                    + $totalProcessingFeePercent + $totalProcessingFeeFixed;
            }
            $eventTax = $taxBase * ($taxRatePercent / 100);
        } else {
            $eventTax = 0;
        }

        $orderTotalFees = $totalServiceFeePercent + $totalServiceFeeFixed
            + $totalProcessingFeePercent + $totalProcessingFeeFixed
            + $totalDrinkFees + $totalBottleFees + $totalVipFees + $totalMobileFee + $eventTax;

        $total = $cartSubtotal + $orderTotalFees;

        $couponAmount = 0;
        if (!empty($cart['appliedCoupons']) && is_array($cart['appliedCoupons'])) {
            $baseTicketSubtotal = 0;
            foreach ($newCartItems as $ci) {
                $baseTicketSubtotal += (floatval($ci['price'] ?? 0) * intval($ci['qty'] ?? 0));
            }
            foreach ($cart['appliedCoupons'] as $couponCode) {
                $coupon = Coupon::where('code', $couponCode)->where('link_up_event_id', $eventId)->first();
                if ($coupon) {
                    $discount = $coupon->discount_type === 'percentage'
                        ? ($coupon->discount / 100) * $baseTicketSubtotal
                        : floatval($coupon->discount);
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

        return [
            'items' => $newCartItems,
            'event' => $event,
            'event_fee_settings' => $eventFeeSettings,
            'currency' => $currency,
            'cart_subtotal' => $cartSubtotal,
            'fees' => [
                'service_fee_percent' => $totalServiceFeePercent,
                'service_fee_fixed' => $totalServiceFeeFixed,
                'processing_fee_percent' => $totalProcessingFeePercent,
                'processing_fee_fixed' => $totalProcessingFeeFixed,
                'drink_fees' => $totalDrinkFees,
                'bottle_fees' => $totalBottleFees,
                'vip_fees' => $totalVipFees,
                'mobile_fee' => $totalMobileFee,
                'tax' => $eventTax,
            ],
            'coupon_amount' => $couponAmount,
            'total' => $total,
        ];
    }

    private function resolveCookout(mixed $cookoutConfig, ?array $cookoutInput): array
    {
        $cookoutTotal = 0;
        $cookoutAddonsJson = [];
        $cookoutSelectionJson = null;

        if (!is_array($cookoutConfig) || ($cookoutConfig['includeFood'] ?? null) !== 'yes' || !is_array($cookoutInput)) {
            return [$cookoutTotal, $cookoutAddonsJson, $cookoutSelectionJson];
        }

        $buildAllowed = function (array $source, bool $addonModeOnly = true) {
            $allowed = [];
            foreach ($source as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                if ($addonModeOnly && ($entry['mode'] ?? null) !== 'addon') {
                    continue;
                }
                $name = trim((string) ($entry['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $allowed[strtolower($name)] = [
                    'name' => $name,
                    'price' => floatval($entry['price'] ?? 0),
                    'maxQty' => array_key_exists('qty', $entry) ? intval($entry['qty'] ?? 0) : null,
                ];
            }
            return $allowed;
        };

        $proteinSources = array_merge(
            $cookoutConfig['proteins'] ?? [],
            $cookoutConfig['customProteins'] ?? [],
            $cookoutConfig['custom_proteins'] ?? []
        );
        $allowedProteins = $buildAllowed($proteinSources);
        $allowedManual = $buildAllowed($cookoutConfig['manualAddons'] ?? []);
        $allowedDrinks = $buildAllowed($cookoutConfig['drinks'] ?? []);

        $selectFrom = function (array $selections, array $allowed, string $category) use (&$cookoutTotal, &$cookoutAddonsJson) {
            foreach ($selections as $sel) {
                if (!is_array($sel)) {
                    continue;
                }
                $name = trim((string) ($sel['name'] ?? ''));
                $qty = intval($sel['qty'] ?? 0);
                if ($name === '' || $qty <= 0) {
                    continue;
                }
                $key = strtolower($name);
                if (!isset($allowed[$key])) {
                    throw new TicketCartValidationException('cookout', 'Invalid cookout addon selected');
                }
                $maxQty = $allowed[$key]['maxQty'];
                if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                    throw new TicketCartValidationException('cookout', 'Invalid cookout addon quantity');
                }
                $unitPrice = floatval($allowed[$key]['price']);
                $lineTotal = $unitPrice * $qty;
                $cookoutTotal += $lineTotal;
                $cookoutAddonsJson[] = [
                    'name' => $allowed[$key]['name'],
                    'category' => $category,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }
        };

        $selectFrom($cookoutInput['proteins'] ?? [], $allowedProteins, 'cookout_protein');
        $selectFrom($cookoutInput['manualAddons'] ?? [], $allowedManual, 'cookout_extra');
        $selectFrom($cookoutInput['drinks'] ?? [], $allowedDrinks, 'cookout_drink');

        $cookoutSelectionJson = [
            'includedProtein' => $cookoutInput['includedProtein'] ?? null,
            'includedSides' => (is_array($cookoutConfig['sides'] ?? null)) ? $cookoutConfig['sides'] : [],
        ];

        return [$cookoutTotal, $cookoutAddonsJson, $cookoutSelectionJson];
    }

    private function resolveWellness($ticket, mixed $wellnessConfig, ?array $wellnessInput): array
    {
        $wellnessTotal = 0;
        $wellnessAddonsJson = [];
        $wellnessIncludedService = null;
        $wellnessIncludedServiceType = null;
        $wellnessSlotBlockId = null;

        if (!is_array($wellnessConfig) || ($wellnessConfig['includeService'] ?? null) !== 'yes' || !is_array($wellnessInput)) {
            return [$wellnessTotal, $wellnessAddonsJson, $wellnessIncludedService, $wellnessSlotBlockId];
        }

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
                $block = WellnessSlotBlock::where([
                    'ticket_id' => $ticket->id,
                    'slot_date' => $wellnessSelectedSlotDate,
                    'start_time' => $start,
                    'end_time' => $end,
                ])->first();

                if (!$block || $block->count <= 0) {
                    $wellnessSelectedSlot = null;
                    $wellnessSelectedSlotDate = null;
                } else {
                    $wellnessSlotBlockId = $block->id;
                }
            }
        }

        $includedName = trim((string) ($wellnessInput['includedService'] ?? ''));
        if ($includedName !== '') {
            $allowedIncluded = [];
            foreach (($wellnessConfig['services'] ?? []) as $s) {
                if (!is_array($s) || ($s['mode'] ?? null) === 'addon') {
                    continue;
                }
                $name = trim((string) ($s['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $allowedIncluded[strtolower($name)] = ['name' => $name, 'type' => $s['type'] ?? null];
            }
            $key = strtolower($includedName);
            if (!isset($allowedIncluded[$key])) {
                throw new TicketCartValidationException('wellness', 'Invalid included wellness service selected');
            }
            $wellnessIncludedService = $allowedIncluded[$key]['name'] ?? null;
            $wellnessIncludedServiceType = $allowedIncluded[$key]['type'] ?? null;
        }

        $buildAllowed = function (array $source) {
            $allowed = [];
            foreach ($source as $entry) {
                if (!is_array($entry) || ($entry['mode'] ?? null) !== 'addon') {
                    continue;
                }
                $name = trim((string) ($entry['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $allowed[strtolower($name)] = [
                    'name' => $name,
                    'price' => floatval($entry['price'] ?? 0),
                    'maxQty' => array_key_exists('qty', $entry) ? intval($entry['qty'] ?? 0) : null,
                ];
            }
            return $allowed;
        };
        $allowedManualBuilder = function (array $source) {
            $allowed = [];
            foreach ($source as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $name = trim((string) ($entry['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $allowed[strtolower($name)] = [
                    'name' => $name,
                    'price' => floatval($entry['price'] ?? 0),
                    'maxQty' => array_key_exists('qty', $entry) ? intval($entry['qty'] ?? 0) : null,
                ];
            }
            return $allowed;
        };

        $allowedServices = $buildAllowed($wellnessConfig['services'] ?? []);
        $allowedManual = $allowedManualBuilder($wellnessConfig['manualAddons'] ?? []);

        $selectFrom = function (array $selections, array $allowed, string $category) use (&$wellnessTotal, &$wellnessAddonsJson) {
            foreach ($selections as $sel) {
                if (!is_array($sel)) {
                    continue;
                }
                $name = trim((string) ($sel['name'] ?? ''));
                $qty = intval($sel['qty'] ?? 0);
                if ($name === '' || $qty <= 0) {
                    continue;
                }
                $key = strtolower($name);
                if (!isset($allowed[$key])) {
                    throw new TicketCartValidationException('wellness', 'Invalid wellness ' . ($category === 'wellness_service' ? 'service' : 'addon') . ' selected');
                }
                $maxQty = $allowed[$key]['maxQty'];
                if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                    throw new TicketCartValidationException('wellness', 'Invalid wellness ' . ($category === 'wellness_service' ? 'service' : 'addon') . ' quantity');
                }
                $unitPrice = floatval($allowed[$key]['price']);
                $lineTotal = $unitPrice * $qty;
                $wellnessTotal += $lineTotal;
                $wellnessAddonsJson[] = [
                    'name' => $allowed[$key]['name'],
                    'category' => $category,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }
        };

        $selectFrom($wellnessInput['services'] ?? [], $allowedServices, 'wellness_service');
        $selectFrom($wellnessInput['manualAddons'] ?? [], $allowedManual, 'wellness_manual');

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
        if (isset($wellnessInput['serviceMode'])) {
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

        return [$wellnessTotal, $wellnessAddonsJson, $wellnessIncludedService, $wellnessSlotBlockId];
    }

    private function resolveDrinksAndTables($ticket, array $item, float $discounted, ?EventFeeSetting $eventFeeSettings): array
    {
        $tablesTotal = 0;
        $drinksTotal = 0;
        $drinkAddonsJson = [];
        $tableAddonsJson = [];
        $itemDrinkFees = 0;
        $itemBottleFees = 0;
        $tableSections = [];

        $hasPackage = !empty($ticket->package_id) && !empty($ticket->drinkPackage) && ($ticket->has_table === 'yes');
        $hasExplicitTableAddon = false;

        if (!empty($item['addons']) && is_array($item['addons'])) {
            foreach ($item['addons'] as $addonData) {
                if (($addonData['category'] ?? null) === 'table') {
                    $secLabel = $addonData['section'] ?? '-';
                    $secQty = intval($addonData['quantity'] ?? 0);
                    if ($secQty > 0) {
                        $tableSections[] = ['section' => $secLabel, 'quantity' => $secQty];
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
                        ];
                    }
                    continue;
                }

                $addon = $this->addons->resolveDrinkAddon($ticket, $addonData);
                if (!$addon) {
                    throw new TicketCartValidationException('addon', 'Invalid addon selected');
                }
                $drinkQty = intval($addonData['quantity'] ?? 0);
                $drinkUnitPrice = isset($addon['cost']) ? floatval($addon['cost']) : floatval($addon['price'] ?? 0);
                $drinkTotalPrice = $drinkUnitPrice * $drinkQty;
                $drinksTotal += $drinkTotalPrice;

                $drinkAddonsJson[] = [
                    'addon_id' => $addon['id'] ?? null,
                    'name' => $addon['name'],
                    'category' => $addon['category'] ?? null,
                    'section' => $addonData['section'] ?? '-',
                    'quantity' => $drinkQty,
                    'unit_price' => $drinkUnitPrice,
                    'total_price' => $drinkTotalPrice,
                ];

                if ($eventFeeSettings) {
                    $category = $addon['category'] ?? '';
                    $isBottleType = ($category === 'bottles');
                    $isRegularDrinkType = in_array($category, ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks']);
                    if ($isRegularDrinkType) {
                        $itemDrinkFees += $drinkTotalPrice * (floatval($eventFeeSettings->drink_fee_pct ?? 0) / 100);
                    }
                    if ($isBottleType) {
                        $itemBottleFees += $drinkTotalPrice * (floatval($eventFeeSettings->bottle_fee_pct ?? 0) / 100);
                    }
                }
            }
        }

        if ($hasPackage && !$hasExplicitTableAddon) {
            $tableUnit = floatval($ticket->table_price ?? 0);
            $packageTableQty = intval($item['qty'] ?? 0);
            $shouldChargePackageTable = ($tableUnit > 0 && $tableUnit !== $discounted);
            $packageTableCharge = $shouldChargePackageTable ? ($tableUnit * $packageTableQty) : 0;

            if ($tableUnit > 0 && $packageTableQty > 0) {
                if (!empty($tableSections)) {
                    foreach ($tableSections as $ts) {
                        $qty = intval($ts['quantity'] ?? 0);
                        if ($qty <= 0) {
                            continue;
                        }
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

        return [$tablesTotal, $drinksTotal, $drinkAddonsJson, $tableAddonsJson, $itemDrinkFees, $itemBottleFees, $hasPackage, $hasExplicitTableAddon];
    }
}
