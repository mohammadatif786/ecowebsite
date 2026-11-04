<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceAffiliateEarning extends Model
{
    protected $fillable = [
        'promotion_id', 'affiliate_user_id', 'order_id', 'order_item_id', 'product_id',
        'vibe_id', 'ticket_sale_id', 'source', 'title', 'sale_amount',
        'commission_amount', 'status', 'released_at', 'paid_at',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'sale_amount' => 'decimal:2',
        'released_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /** One row of My Earnings → recent sales (same shape on the website and in the app). */
    public function summaryRow(): array
    {
        $product = $this->product_id ? MarketplaceProduct::find($this->product_id) : null;
        $commission = (float) $this->commission_amount;
        $rate = $product ? (float) $product->commission : 10;
        $sale = $this->sale_amount !== null
            ? (float) $this->sale_amount
            : ($product && $product->price ? (float) $product->price : ($rate > 0 ? $commission / ($rate / 100) : $commission));

        return [
            'id' => $this->id,
            'title' => $this->title ?? $product?->name ?? 'Affiliate Sale',
            'amount' => round($sale, 2),
            'commission' => $commission,
            'rate' => $sale > 0 ? round($commission / $sale * 100, 1) : $rate,
            'status' => $this->status,
            'source' => $this->source ?? ($this->vibe_id || ! $this->order_id ? 'vibe' : 'marketplace'),
            'kind' => $this->ticket_sale_id ? 'ticket' : 'product',
            'vibe_id' => $this->vibe_id,
            'date' => $this->created_at?->toDateString(),
        ];
    }
}
