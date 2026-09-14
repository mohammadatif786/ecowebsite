<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'cover_image',
        'name',
        'product_category_id',
        'description',
        'qty',
        'price',
        'collect_tax',
        'status',
        'images',
        'listing_type',
        'seller_owner',
        'image_url',
        'commMode',
        'commission',
        'commFlat',
    ];

    protected $casts = [
        'images' => 'array',
        'status' => 'boolean',
        'collect_tax' => 'boolean',
        'commission' => 'float',
        'commFlat' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
            $query->orWhere('description', 'like', '%'.$search.'%');
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchants::class, 'seller_owner');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vibes()
    {
        return $this->morphToMany(Vibe::class, 'attachable', 'vibe_attachments');
    }
}
