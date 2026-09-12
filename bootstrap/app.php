<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Http\Middleware\CheckWizardCompleted;
use App\Http\Middleware\RedirectIfAdminIsAuthenticated;
use App\Http\Middleware\EnsureOtpVerified;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            RedirectIfAdminIsAuthenticated::class
        ]);
        $middleware->alias([
            'isWizardComplete' => CheckWizardCompleted::class,
            'otp.verified' => EnsureOtpVerified::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'userType' => \App\Http\Middleware\CheckUserType::class,
            'check.organizer' => \App\Http\Middleware\CheckOrganizer::class,
            'check.kyc' => \App\Http\Middleware\CheckOrganizerKyc::class,
            'check.user_wallet_kyc' => \App\Http\Middleware\CheckUserWalletKyc::class,
            'api.check.organizer' => \App\Http\Middleware\Api\ApiCheckOrganizer::class,
            'api.check.organizer.kyc' =>  App\Http\Middleware\Api\ApiCheckOrganizerKyc::class,
            'feature' => \App\Http\Middleware\FeatureGateMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
