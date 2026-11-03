<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = ['key', 'name', 'full_name', 'country', 'flag', 'is_custom', 'created_by'];

    protected function casts(): array
    {
        return ['is_custom' => 'boolean'];
    }

    /** Members who attend or attended this school. */
    public function members()
    {
        return $this->belongsToMany(User::class, 'user_universities')
            ->withPivot(['status', 'is_main'])
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'university_follows')->withTimestamps();
    }

    public function vibes()
    {
        return $this->hasMany(Vibe::class);
    }
}
