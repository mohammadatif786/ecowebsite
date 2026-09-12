<?php

namespace App\Actions;

use App\Models\LinkUpEvent;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Models\TicketSale;
use App\Models\TicketSaleAddon;
use App\Models\User;
use App\Models\WellnessSlotBlock;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;

/**
 * Persists a priced cart (BuildTicketCartAction output) as TicketSale +
 * TicketSaleAddon rows once payment has actually happened — wallet debit
 * already committed, or a Stripe Checkout Session confirmed paid.
 *
 * Each admitted unit gets its own TicketSale row (own QR code), even when a
 * single cart item was bought with qty > 1 — one physical ticket needs one
 * scannable code. Only the first unit of a given item carries that item's
 * addon/cookout/wellness breakdown and package data; only the very first
 * unit of the whole order carries the order-level fees/tax/discount — the
 * rest are bare per-seat rows. Inventory is still decremented once per item
 * using the item's full qty, not once per generated row.
 */
class FinalizeTicketSaleAction
{
    public function handle(array $cart, int $userId, string $orderId, string $paymentMethod, string $payType): array
    {
        $fees = $cart['fees'];
        $couponAmount = $cart['coupon_amount'];

        $isFirstTicketInOrder = true;
        $feesSummary = null;
        $ticketSaleIds = [];

        foreach ($cart['items'] as $item) {
            $ticket = $item['ticket'];
            $qty = (int) ($item['qty'] ?? 0);
            $units = $qty >= 1 ? $qty : 1;

            for ($unit = 0; $unit < $units; $unit++) {
                $isFirstUnitOfItem = ($unit === 0);

                [$ticketSale, $feesSummaryForRow] = $this->createTicketSaleRow(
                    $ticket,
                    $item,
                    $qty,
                    $isFirstUnitOfItem,
                    $isFirstTicketInOrder,
                    $cart,
                    $fees,
                    $couponAmount,
                    $userId,
                    $orderId,
                    $paymentMethod,
                    $payType
                );

                if ($isFirstTicketInOrder) {
                    $feesSummary = $feesSummaryForRow;
                }

                $ticketSaleIds[] = $ticketSale->id;
                $isFirstTicketInOrder = false;
            }

            if (!empty($item['wellness_slot_block_id'])) {
                $block = WellnessSlotBlock::find($item['wellness_slot_block_id']);
                if ($block && $block->count > 0) {
                    $block->decrement('count');
                }
            }

            if ($qty > 0) {
                $ticket->quantity = max(0, $ticket->quantity - $qty);
            }
            foreach ($item['table_addons_json'] ?? [] as $t) {
                $tableQty = intval($t['quantity'] ?? 0);
                if ($tableQty > 0) {
                    $ticket->table_capacity = max(0, ($ticket->table_capacity ?? 0) - $tableQty);
                }
            }
            $ticket->save();
        }

        $ticketSales = TicketSale::whereIn('id', $ticketSaleIds)->get();
        $event = LinkUpEvent::find($cart['event']->id);
        $this->notifyOrganizer($event, $ticketSales, $userId, $ticketSaleIds, $paymentMethod);

        return [
            'ticket_sales' => $ticketSales,
            'fees_summary' => $feesSummary,
            'event' => $event,
        ];
    }

