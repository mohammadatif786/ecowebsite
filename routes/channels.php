<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use App\Models\LiveStreamGumlet;

Broadcast::channel('user', function ($user) {
    return Auth::check();
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('live-stream.{publicId}', function ($user, string $publicId) {
    $stream = LiveStreamGumlet::query()
        ->where('public_id', $publicId)
        ->first();

    return $stream !== null && Gate::forUser($user)->allows('joinLiveSession', $stream);
});
