<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrganizerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organizer_name',
        'date_of_birth',
        'place_of_birth',
        'nationality',
        'address',
        'telephone',
        'about_the_organizer',
        'categories',
        'ssn',
    ];

    protected $casts = [
        'categories' => 'array',
        'date_of_birth' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasOne(OrganizerMedia::class, 'organizer_id');
    }

    public function contacts()
    {
        return $this->hasOne(OrganizerContact::class, 'organizer_id');
    }

    public function settings()
    {
        return $this->hasOne(OrganizerSetting::class, 'organizer_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(OrganizerBankAccount::class, 'organizer_id');
    }

    public function kyc()
    {
        return $this->hasOne(OrganizerKyc::class, 'organizer_id');
    }

    public function followers()
    {
        return $this->hasMany(OrganizerFollower::class, 'organizer_id');
    }

    public function events()
    {
        return $this->hasMany(LinkUpEvent::class, 'organizer_id');
    }

    public function vibes()
    {
        return $this->morphMany(Vibe::class, 'publisher');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where(
                        DB::raw("CONCAT(first_name, ' ', last_name)"),
                        'like',
                        '%'.$search.'%'
                    );
                });
            });
        });
    }
}