    /**
     * @return array{0: TicketSale, 1: ?array}
     */
    private function createTicketSaleRow(
        $ticket,
        array $item,
        int $qty,
        bool $isFirstUnitOfItem,
        bool $isFirstTicketInOrder,
        array $cart,
        array $fees,
        float $couponAmount,
        int $userId,
        string $orderId,
        string $paymentMethod,
        string $payType
    ): array {
        $uniqueCode = Str::random(20);

        $options = new QROptions([
            'eccLevel' => EccLevel::L,
            'scale' => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
        ]);
        $qrCodeImage = (new QRCode($options))->render($uniqueCode);
        $qrCodePath = storage_path('app/public/qr-codes/' . $uniqueCode . '.png');
        if (!file_exists(dirname($qrCodePath))) {
            mkdir(dirname($qrCodePath), 0755, true);
        }
        file_put_contents($qrCodePath, $qrCodeImage);

        // Per-seat base price: one unit's ticket price, except a pure
        // addon-only line (qty 0) which keeps its original (zero) subtotal.
        $unitSubtotal = $qty >= 1 ? $item['price'] : ($item['price'] * $qty);

        $unitAddonTotal = $isFirstUnitOfItem ? $item['addon_total'] : 0;

        $firstTicketFees = $isFirstTicketInOrder ? (
            $fees['service_fee_percent'] + $fees['service_fee_fixed']
            + $fees['processing_fee_percent'] + $fees['processing_fee_fixed']
            + $fees['drink_fees'] + $fees['bottle_fees'] + $fees['vip_fees'] + $fees['mobile_fee'] + $fees['tax']
            - $couponAmount
        ) : 0;

        $feesSummary = null;
        if ($isFirstTicketInOrder) {
            $feesSummary = [
                'service_fee_total' => $fees['service_fee_percent'] + $fees['service_fee_fixed'],
                'processing_fee_total' => $fees['processing_fee_percent'] + $fees['processing_fee_fixed'],
                'drink_fee_total' => $fees['drink_fees'] + $fees['bottle_fees'],
                'vip_fee_total' => $fees['vip_fees'],
                'mobile_fee_total' => $fees['mobile_fee'],
                'tax_total' => $fees['tax'],
                'discount_total' => $couponAmount,
                'subtotal' => $cart['cart_subtotal'],
                'total' => $cart['total'],
            ];
        }

        $unitTotal = $unitSubtotal + $unitAddonTotal + $firstTicketFees;

        $hasSelectedTable = false;
        foreach ($item['table_addons_json'] ?? [] as $tableAddon) {
            if (intval($tableAddon['quantity'] ?? 0) > 0) {
                $hasSelectedTable = true;
                break;
            }
        }

        $packagePayload = null;
        if ($isFirstUnitOfItem && $hasSelectedTable && !empty($ticket->package_id) && !empty($ticket->drinkPackage)) {
            $pkg = $ticket->drinkPackage;
            $packagePayload = [
                'id' => $ticket->package_id,
                'name' => $pkg->name ?? null,
                'bottles' => is_array($pkg->bottles) ? $pkg->bottles : (is_string($pkg->bottles) ? json_decode($pkg->bottles, true) : []),
                'chasers' => is_array($pkg->chasers) ? $pkg->chasers : (is_string($pkg->chasers) ? json_decode($pkg->chasers, true) : []),
                'waters' => is_array($pkg->waters) ? $pkg->waters : (is_string($pkg->waters) ? json_decode($pkg->waters, true) : []),
                'notes' => $pkg->notes ?? null,
            ];
        }

        $dbTicketName = $ticket->name;
        $wellnessConfig = $ticket->wellness ? (is_string($ticket->wellness) ? json_decode($ticket->wellness, true) : $ticket->wellness) : null;
        if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
            $dbTicketName .= ' (Wellness)';
        }

        $isDrinkOnlyTicket = $item['is_drink_only_ticket'] ?? false;

        $fullFeeBreakdown = $cart['event_fee_settings'] ? [
            'service_fee_pct_rate' => floatval($cart['event_fee_settings']->service_fee_pct ?? 0),
            'service_fee_pct_amount' => $isFirstTicketInOrder ? $fees['service_fee_percent'] : 0,
            'service_fee_fixed' => $isFirstTicketInOrder ? $fees['service_fee_fixed'] : 0,
            'processing_fee_pct_rate' => floatval($cart['event_fee_settings']->processing_fee_pct ?? 0),
            'processing_fee_pct_amount' => $isFirstTicketInOrder ? $fees['processing_fee_percent'] : 0,
            'processing_fee_fixed' => $isFirstTicketInOrder ? $fees['processing_fee_fixed'] : 0,
            'vip_package_fee_pct_rate' => floatval($cart['event_fee_settings']->vip_fee_pct ?? 0),
            'vip_package_fee_amount' => $isFirstTicketInOrder ? $fees['vip_fees'] : 0,
            'mobile_fee_amount' => $isFirstTicketInOrder ? $fees['mobile_fee'] : 0,
        ] : null;

        $ticketFeeBreakdown = null;
        if ($cart['event_fee_settings']) {
            if ($hasSelectedTable) {
                $ticketFeeBreakdown = [
                    'vip_package_fee_pct_rate' => floatval($cart['event_fee_settings']->vip_fee_pct ?? 0),
                    'vip_package_fee_amount' => $isFirstTicketInOrder ? $fees['vip_fees'] : 0,
                ];
            } elseif ($isDrinkOnlyTicket || floatval($item['drinks_total'] ?? 0) > 0) {
                $ticketFeeBreakdown = [
                    'drink_fee_pct_rate' => floatval($cart['event_fee_settings']->drink_fee_pct ?? 0),
                    'drink_fee_amount' => $isFirstTicketInOrder ? $fees['drink_fees'] : 0,
                    'bottle_fee_pct_rate' => floatval($cart['event_fee_settings']->bottle_fee_pct ?? 0),
                    'bottle_fee_amount' => $isFirstTicketInOrder ? $fees['bottle_fees'] : 0,
                ];
            } else {
                $ticketFeeBreakdown = $fullFeeBreakdown;
            }
        }

