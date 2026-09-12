<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class MacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Response::macro('inertiaMessage', function (string $message, string $type = 'success') {
            return redirect()->back()->with($type, $message);
        });
    }

    public function register()
    {
        //
    }
}
