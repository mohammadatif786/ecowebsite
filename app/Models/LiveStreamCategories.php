<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStreamCategories extends Model
{
    protected $table = 'live_stream_categories';

    protected $fillable = [
        'category',
    ];
}