        $ticketSale = TicketSale::create([
            'ticket_id' => $ticket->id,
            'ticket_type' => $ticket->type,
            'ticket_name' => $dbTicketName,
            'ticket_qrcode' => $uniqueCode,
            'ticket_qrcode_id' => $uniqueCode,
            'no_of_tickets' => $qty >= 1 ? 1 : 0,
            'user_id' => $userId,
            'link_up_event_id' => $ticket->event_id,
            'ticket_status' => 'confirmed',
            'payment_method' => $paymentMethod,
            'pay_type' => $payType,
            'stripe_id' => $orderId,
            'fee' => ($isFirstTicketInOrder && !$isDrinkOnlyTicket) ? ($fees['service_fee_percent'] + $fees['service_fee_fixed']) : 0,
            'discount' => $isFirstTicketInOrder ? $couponAmount : 0,
            'tax' => ($isFirstTicketInOrder && !$isDrinkOnlyTicket) ? ($fees['processing_fee_percent'] + $fees['processing_fee_fixed']) : 0,
            'coupan_amount' => $isFirstTicketInOrder ? $couponAmount : 0,
            'drink_fees' => $isFirstTicketInOrder ? ($fees['drink_fees'] + $fees['bottle_fees']) : 0,
            'event_tax' => ($isFirstTicketInOrder && !$isDrinkOnlyTicket) ? $fees['tax'] : 0,
            'drinks_total' => $isFirstUnitOfItem ? ($item['drinks_total'] ?? 0) : 0,
            'tables_total' => $isFirstUnitOfItem ? ($item['tables_total'] ?? 0) : 0,
            'drink_addons' => ($isFirstUnitOfItem && !empty($item['drink_addons_json'])) ? $item['drink_addons_json'] : null,
            'table_addons' => ($isFirstUnitOfItem && !empty($item['table_addons_json'])) ? $item['table_addons_json'] : null,
            'cookout_included_protein' => $isFirstUnitOfItem ? ($item['cookout_selection']['includedProtein'] ?? null) : null,
            'cookout_included_sides' => $isFirstUnitOfItem ? ($item['cookout_selection']['includedSides'] ?? null) : null,
            'cookout_addons' => ($isFirstUnitOfItem && !empty($item['cookout_addons_json'])) ? $item['cookout_addons_json'] : null,
            'cookout_total' => $isFirstUnitOfItem ? floatval($item['cookout_total'] ?? 0) : 0,
            'wellness_addons' => ($isFirstUnitOfItem && !empty($item['wellness_addons_json'])) ? $item['wellness_addons_json'] : null,
            'wellness_total' => $isFirstUnitOfItem ? floatval($item['wellness_total'] ?? 0) : 0,
            'wellness_slot_block_id' => $isFirstUnitOfItem ? ($item['wellness_slot_block_id'] ?? null) : null,
            'package_id' => $packagePayload['id'] ?? null,
            'package_data' => $packagePayload,
            'sub_total' => $unitSubtotal,
            'total' => $unitTotal,
            'stripe_price' => $unitTotal,
            'web_qrcode' => 'qr-codes/' . $uniqueCode . '.png',
            'fee_breakdown' => $ticketFeeBreakdown,
        ]);

        if ($isFirstUnitOfItem) {
            $this->createAddonRows($ticketSale, $ticket, $item, $cart['event_fee_settings']);
        }

