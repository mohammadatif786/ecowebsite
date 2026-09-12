<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = [
        'firebase_id',
        'coins',
        'file_object',
        'name',
        'category',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'pivot_gift_user');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }
    public function getFileObjectAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
