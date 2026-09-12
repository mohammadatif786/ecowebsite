<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'event_id',
        'event_name',
        'name',
        'type',
        'description',
        'has_table',
        'table_price',
        'table_capacity',
        'sections',
        'main_bottles',
        'chasers_or_mixers',
        'water_options',
        'is_free',
        'price',
        'promo_price',
        'quantity',
        'qty_available',
        'tickets_per_attendee',
        'sale_start',
        'sale_end',
        'sales_start',
        'sales_end',
        'status',
        'drink_addons',
        'ticket_type',
        'package_id',
        'commMode',
        'commission',
        'commFlat',
    ];

    protected $casts = [
        'sections' => 'array',
        'main_bottles' => 'array',
        'chasers_or_mixers' => 'array',
        'water_options' => 'array',
        'drink_addons' => 'array',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
    ];

    protected $appends = [
        'qty_available',
        'cookout',
        'wellness',
    ];

    public function getTicketTypeAttribute()
    {
        return $this->attributes['ticket_type'] ?? $this->type;
    }

    public function getLinkUpEventIdAttribute()
    {
        return $this->attributes['link_up_event_id'] ?? $this->event_id;
    }

    public function getSalesStartAttribute()
    {
        return $this->attributes['sales_start'] ?? $this->sale_start;
    }

    public function getSalesEndAttribute()
    {
        return $this->attributes['sales_end'] ?? $this->sale_end;
    }

    public function getQtyAvailableAttribute()
    {
        return $this->attributes['qty_available'] ?? $this->quantity;
    }

    public function getCookoutAttribute()
    {
        return $this->extraSetting ? $this->extraSetting->cookout : null;
    }

    public function getWellnessAttribute()
    {
        return $this->extraSetting ? $this->extraSetting->wellness : null;
    }

    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class);
    }

    public function drinkPackage()
    {
        return $this->belongsTo(DrinkPackage::class, 'package_id');
    }

    public function extraSetting()
    {
        return $this->hasOne(TicketExtraSetting::class);
    }

    public function wellnessSlotBlocks()
    {
        return $this->hasMany(WellnessSlotBlock::class)->orderBy('start_time', 'asc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function scopeAvailableForSale($query)
    {
        $query
            ->where('quantity', '>', 0)
            ->where(function ($q) {
                $q->where('status', 'active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true);
            });
    }

    public function isCompletelyFree(): bool
    {
        $basePrice = (float) ($this->price ?? 0);
        $tablePrice = (float) ($this->table_price ?? 0);

        if ($basePrice > 0 || $tablePrice > 0) {
            return false;
        }

        return $this->drinksAreFree();
    }

    protected function drinksAreFree(): bool
    {
        $addons = $this->drink_addons ?? [];
        if (!is_array($addons) || empty($addons['enabled'])) {
            return true;
        }

        $items = $addons['items'] ?? [];
        foreach ($items as $group) {
            if (!is_iterable($group)) {
                continue;
            }

            foreach ($group as $item) {
                $cost = (float) ($item['cost'] ?? $item['price'] ?? $item['total_price'] ?? 0);
                if ($cost > 0) {
                    return false;
                }
            }
        }

        foreach (['main_bottles', 'chasers_or_mixers', 'water_options'] as $group) {
            $collection = $this->{$group} ?? [];
            if (!is_iterable($collection)) {
                continue;
            }

            foreach ($collection as $item) {
                $cost = (float) ($item['cost'] ?? $item['price'] ?? 0);
                if ($cost > 0) {
                    return false;
                }
            }
        }

        return true;
    }

    public function scopeFilterByOrganizerCategory($query, $categories)
    {
        if (empty($categories)) {
            return $query;
        }

        // Standardize categories for matching
        $categoryList = (array) $categories;

        // If 'Cookouts/Food' is present, also allow 'Cookouts' and vice versa for legacy compatibility
        if (in_array('Cookouts/Food', $categoryList) || in_array('Cookouts', $categoryList)) {
            $categoryList[] = 'Cookouts/Food';
            $categoryList[] = 'Cookouts';
        }

        return $query->whereIn('type', array_unique($categoryList));
    }
}
