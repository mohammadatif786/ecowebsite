<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAnalytic extends Model
{
    protected $table = 'product_analytics';

    protected $fillable = [
        'product_id',
        'user_id',
        'date',
        'views',
        'clicks',
        'conversions',
        'revenue',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment views for a product on today's date.
     */
    public static function recordView(int $productId, int $userId): void
    {
        self::updateOrCreate(
            ['product_id' => $productId, 'date' => now()->toDateString()],
            ['user_id' => $userId]
        )->increment('views');
    }

    /**
     * Increment clicks for a product on today's date.
     */
    public static function recordClick(int $productId, int $userId): void
    {
        self::updateOrCreate(
            ['product_id' => $productId, 'date' => now()->toDateString()],
            ['user_id' => $userId]
        )->increment('clicks');
    }

    /**
     * Record a conversion (order) for a product.
     */
    public static function recordConversion(int $productId, int $userId, float $revenue): void
    {
        $record = self::updateOrCreate(
            ['product_id' => $productId, 'date' => now()->toDateString()],
            ['user_id' => $userId]
        );
        $record->increment('conversions');
        $record->increment('revenue', $revenue);
    }
}
