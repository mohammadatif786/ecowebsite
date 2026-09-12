<?php

namespace App\Models;

use App\Models\LinkUpEvent;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TicketSale extends Model
{
    protected $fillable =  [
        'ticket_id',
        'ticket_name',
        'ticket_qrcode_id',
        'ticket_qrcode',
        'user_id',
        'transferred_from_user_id',
        'transferred_at',
        'link_up_event_id',
        'ticket_status',
        'payment_method',
        'pay_type',
        'ticket_type',
        'fee',
        'discount',
        'tax',
        'coupan_amount',
        'drink_fees',
        'drinks_total',
        'tables_total',
        'drink_addons',
        'table_addons',
        'cookout_included_protein',
        'cookout_included_sides',
        'cookout_addons',
        'cookout_total',
        'wellness_addons',
        'wellness_total',
        'wellness_slot_block_id',
        'package_id',
        'package_data',
        'sub_total',
        'total',
        'no_of_tickets',
        'stripe_id',
        'stripe_price',
        'stripe_status',
        'web_qrcode',
        'fee_breakdown',
        'event_tax',
    ];

    protected $casts = [
        'drink_addons' => 'array',
        'table_addons' => 'array',
        'cookout_included_sides' => 'array',
        'cookout_addons' => 'array',
        'wellness_addons' => 'array',
        'package_data' => 'array',
        'fee_breakdown' => 'array',
        'transferred_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'link_up_event_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function SaleAddon()
    {
        return $this->hasMany(TicketSaleAddon::class);
    }

    public function drinkPackage()
    {
        return $this->belongsTo(DrinkPackage::class, 'package_id');
    }

    public function checkins()
    {
        return $this->hasMany(TicketCheckin::class, 'ticket_sale_id');
    }

    public function cancellationRequest()
    {
        return $this->hasOne(CancellationRequests::class, 'ticket_id', 'id');
    }

}