        return [$ticketSale, $feesSummary];
    }

    private function createAddonRows(TicketSale $ticketSale, $ticket, array $item, $eventFeeSettings): void
    {
        foreach ($item['table_addons_json'] ?? [] as $tableAddon) {
            if (($tableAddon['total_price'] ?? 0) > 0 && ($tableAddon['quantity'] ?? 0) > 0) {
                TicketSaleAddon::create([
                    'ticket_sale_id' => $ticketSale->id,
                    'addon_name' => 'Table - ' . ($tableAddon['section'] ?? '-'),
                    'quantity' => $tableAddon['quantity'],
                    'unit_price' => $tableAddon['unit_price'],
                    'total_price' => $tableAddon['total_price'],
                    'category' => 'table',
                ]);
            }
        }

        foreach ($item['drink_addons_json'] ?? [] as $drinkAddon) {
            if (($drinkAddon['quantity'] ?? 0) <= 0) {
                continue;
            }
            $drinkFeePctRate = floatval($eventFeeSettings->drink_fee_pct ?? 0);
            $bottleFeePctRate = floatval($eventFeeSettings->bottle_fee_pct ?? 0);
            $isBottleType = ($drinkAddon['category'] ?? null) === 'bottles';
            $lineTotal = floatval($drinkAddon['total_price']);
            $section = ($drinkAddon['section'] ?? null) === '-' ? null : ($drinkAddon['section'] ?? null);

            TicketSaleAddon::create([
                'ticket_sale_id' => $ticketSale->id,
                'addon_name' => collect([$drinkAddon['name'], $section])->filter()->implode(' - '),
                'quantity' => $drinkAddon['quantity'],
                'unit_price' => $drinkAddon['unit_price'],
                'total_price' => $drinkAddon['total_price'],
                'category' => $drinkAddon['category'] ?? 'generic',
                'addon_id' => $drinkAddon['addon_id'] ?? null,
                'addon_fee_breakdown' => $eventFeeSettings ? [
                    'drink_fee_pct_rate' => $drinkFeePctRate,
                    'drink_fee_amount' => $lineTotal * ($drinkFeePctRate / 100),
                    'bottle_fee_pct_rate' => $bottleFeePctRate,
                    'bottle_fee_amount' => $isBottleType ? ($lineTotal * ($bottleFeePctRate / 100)) : 0,
                ] : null,
            ]);
        }

        foreach ($item['cookout_addons_json'] ?? [] as $addon) {
            $qty = intval($addon['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            TicketSaleAddon::create([
                'ticket_sale_id' => $ticketSale->id,
                'addon_name' => $addon['name'] ?? 'Cookout Add-on',
                'quantity' => $qty,
                'unit_price' => floatval($addon['unit_price'] ?? 0),
                'total_price' => floatval($addon['unit_price'] ?? 0) * $qty,
                'category' => $addon['category'] ?? 'cookout',
            ]);
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

        foreach ($item['wellness_addons_json'] ?? [] as $addon) {
            $qty = intval($addon['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            TicketSaleAddon::create([
                'ticket_sale_id' => $ticketSale->id,
                'addon_name' => $addon['name'] ?? 'Wellness Add-on',
                'quantity' => $qty,
                'unit_price' => floatval($addon['unit_price'] ?? 0),
                'total_price' => floatval($addon['unit_price'] ?? 0) * $qty,
                'category' => $addon['category'] ?? 'wellness',
            ]);
        }

        $hasPackage = !empty($ticket->package_id) && !empty($ticket->drinkPackage) && $ticket->has_table === 'yes';
        if ($hasPackage) {
            $hasSelectedTable = false;
            foreach ($item['table_addons_json'] ?? [] as $tableAddon) {
                if (intval($tableAddon['quantity'] ?? 0) > 0) {
                    $hasSelectedTable = true;
                    break;
                }
            }

            $unit = floatval($ticket->table_price ?? 0);
            $packageTableQty = intval($item['qty'] ?? 0);
            if (!$hasSelectedTable && $unit > 0 && $packageTableQty > 0) {
                TicketSaleAddon::create([
                    'ticket_sale_id' => $ticketSale->id,
                    'addon_name' => 'Table - Package',
                    'quantity' => $packageTableQty,
                    'unit_price' => $unit,
                    'total_price' => $unit * $packageTableQty,
                    'category' => 'table',
                ]);
            }
        }
    }

    private function notifyOrganizer(?LinkUpEvent $event, $ticketSales, int $userId, array $ticketSaleIds, string $paymentMethod): void
    {
        if (!$event) {
            return;
        }

        $organizerUserId = null;
        if ($event->organizer_id && User::whereKey($event->organizer_id)->exists()) {
            $organizerUserId = $event->organizer_id;
        }
        if (!$organizerUserId && $event->organizer_id) {
            $organizerUserId = OrganizerProfile::whereKey($event->organizer_id)->value('user_id');
        }
        if (!$organizerUserId && $event->user_id && User::whereKey($event->user_id)->exists()) {
            $organizerUserId = $event->user_id;
        }
        if ($organizerUserId && !User::whereKey($organizerUserId)->exists()) {
            $organizerUserId = null;
        }

        if (!$organizerUserId) {
            return;
        }

        $user = User::find($userId);
        $totalTickets = (int) $ticketSales->sum('no_of_tickets');
        if ($totalTickets <= 0) {
            $totalTickets = (int) $ticketSales->count();
        }

        Notification::create([
            'title' => 'New ticket purchase',
            'message' => ($user?->name ?? 'Someone') . " purchased {$totalTickets} ticket(s) for \"{$event->title}\".",
            'send_by' => (string) $userId,
            'user_id' => $organizerUserId,
            'type' => 'payment',
            'context' => 'ticket_purchase',
            'unread' => true,
            'avatar' => $user?->avatar ?? null,
            'metadata' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'ticket_sale_ids' => $ticketSaleIds,
                'tickets_count' => $totalTickets,
                'amount_paid' => (float) $ticketSales->sum('stripe_price'),
                'payment_method' => $paymentMethod,
            ],
        ]);
    }
}
