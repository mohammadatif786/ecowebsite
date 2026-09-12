<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceProduct extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'user_id',
        'name',
        'product_category_id',
        'description',
        'cover_image',
        'qty',
        'price',
        'status',
        'images',
        'listing_type',
        'seller_owner',
        'image_url',
    ];

    protected $casts = [
        'images' => 'array',
        'status' => 'boolean',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        });
    }
}
