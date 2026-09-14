<?php

namespace App\Models;

use App\Models\Frontend\FavouriteEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LinkUpEvent extends Model
{
    protected $fillable = [
        'id',
        'user_id', // This is the organizer's user ID
        'firebase_id',
        'category_id',
        'city',
        'country',
        'coupon_visibility',
        'description',
        'disclaimer',
        'email',
        'featured_image',
        'image_object',
        'latitude',
        'longtitude', // Consider renaming to 'longitude' if it's a typo
        'organizer_image_object',
        'organizer_name',
        'phone',
        'state',
        'status', // Added status field
        'title',
        'type',
        'venue',
        'website',
        'likes_count',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
        'is_free',
        'organizer_id',
    ];

    protected $appends = [
        'image_url',
        'organizer_image_url',
        'min_price',
        'max_price',
        'ticket_count',
        'gallery_urls',
    ];

    public function getGalleryUrlsAttribute()
    {
        $gallery = $this->eventDetails?->image_gallery;
        if (! is_array($gallery)) {
            return [];
        }

        return collect($gallery)
            ->map(fn ($img) => asset(Storage::url($img)))
            ->prepend($this->image_url)
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }

    public function getMinPriceAttribute()
    {
        return $this->tickets_min_price ?? ($this->relationLoaded('tickets') ? $this->tickets->min('price') : null);
    }

    public function getMaxPriceAttribute()
    {
        return $this->tickets_max_price ?? ($this->relationLoaded('tickets') ? $this->tickets->max('price') : null);
    }

    public function getTicketCountAttribute()
    {
        return $this->tickets_count ?? ($this->relationLoaded('tickets') ? $this->tickets->count() : 0);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image_object' => 'array',
            'organizer_image_object' => 'array',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_free' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    protected static function booted()
    {

        static::deleting(function ($event) {
            // $event->tickets()->delete();
            Sponsor::where('link_up_event_id', $event->id)->delete();
            Ticket::where('event_id', $event->id)->delete();
        });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('title', 'like', '%'.$search.'%');
        });
    }

    public function scopeNearby($query, $lat, $lng, $minRadius, $maxRadius)
    {
        return $query->select('*', DB::raw("
                    (6371 * acos(
                        cos(radians($lat)) *
                        cos(radians(latitude)) *
                        cos(radians(longtitude) - radians($lng)) +
                        sin(radians($lat)) *
                        sin(radians(latitude))
                    )) AS distance
                "))
            ->having('distance', '>=', $minRadius)
            ->having('distance', '<=', $maxRadius)
            ->orderBy('distance', 'asc');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_id');
    }

    public function user_favourite()
    {
        return $this->hasOne(FavouriteEvent::class, 'event_id', 'id')
            ->where('user_id', Auth::user()->id);
    }

    public function authUserFavorite()
    {
        return $this->hasOne(FavouriteEvent::class, 'event_id')->where('user_id', auth()->id());
    }

    public function getImageUrlAttribute()
    {
        $image = $this->image_object;
        if (is_array($image)) {
            $image = $image[0] ?? null;
        }

        if (! $image) {
            // Fallback to featured_image if image_object is empty
            $image = $this->featured_image;
        }

        if (! $image) {
            return null;
        }

        return asset(Storage::url($image));
    }

    public function getOrganizerImageUrlAttribute()
    {
        if (! $this->organizer_image_object) {
            return null;
        }

        return asset(Storage::url($this->organizer_image_object));
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'link_up_event_id');
    }

    public function event_audience()
    {
        return $this->hasMany(TicketSale::class, 'link_up_event_id');
    }

    public function eventDetails()
    {
        return $this->hasOne(EventDetails::class, 'event_id');
    }

    public function ticketSales()
    {
        return $this->hasMany(TicketSale::class, 'link_up_event_id');
    }

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }

    public function sponsors()
    {
        return $this->hasMany(Sponsor::class, 'link_up_event_id');
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class, 'event_id');
    }

    public function approvedReviews()
    {
        return $this->hasMany(EventReview::class, 'event_id')->where('status', 'approved');
    }

    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?: 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    public function favourites()
    {
        return $this->hasMany(FavouriteEvent::class, 'event_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('eventDetails', function (Builder $q) {
            $q->where(function (Builder $sub) {
                $sub->where('event_type', 'single')->whereDate('single_event_date', '>', now());
            })->orWhere(function (Builder $sub) {
                $sub->where('event_type', 'recurring')->whereDate('recurr_end_date', '>', now());
            });
        });
    }

    public function scopeActiveTodayAndFuture(Builder $query): Builder
    {
        return $query->whereHas('eventDetails', function (Builder $q) {
            $q->where(function (Builder $sub) {
                $sub->where('event_type', 'single')->whereDate('single_event_date', '>=', now()->today());
            })->orWhere(function (Builder $sub) {
                $sub->where('event_type', 'recurring')->whereDate('recurr_end_date', '>=', now()->today());
            });
        });
    }

    public function vibes()
    {
        return $this->morphToMany(Vibe::class, 'attachable', 'vibe_attachments');
    }
}
