<?php

use Illuminate\Support\Facades\Route;

test('the live watch route retains an opaque stream parameter', function () {
    $url = route('new_frontend.live.watch', ['stream' => '01JQ2YZR3F5N7A9BCDEFGHJKLM']);

    expect($url)->toContain('/new_frontend/live/watch/01JQ2YZR3F5N7A9BCDEFGHJKLM')
        ->and(Route::getRoutes()->getByName('new_frontend.live.watch')->parameterNames())->toBe(['stream']);
});
