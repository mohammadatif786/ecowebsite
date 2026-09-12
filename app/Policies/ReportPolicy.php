<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'user_reports';
}
