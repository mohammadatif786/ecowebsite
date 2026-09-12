<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class  ScanSignUser extends Authenticatable
{
    use HasRoles, HasApiTokens;

    protected $fillable = [
        'firbase_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'telephone',
        'address',
        'scanner_image_object',
        'org_id', 
        '_method',
        'status',
    ];
   protected $guard_name = 'scanner';
     protected $appends = ['scanner_image_object'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
   public function scopeFilter($query, array $filters)
{
    if ($filters['search'] ?? false) {
        $search = $filters['search'];

        $query->where(function ($query) use ($search) {
            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
        });
    }
}
    // public function getScannerImageUrlAttribute()
    // {
    //     return $this->scanner_image_object ? asset('storage/' . $this->scanner_image_object) : null;
    // }

      public function getScannerImageObjectAttribute()
    {
        $avatar = $this->attributes['scanner_image_object'] ?? null;
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
}
