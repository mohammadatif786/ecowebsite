<?php

namespace App\Providers;

use App\Contracts\ChatServiceInterface;
use App\Contracts\FeatureGateRepositoryInterface;
use App\Repositories\FeatureGateRepository;
use App\Services\ChatService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Domain\LiveStreams\Services\StreamingProviderServiceInterface::class,
            \App\Domain\LiveStreams\Services\AgoraStreamingProviderService::class,
        );
        $this->app->bind(
            \App\Domain\LiveStreams\Repositories\LiveStreamRepositoryInterface::class,
            \App\Domain\LiveStreams\Repositories\EloquentLiveStreamRepository::class,
        );
        $this->app->bind(ChatServiceInterface::class, ChatService::class);
        $this->app->singleton(\Stripe\StripeClient::class, fn () => new \Stripe\StripeClient(config('services.stripe.secret')));

        $this->app->bind(
            \App\Contracts\SubscriptionPlanRepositoryInterface::class,
            \App\Repositories\SubscriptionPlanRepository::class
        );

        $this->app->bind(FeatureGateRepositoryInterface::class, FeatureGateRepository::class);

        RedirectResponse::macro('withSuccess', function ($message) {
            return $this->with('messages', [
                [
                    'type' => 'success',
                    'message' => $message,
                ],
            ]);
        });

        RedirectResponse::macro('withError', function ($message) {
            return $this->with('messages', [
                [
                    'type' => 'error',
                    'message' => $message,
                ],
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'user' => \App\Models\User::class,
            'organization' => \App\Models\OrganizerProfile::class,
            'group' => \App\Models\ClubFete::class,
            'custom_publisher' => \App\Models\UserCustomPublisher::class,
            'product' => \App\Models\Product::class,
            'event' => \App\Models\LinkUpEvent::class,
        ]);

        $this->configureRateLimiting();

        \App\Models\LinkUpEvent::observe(\App\Observers\LinkUpEventObserver::class);
        Inertia::share('auth', function () {
            $user = Auth::user();

            if (! $user) {
                return null;
            }

            return [
                'user' => $user,
                'permissions' => [
                    'create_news' => $user->can('create news'),
                    'edit_news' => $user->can('edit news'),
                    'delete_news' => $user->can('delete news'),
                    'toggle_news' => $user->can('toggle news'),
                ],
            ];
        });

        // Automatically fetch and inject backend Sponsor Ads for all emails
        \Illuminate\Support\Facades\View::composer('emails.*', function ($view) {
            $viewName = $view->getName();
            // Skip partials/sponsors to prevent recursion or infinite loops
            if (str_contains($viewName, 'partials') || str_contains($viewName, 'sponsor')) {
                return;
            }

            $data = $view->getData();
            if (array_key_exists('ad', $data)) {
                return;
            }

            // Attempt to extract country code from recipient, user, or similar
            $countryCode = null;
            foreach (['recipient', 'user', 'likedUser', 'receiver', 'member', 'sender', 'buyer'] as $userVar) {
                if (isset($data[$userVar]) && is_object($data[$userVar]) && isset($data[$userVar]->country_code)) {
                    $countryCode = $data[$userVar]->country_code;
                    break;
                }
            }

            // Map view name to category key
            $categoryKey = 'like_received'; // Default fallback
            if (str_contains($viewName, 'money') || str_contains($viewName, 'wallet') || str_contains($viewName, 'coin') || str_contains($viewName, 'payout') || str_contains($viewName, 'withdrawal')) {
                if (str_contains($viewName, 'request')) {
                    $categoryKey = 'money_request';
                } elseif (str_contains($viewName, 'received')) {
                    $categoryKey = 'money_received';
                } else {
                    $categoryKey = 'money_received'; // fallback for other wallet emails
                }
            } elseif (str_contains($viewName, 'event') || str_contains($viewName, 'ticket')) {
                $categoryKey = 'events';
            } elseif (str_contains($viewName, 'marketplace') || str_contains($viewName, 'product') || str_contains($viewName, 'shop')) {
                $categoryKey = 'marketplace';
            } elseif (str_contains($viewName, 'birthday')) {
                $categoryKey = 'birthday';
            }

            try {
                $service = app(\App\Services\SponsorService::class);
                $ad = $service->getAdForEmail($categoryKey, $countryCode);
                if ($ad) {
                    $service->recordImpression($ad, null, $categoryKey, $countryCode);
                }
                $view->with('ad', $ad);
            } catch (\Exception $e) {
                // Fail silently so email sending never crashes if sponsor system has an issue
                \Illuminate\Support\Facades\Log::error('Failed to inject sponsor ad via View Composer: '.$e->getMessage());
                $view->with('ad', null);
            }
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('live-stream-create', fn (Request $request) => Limit::perMinute(3)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('live-stream-join', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('live-stream-end', fn (Request $request) => Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('linkup-swipe', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('linkup-request', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('linkup-message', fn (Request $request) => Limit::perMinute(40)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('linkup-agora', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('admin-password-reset', function (Request $request) {
            return $this->adminLimitsFor(
                $request,
                'admin-password-reset',
                (int) config('admin_security.password_reset.max_attempts', 3),
                (int) config('admin_security.password_reset.decay_minutes', 15)
            );
        });

        RateLimiter::for('admin-verification-send', function (Request $request) {
            return $this->adminLimitsFor(
                $request,
                'admin-verification-send',
                (int) config('admin_security.verification.max_attempts', 3),
                (int) config('admin_security.verification.decay_minutes', 10)
            );
        });

        RateLimiter::for('admin-verification-verify', function (Request $request) {
            return $this->adminLimitsFor(
                $request,
                'admin-verification-verify',
                (int) config('admin_security.verification.max_attempts', 3),
                (int) config('admin_security.verification.decay_minutes', 10)
            );
        });

        RateLimiter::for('admin-password-confirm', function (Request $request) {
            return $this->adminLimitsFor(
                $request,
                'admin-password-confirm',
                (int) config('admin_security.password_confirmation.max_attempts', 5),
                (int) config('admin_security.password_confirmation.decay_minutes', 10)
            );
        });

        RateLimiter::for('admin-sensitive', function (Request $request) {
            return [
                Limit::perMinutes(
                    (int) config('admin_security.sensitive_operations.decay_minutes', 1),
                    (int) config('admin_security.sensitive_operations.max_attempts', 10)
                )->by('admin-sensitive:'.($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip()),
            ];
        });
    }

    /**
     * @return array<int, \Illuminate\Cache\RateLimiting\Limit>
     */
    private function adminLimitsFor(Request $request, string $prefix, int $maxAttempts, int $decayMinutes): array
    {
        $identifier = $request->input('email')
            ?: $request->user()?->getAuthIdentifier()
            ?: $request->route('id')
            ?: 'guest';

        $identifier = Str::lower(Str::transliterate(trim((string) $identifier)));
        $ip = (string) $request->ip();

        return [
            Limit::perMinutes($decayMinutes, $maxAttempts)->by("{$prefix}:{$identifier}|{$ip}"),
            Limit::perMinutes($decayMinutes, $maxAttempts * 3)->by("{$prefix}:ip:{$ip}"),
        ];
    }
}
