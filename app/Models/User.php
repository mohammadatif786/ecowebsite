<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Frontend\FriendRequest;
use App\Models\ScanSignUser;
use Stripe\Customer;
use App\Models\LinkUpEvent;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use O21\LaravelWallet\Contracts\Payable;
use O21\LaravelWallet\Models\Transaction;
use O21\LaravelWallet\Models\Concerns\HasBalance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;

use function O21\LaravelWallet\ConfigHelpers\get_model_class;

class User extends Authenticatable implements Payable
{
    use HasFactory, Notifiable, HasRoles, HasBalance, HasApiTokens, BroadcastsEvents;


    /** @use HasFactory<\Database\Factories\UserFactory> */
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'linkup_id',
        'password',
        'is_wizard_completed',
        'uid',
        'country',
        'front_side',
        'link_me_with',
        'distance_filter',
        'language',
        'religion',
        'whyare',
        'about_me',
        'password',
        // 'balance',  //Replaced by Bavix/Wallet
        'age_filter',
        'fcmToken',
        'address_proof',
        'image',
        'new_message_notification',
        'more_photos',
        'country_code',
        'is_restaurant',
        'is_top_shelf',
        'link_with_me_phone_code',
        'phone_number',
        'city',
        'new_city',
        'back_side',
        'selfie',
        'job',
        'status',
        'hide_profile',
        'is_club',
        'birthday',
        'new_match_notification',
        'new_country',
        'gender',
        'university',
        'created_at',
        'subscription',
        'video',
        'link_me_with_country_name',
        'uid',
        'stripe_customer_id',
        'updated_at',
        'distanceinMK',
        'state',
        'new_state',
        'first_name',
        'email',
        'kyc_status',
        'last_name',
        'promotion_notification',
        'avatar',
        'show_age',
        'phone_number_dial_code',
        'which_latin_country_you_linked_with',
        'is_ghost',
        'link_me_with_country_code',
        'last_active',
        'interests',
        'age',
        'is_live_streaming',
        'latitude',
        'longitude',
        'first_name',
        'last_name',
        'caribbean_interest',
        'username',
        'type',
        'coins',
        'kyc_submitted',
        'is_active',
        'platform_revenue',
        'pinned',
        'scope',
        'two_factor_enabled',
        'last_login_at',
        'quiet_hours_from',
        'quiet_hours_to',
        'allow_priority_notification',
        'mute_payment_notification',
        'mute_gift_notification',
        'mute_system_notification',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_wizard_completed' => 'boolean',
            'password' => 'hashed',
            'age_filter' => 'array',
            'distance_filter' => 'array',
            'interests' => 'array',
            'subscription' => 'array',
            'more_photos' => 'array',
            'birthday' => 'date',
            'new_match_notification' => 'boolean',
            'new_message_notification' => 'boolean',
            'promotion_notification' => 'boolean',
            'allow_priority_notification' => 'boolean',
            'mute_payment_notification' => 'boolean',
            'mute_gift_notification' => 'boolean',
            'mute_system_notification' => 'boolean',
            'quiet_hours_from' => 'datetime:H:i',
            'quiet_hours_to' => 'datetime:H:i',
            'show_age' => 'boolean',
            'status' => 'boolean',
            'is_club' => 'boolean',
            'is_ghost' => 'boolean',
            'is_live_streaming' => 'boolean',
            'is_restaurant' => 'boolean',
            'is_top_shelf' => 'boolean',
            'hide_profile' => 'boolean',
            'pinned' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
    protected $appends = [
        'front_side_url',
        'back_side_url',
        'address_proof_url',
        'popularity_level',
    ];


    public function messages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    /**
     * The same persisted completion contract used by the web/mobile wizard.
     * Keep this SQL scope in sync with WizardUpdateFormRequest: discovery must
     * never hydrate incomplete candidates and filter them in PHP.
     */
    public function scopeEligibleForLinkup($query)
    {
        return $query->where('is_wizard_completed', true)
            ->whereNotNull('uid')
            ->whereNotNull('country')->where('country', '!=', '')
            ->whereNotNull('gender')->where('gender', '!=', '')
            ->whereNotNull('birthday')
            ->whereNotNull('state')->where('state', '!=', '')
            ->whereNotNull('city')->where('city', '!=', '')
            ->whereNotNull('link_me_with')->where('link_me_with', '!=', '')
            ->whereNotNull('age_filter')
            ->whereNotNull('distance_filter')
            ->whereNotNull('link_me_with_country_name')->where('link_me_with_country_name', '!=', '')
            ->whereNotNull('about_me')->where('about_me', '!=', '')
            ->whereNotNull('job')->where('job', '!=', '')
            ->whereNotNull('university')->where('university', '!=', '')
            ->whereNotNull('whyare')->where('whyare', '!=', '')
            ->whereNotNull('phone_number')->where('phone_number', '!=', '')
            ->whereNotNull('language')->where('language', '!=', '')
            ->whereNotNull('caribbean_interest')->where('caribbean_interest', '!=', '')
            ->whereNotNull('interests')->where('interests', '!=', '[]')
            ->whereNotNull('more_photos')->where('more_photos', '!=', '[]');
    }

