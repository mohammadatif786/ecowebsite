<?php

use App\Http\Controllers\Admin\Ads\AdsController;
use App\Http\Controllers\Admin\EmailAdsManagerController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {


    Route::group(['middleware' => ['auth', 'role:admin', 'userType:admin']], function () {

        Route::middleware(['permission:view ads'])->group(function () {

            Route::controller(AdsController::class)->prefix('ads')->name('ads.')->group(function () {

                Route::prefix('dashboard')->name('dashboard.')->group(function () {
                    Route::get('/', 'index')->name('index');
                })->middleware('permission:view ads dashboard');

                Route::prefix('analytics')->name('analytics.')->group(function () {
                    Route::get('/', 'analytics')->name('index');
                })->middleware('permission:view ads analytics');

                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', 'reports')->name('index');
                })->middleware('permission:view ads reports');

                Route::prefix('all')->group(function () {
                    Route::get('/', 'indexAds')->name('index');
                    Route::post('/', 'storeAds')->name('store');
                    Route::get('{id}/edit', 'editAds')->name('edit');
                    Route::put('{id}', 'updateAds')->name('update');
                    Route::delete('{id}', 'destroyAds')->name('destroy');
                })->middleware('permission:view all ads list');

                Route::prefix('restaurants')->name('restaurants.')->group(function () {
                    Route::get('/', 'restaurantsAdsList')->name('index');
                })->middleware('permission:view restaurants ads');

                Route::prefix('clubs-fetes')->name('clubs-fetes.')->group(function () {
                    Route::get('/', 'clubsFetesAdsList')->name('index');
                })->middleware('permission:view clubs and fetes ads');

                Route::prefix('campaigns')->name('campaigns.')->group(function () {
                    Route::get('/', 'campaignsIndex')->name('index');
                    Route::post('/store', 'Campaignstore')->name('store');
                    Route::post('/launch', 'campaignLaunch')->name('launch');
                    Route::put('/launch/{id}', 'campaignLaunchUpdate')->name('launch.update');
                    Route::get('/{id}', 'campaignShow')->name('show');
                    Route::put('/{id}', 'campaignUpdate')->name('update');
                    Route::delete('/{id}', 'campaignDelete')->name('destroy');
                    // end
                })->middleware('permission:view ads campaigns');

                Route::prefix('territory-tiers')->name('territory-tiers.')->group(function () {
                    Route::post('/', 'territoryTierStore')->name('store');
                    Route::put('/{id}', 'territoryTierUpdate')->name('update');
                    Route::delete('/{id}', 'territoryTierDelete')->name('destroy');
                })->middleware('permission:view ads campaigns');

                Route::prefix('delivery-channels')->name('delivery-channels.')->group(function () {
                    Route::post('/', 'deliveryChannelStore')->name('store');
                    Route::put('/{id}', 'deliveryChannelUpdate')->name('update');
                    Route::delete('/{id}', 'deliveryChannelDelete')->name('destroy');
                })->middleware('permission:view ads campaigns');

                Route::prefix('exclusivity-upgrades')->name('exclusivity-upgrades.')->group(function () {
                    Route::post('/', 'exclusivityUpgradeStore')->name('store');
                    Route::put('/{id}', 'exclusivityUpgradeUpdate')->name('update');
                    Route::delete('/{id}', 'exclusivityUpgradeDelete')->name('destroy');
                })->middleware('permission:view ads campaigns');

                Route::prefix('industries')->name('industries.')->group(function () {
                    Route::post('/', 'adIndustryStore')->name('store');
                    Route::put('/{id}', 'adIndustryUpdate')->name('update');
                    Route::delete('/{id}', 'adIndustryDelete')->name('destroy');
                })->middleware('permission:view ads campaigns');

                Route::prefix('surge-options')->name('surge-options.')->group(function () {
                    Route::post('/', 'adSurgeOptionStore')->name('store');
                    Route::put('/{id}', 'adSurgeOptionUpdate')->name('update');
                    Route::delete('/{id}', 'adSurgeOptionDelete')->name('destroy');
                })->middleware('permission:view ads campaigns');

                Route::prefix('email-sponsor')->name('email-sponsor.')->group(function () {
                    Route::get('/', [EmailAdsManagerController::class, 'adsIndex'])->name('index');
                    Route::post('/', [EmailAdsManagerController::class, 'adsStore'])->name('store');
                    Route::put('/{ad}', [EmailAdsManagerController::class, 'adsUpdate'])->name('update');
                    Route::delete('/{ad}', [EmailAdsManagerController::class, 'adsDestroy'])->name('destroy');
                    Route::post('/categories', [EmailAdsManagerController::class, 'categoriesStore'])->name('categories.store');
                    Route::delete('/categories/{category}', [EmailAdsManagerController::class, 'categoriesDestroy'])->name('categories.destroy');
                })->middleware('permission:view ads email sponsor');

                Route::prefix('c360-news')->name('c360-news.')->group(function () {
                    Route::get('/', 'c360NewsList')->name('index');
                    Route::get('/ads', 'c360NewsAds')->name('ads');
                    Route::post('/ads', 'c360NewsStore')->name('store');
                    Route::put('/ads/{ad}', 'c360NewsUpdate')->name('update');
                    Route::delete('/ads/{ad}', 'c360NewsDestroy')->name('destroy');
                })->middleware('permission:view ads c360 news');
            });
        });
    });
});
