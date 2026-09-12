<?php

use App\Http\Controllers\Admin\Subscriptions\FeatureGateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Subscriptions\UserPlanController;


Route::prefix('admin')->name('admin.')->group(function () {

    Route::group(['middleware' => ['auth', 'role:admin', 'userType:admin']], function () {


        Route::get("subscription-dashboard", [UserPlanController::class, 'dashboard'])->name('subscriptions-dashboard');
        Route::get('subscriptions', [UserPlanController::class, 'index'])->name('subscriptions.index');
        Route::get('subscribers', [UserPlanController::class, 'subscribedUser'])->name('subscription.users.list');

        Route::get('subscriptions-revenue', [UserPlanController::class, 'subscriptionRevenue'])->name('subscriptions.revenue');
        Route::get('subscriptions-go-live', [UserPlanController::class, 'subscriptionGoLive'])->name('subscriptions.go-live');

        Route::post('subscription-plans/store', [UserPlanController::class, 'store']);
        Route::post('subscription-plans/{planId}/update', [UserPlanController::class, 'update']);
        Route::delete('subscription-plan/{planId}', [UserPlanController::class, 'delete']);

        Route::get('subscription-club-and-restaurants', [UserPlanController::class, 'subscriptionClubRestaurant'])->name('subscriptions.club.restaurant');
        Route::get('subscription-market-place', [UserPlanController::class, 'subscriptionMarketPlace'])->name('subscriptions.marketplace');
        Route::get('subscription-events', [UserPlanController::class, 'subscriptionEvents'])->name('subscriptions.events');
        Route::get('subscription-labs', [UserPlanController::class, 'subscriptionLab'])->name('subscriptions.lab');

        Route::apiResource('feature-gates', FeatureGateController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});
