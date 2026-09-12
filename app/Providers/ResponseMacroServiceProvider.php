<?php

namespace App\Providers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Response::macro('withSuccess', function (string $message) {
            return $this->with('message', $message)->with('messageType', 'success');
        });

        Response::macro('withError', function (string $message) {
            return $this->with('message', $message)->with('messageType', 'error');
        });
    }
}
