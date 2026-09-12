<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceAffiliatePromotion extends Model
{
    protected $fillable = ['user_id', 'product_id', 'public_token'];

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}
