<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'taxes';
}
