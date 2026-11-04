<?php

/*
|--------------------------------------------------------------------------
| Admin routes (everything under /admin/*)
|--------------------------------------------------------------------------
| Loaded from routes/web.php, so the "web" middleware group is already applied.
| Each file declares its own prefix('admin')->name('admin.') group and its own auth/role middleware.
*/

require __DIR__ . '/settings.php';       // profile, password, appearance
require __DIR__ . '/core.php';           // main admin area
require __DIR__ . '/ads.php';            // ads manager
require __DIR__ . '/subscriptions.php';  // plans, subscribers, feature gates
require __DIR__ . '/dashboards.php';     // "new admin" dashboards
