<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceAffiliateEarning extends Model
{
    protected $fillable = ['promotion_id', 'affiliate_user_id', 'order_id', 'order_item_id', 'product_id', 'commission_amount', 'status', 'released_at', 'paid_at'];
    protected $casts = ['commission_amount' => 'decimal:2', 'released_at' => 'datetime', 'paid_at' => 'datetime'];
}
