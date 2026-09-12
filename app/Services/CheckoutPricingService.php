<?php

namespace App\Services;

use App\Models\Product;

class CheckoutPricingService
{
    public function calculate(array $items, array $location = [], string $shippingMethod = 'standard'): array
    {
        $subtotal = 0;
        $tax = 0;
        $lines = [];

        foreach ($items as $item) {
            $product = Product::with('seller')->findOrFail($item['id']);

            $price = (float) $product->price;
            $qty   = (int) $item['qty'];
            $lineTotal = $price * $qty;

            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'unit_price' => $price,
                'qty' => $qty,
                'sub_total' => $lineTotal,
            ];

            if ($product->collect_tax) {
                $jurisdiction = $shippingMethod === 'pickup' ? [
                    'country' => $product->seller?->new_country ?: $product->seller?->country,
                    'state' => $product->seller?->new_state ?: $product->seller?->state,
                    'city' => $product->seller?->new_city ?: $product->seller?->city,
                    'zip' => null,
                ] : $location;
                if (! empty($jurisdiction['country']) && ! empty($jurisdiction['state'])) {
                    $rule = app(TaxRateService::class)->getStateTaxRules($jurisdiction['state'], $jurisdiction['zip'] ?? null, $jurisdiction['city'] ?? null, $jurisdiction['country']);
                    if ($rule['success'] && ! ($rule['no_state_sales_tax'] ?? false)) {
                        $taxable = $lineTotal;
                        $tax += $taxable * ((float) $rule['rate']);
                    }
                }
            }
        }

        $shipping = 0;
        $discount = 0;
        $feeAmount = 0;
        $feeLabel = 'Marketplace Fee';
        
        // Calculate shop fee
        $shopFeeRepo = app(\App\Repositories\ShopFeeRepository::class);
        $feeSetting = $shopFeeRepo->getFee();

        if ($feeSetting && $feeSetting->enabled) {
            $feeLabel = $feeSetting->label;
            
            if ($feeSetting->fee_type === 'percent') {
                $feeAmount = $subtotal * ($feeSetting->percent / 100);
            } elseif ($feeSetting->fee_type === 'fixed') {
                $feeAmount = $feeSetting->fixed;
            } elseif ($feeSetting->fee_type === 'both') {
                $feeAmount = ($subtotal * ($feeSetting->percent / 100)) + $feeSetting->fixed;
            }
            if ($feeSetting->min_fee > 0 && $feeAmount < $feeSetting->min_fee) {
                $feeAmount = $feeSetting->min_fee;
            }
            if ($feeSetting->max_fee > 0 && $feeAmount > $feeSetting->max_fee) {
                $feeAmount = $feeSetting->max_fee;
            }
        }

        $net = ($subtotal + $tax + $shipping + $feeAmount) - $discount;

        return [
            'subtotal' => $subtotal,
            'tax' => round($tax, 2),
            'shipping' => $shipping,
            'discount' => $discount,
            'fee_amount' => round($feeAmount, 2),
            'fee_label' => $feeLabel,
            'process_fee_amount' => 0,
            'net' => round($net, 2),
            'lines' => $lines,
        ];
    }
}
