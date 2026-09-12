<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserReportPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'user_reports';
}
