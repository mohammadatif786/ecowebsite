<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventFeeRequest;
use App\Models\EventFee;
use Inertia\Inertia;

class AdminFeeController extends Controller
{
    public function index()
    {
        $eventFee = EventFee::first();
        //dd($eventFee);
        $tiersArray = [];

        if ($eventFee && $eventFee->tiers) {
            $tiersArray = collect(explode('},', $eventFee->tiers))->map(function ($item) {
                $clean = trim($item, '{}');
                return collect(explode(',', $clean))->mapWithKeys(function ($pair) {
                    [$key, $value] = explode(':', $pair);
                    return [trim($key) => trim($value)];
                });
            })->toArray();
        }

        $data = $eventFee ? [
            'id'                        => $eventFee->id,
            'enableSubscriptions'       => $eventFee->enable_subscriptions,
            'platformShareBasePct'      => $eventFee->platform_share_base_pct,
            'creatorShareBasePct'       => $eventFee->creator_share_base_pct,
            'useTiers'                  => $eventFee->use_tiers,
            'enablePrivateSubs'         => $eventFee->enable_private_subs,
            'privateSplitMode'          => $eventFee->private_split_mode,
            'minPrivateSubPrice'        => $eventFee->min_private_sub_price,
            'maxPrivateSubPrice'        => $eventFee->max_private_sub_price,
            'liveShopFeePct'            => $eventFee->live_shop_fee_pct,
            'liveShopProcessingFeePct'  => $eventFee->live_shop_processing_fee_pct,
            'liveShopProcessingFeeFixed' => $eventFee->live_shop_processing_fee_fixed,
            'wireProcessingFeePct'      => $eventFee->wire_processing_fee_pct,
            'payoutThreshold'           => $eventFee->payout_threshold,
            'payoutHoldDays'            => $eventFee->payout_hold_days,
            'taxRate'                   => $eventFee->tax_rate,
            'taxInclusive'              => $eventFee->tax_inclusive,
            'currency'                  => $eventFee->currency,
            'tiers'                     => $tiersArray,
        ] : null;

        return Inertia::render('admin/subscriptions/AdminFee/Index', [
            'eventFee' => $data,
        ]);
    }

    public function storeOrUpdate(EventFeeRequest $request)
    {
        $data = $request->validated();
//dd($data);
        $tiersString = collect($data['tiers'] ?? [])->map(function ($tier) {
            return '{' . collect($tier)->map(fn($value, $key) => "$key:$value")->implode(',') . '}';
        })->implode(',');
//dd($tiersString);
        $payload = [
            'enable_subscriptions'           => $data['enableSubscriptions'] ?? 0,
            'platform_share_base_pct'        => $data['platformShareBasePct'] ?? 60,
            'creator_share_base_pct'         => $data['creatorShareBasePct'] ?? 40,
            'use_tiers'                      => $data['useTiers'] ?? 0,
            'enable_private_subs'            => $data['enablePrivateSubs'] ?? 0,
            'private_split_mode'             => $data['privateSplitMode'] ?? 'fixed50',
            'min_private_sub_price'          => $data['minPrivateSubPrice'] ?? 0,
            'max_private_sub_price'          => $data['maxPrivateSubPrice'] ?? 0,
            'live_shop_fee_pct'              => $data['liveShopFeePct'] ?? 0,
            'live_shop_processing_fee_pct'   => $data['liveShopProcessingFeePct'] ?? 0,
            'live_shop_processing_fee_fixed' => $data['liveShopProcessingFeeFixed'] ?? 0,
            'wire_processing_fee_pct'        => $data['wireProcessingFeePct'] ?? 0,
            'payout_threshold'               => $data['payoutThreshold'] ?? 0,
            'payout_hold_days'               => $data['payoutHoldDays'] ?? 0,
            'tax_rate'                       => $data['taxRate'] ?? 0,
            'tax_inclusive'                  => $data['taxInclusive'] ?? 0,
            'currency'                       => $data['currency'] ?? 'USD',
            'tiers'                          => $tiersString,
        ];
        //dd($payload);
        $eventFee = EventFee::first();

        if ($eventFee) {
            $eventFee->update($payload);
            $msg = 'Event fee settings updated successfully!';
        } else {
            $eventFee = EventFee::create($payload);
            $msg = 'Event fee settings created successfully!';
        }

        return redirect()
            ->route('admin.admin_fee.index')
            ->with('success', $msg);
    }
}
