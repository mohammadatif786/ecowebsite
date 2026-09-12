<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscribedPlan extends Model
{
   protected $guarded = [];

   public function plan()
   {
      return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'stripe_price_id');
   }
}