    public function isEligibleForLinkup(): bool
    {
        return static::query()->eligibleForLinkup()->whereKey($this->id)->exists();
    }

    public function getGiftsCollectedAttribute()
    {
        return $this->gifts()->count();
    }

    public function getFrontSideUrlAttribute()
    {
        return $this->front_side ? asset($this->front_side) : null;
    }

    public function getBackSideUrlAttribute()
    {
        return $this->back_side ? asset($this->back_side) : null;
    }

    public function getAddressProofUrlAttribute()
    {
        return $this->address_proof ? asset($this->address_proof) : null;
    }

    public function getPopularityLevelAttribute(): string
    {
        $score = $this->popularity_score ?? 0;
        if ($score < 50) return 'Low';
        if ($score < 200) return 'Medium';
        if ($score < 500) return 'High';
        return 'Very High';
    }

    public function events()
    {
        return $this->hasMany(LinkUpEvent::class, 'user_id');
    }

    public function gifts()
    {
        return $this->belongsToMany(Gift::class, 'pivot_gift_user')->withPivot('status');
    }

    public function cash_outs()
    {
        return $this->hasMany(CashOut::class);
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->name = $user->first_name . ' ' . $user->last_name;
            if (empty($model->uid)) {
                $user->uid = Str::random(28);
            }
            // Sync location fields
            if ($user->new_country) $user->country = $user->new_country;
            if ($user->new_state)   $user->state = $user->new_state;
            if ($user->new_city)    $user->city = $user->new_city;

            if (!$user->new_country && $user->country) $user->new_country = $user->country;
            if (!$user->new_state && $user->state)     $user->new_state = $user->state;
            if (!$user->new_city && $user->city)       $user->new_city = $user->city;
        });
        static::updating(function ($user) {
            $user->name = $user->first_name . ' ' . $user->last_name;

            // Sync location fields - prefer dirty new_* fields
            if ($user->isDirty('new_country')) $user->country = $user->new_country;
            if ($user->isDirty('new_state'))   $user->state = $user->new_state;
            if ($user->isDirty('new_city'))    $user->city = $user->new_city;

            // If old fields are dirty and new ones aren't, sync back
            if ($user->isDirty('country') && !$user->isDirty('new_country')) $user->new_country = $user->country;
            if ($user->isDirty('state')   && !$user->isDirty('new_state'))   $user->new_state = $user->state;
            if ($user->isDirty('city')    && !$user->isDirty('new_city'))    $user->new_city = $user->city;
        });
    }

    // 🔹 Messages sent by the user
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    // 🔹 Messages received by the user
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }
    // 🔹 User's subscriptions
    public function subscriptions()
    {
        return $this->hasMany(SubscriptionPlan::class);
    }
    public function subscribed()
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }
    public function checkSubscribed()
    {
        return $this->hasOne(Subscription::class, 'user_id')
            ->with('plan');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function organizerProfile()
    {
        return $this->hasOne(OrganizerProfile::class);
    }
    public function cashouts()
    {
        return $this->hasMany(CashOut::class, 'user_id');
    }
    public function scanSignUser()
    {
        return $this->hasMany(ScanSignUser::class, 'user_id',);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function matchesSent()
    {
        return $this->hasMany(UserMatch::class, 'user_id');
    }

    public function matchesReceived()
    {
        return $this->hasMany(UserMatch::class, 'target_user_id');
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'user_matches', 'user_id', 'target_user_id')
            ->withPivot('status')
            ->wherePivot('status', '=', 'like')
            ->select('users.uid', 'users.id', 'users.age', 'users.name', 'users.country', 'users.gender', 'users.more_photos', 'users.avatar', 'users.popularity_score', 'users.last_active', 'users.interests', 'users.caribbean_interest');
    }


    public function dislikedUsers()
    {
        return $this->belongsToMany(User::class, 'user_matches', 'user_id', 'target_user_id')
            ->withPivot('status')
            ->wherePivot('status', '=', 'dislike')
            ->select('users.id', 'users.age', 'users.name', 'users.country', 'users.gender', 'users.more_photos', 'users.avatar');
    }

    public function scopeNearby($query, $lat, $lng, $minRadius, $maxRadius)
    {
        $distanceSql = "(6371 * acos(
        cos(radians(?)) *
        cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) *
        sin(radians(latitude))
    ))";

        return $query->selectRaw("
            users.id,
            users.uid,
            users.whyare,
            users.name,
            users.age,
            users.more_photos,
            users.country,
            users.caribbean_interest,
            users.linkup_id,
            {$distanceSql} AS distance
        ", [$lat, $lng, $lat])
            ->havingRaw("distance >= ?", [$minRadius])
            ->havingRaw("distance <= ?", [$maxRadius])
            ->orderBy("distance", "asc");
    }

    public function conversations()
    {
        return $this->belongsToMany(ConversationUser::class);
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'user_id')
            ->where('status', 0);
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id')
            ->where('status', 0);
    }

    public function friends()
    {
        return User::select(
            'id',
            'uid',
            'name',
            'avatar'
        )
            ->whereNotNull('uid')
            ->where(function ($query) {
                $query->whereIn('id', function ($q) {
                    $q->select('receiver_id')
                        ->from('friend_requests')
                        ->where('user_id', $this->id)
                        ->where('status', 1);
                })->orWhereIn('id', function ($q) {
                    $q->select('user_id')
                        ->from('friend_requests')
                        ->where('receiver_id', $this->id)
                        ->where('status', 1);
                });
            });
    }

    // public function getAvatarAttribute($value)
    // {
    //     return $value ? asset($value) : null;
    // }
    public function receivedTransactions()
    {
        return $this->morphMany(Transaction::class, 'from');
    }

    public function walletBalances()
    {
        return $this->morphMany(get_model_class('balance'), 'payable');
    }

    public function favoriteEvents()
    {
        return $this->belongsToMany(LinkUpEvent::class, 'favourite_events', 'user_id', 'event_id');
    }

    public function createOrGetStripeCustomer()
    {
        return Customer::retrieve($this->stripe_id);
    }
    public function getAvatarAttribute()
    {
        $avatar = $this->attributes['avatar'] ?? null;
        if (!$avatar) {
            return null;
        }

        // If it's a valid URL (e.g., Firebase), return as is
        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        // Otherwise, assume it's a local path and prepend storage
        return asset('storage/' . $avatar);
    }
    public function isMatchedWith(User $otherUser)
    {
        return UserMatch::where('user_id', $this->id)
            ->where('target_user_id', $otherUser->id)
            ->where('status', 'like')
            ->exists()
            &&
            UserMatch::where('user_id', $otherUser->id)
            ->where('target_user_id', $this->id)
            ->where('status', 'like')
            ->exists();
    }

    // Organizer followers relationships
    public function followingOrganizers()
    {
        return $this->hasMany(OrganizerFollower::class, 'user_id');
    }

    public function isFollowingOrganizer($organizerProfileId)
    {
        return $this->followingOrganizers()->where('organizer_id', $organizerProfileId)->exists();
    }

    public function invitedAsues()
    {
        return $this->belongsToMany(Asue::class, 'asue_invites', 'user_id', 'asue_id')
            ->withPivot('position', 'participation_status')
            ->withTimestamps();
    }

    public function hideSpecificUser()
    {
        return $this->hasMany(HideSpecificUser::class);
    }
    // App/Models/User.php ke end mein add karein

    /**
     * Get all transactions (Sent and Received).
     */
    public function transactions()
    {
        return Transaction::where(function ($query) {
            $query->where('from_id', $this->id)
                ->where('from_type', self::class);
        })->orWhere(function ($query) {
            $query->where('to_id', $this->id)
                ->where('to_type', self::class);
        });
    }

    public function scopeContactForWallet($query)
    {
        $userId = Auth::id();

        return $query->whereNotNull('linkup_id')
            ->where('id', '!=', $userId)
            ->where(function ($q) use ($userId) {
                $q->whereIn('id', function ($sub) use ($userId) {
                    $sub->select('receiver_id')
                        ->from('friend_requests')
                        ->where('user_id', $userId)
                        ->where('status', 1);
                })
                    ->orWhereIn('id', function ($sub) use ($userId) {
                        $sub->select('user_id')
                            ->from('friend_requests')
                            ->where('receiver_id', $userId)
                            ->where('status', 1);
                    });
            });
    }

    public function favoriteSellers()
    {
        return $this->belongsToMany(User::class, 'favorite_sellers', 'user_id', 'seller_id')
            ->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function ticketSales()
    {
        return $this->hasMany(TicketSale::class, 'user_id');
    }

    public function linkupEvents()
    {
        return $this->hasMany(LinkUpEvent::class, 'user_id');
    }

    public function subscribedPlans()
    {
        return $this->hasMany(SubscribedPlan::class, 'user_id');
    }
}
