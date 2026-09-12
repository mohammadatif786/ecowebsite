<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSaleAddon extends Model
{
    protected $fillable = [
        'ticket_sale_id',
        'addon_name',
        'quantity',
        'unit_price',
        'total_price',
        'category',
        'addon_fee_breakdown',
    ];

    protected $casts = [
        'addon_fee_breakdown' => 'array',
    ];

    public function ticketSale()
    {
        return $this->belongsTo(TicketSale::class);
    }
}
