<?php

namespace App\Providers;

use App\Models\LinkUpEvent;
use App\Models\LiveStreamGumlet;
use App\Models\OrganizerKyc;
use App\Policies\EventPolicy;
use App\Policies\LiveStreamPolicy;
use App\Policies\FriendRequestPolicy;
use App\Models\Frontend\FriendRequest;
use App\Policies\OrganizerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        OrganizerKyc::class => OrganizerPolicy::class,
        LinkUpEvent::class => EventPolicy::class,
        LiveStreamGumlet::class => LiveStreamPolicy::class,
        FriendRequest::class => FriendRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

    }
}
