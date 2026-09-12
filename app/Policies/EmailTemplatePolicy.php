<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailTemplatePolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'email_templates';
}
