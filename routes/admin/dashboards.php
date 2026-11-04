<?php

use App\Http\Controllers\Admin\NewAdminController;
use App\Http\Controllers\Admin\NewAdmin\AdminUserController;
use App\Http\Controllers\Admin\NewAdmin\AdsSettingController;
use App\Http\Controllers\Admin\NewAdmin\EventFeeSettingController;
use App\Http\Controllers\Admin\NewAdmin\RolePermissionController;
use App\Http\Controllers\Admin\NewAdmin\SmtpSettingController;
use App\Http\Controllers\Admin\NewAdmin\TaxesController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::group(['middleware' => ['auth', 'role:admin', 'userType:admin']], function () {
        Route::get('new-dashboard', [NewAdminController::class, 'dashboard'])->name('new-dashboard');
        Route::get('fee-revenue-dashboard', [NewAdminController::class, 'feeRevenue'])->name('fee-revenue-dashboard');
        Route::get('users-dashboard', [NewAdminController::class, 'users'])->name('users-dashboard');
        Route::post('users-dashboard/{user}', [NewAdminController::class, 'updateUserProfile'])->middleware('throttle:admin-sensitive')->name('users-dashboard.update');
        Route::delete('users-dashboard/{user}', [NewAdminController::class, 'destroyUserProfile'])->middleware('throttle:admin-sensitive')->name('users-dashboard.destroy');
        Route::get('business-units-dashboard', [NewAdminController::class, 'businessUnits'])->name('business-units-dashboard');
        Route::get('countries-dashboard', [NewAdminController::class, 'countries'])->name('countries-dashboard');
        Route::get('forecasting-dashboard', [NewAdminController::class, 'forecasting'])->name('forecasting-dashboard');
        Route::get('ask-ai-dashboard', [NewAdminController::class, 'askAI'])->name('ask-ai-dashboard');
        Route::get('settings-dashboard', [NewAdminController::class, 'settingsDashboard'])->name('settings-dashboard');
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::put('ads', [AdsSettingController::class, 'update'])->name('ads.update');
            Route::put('smtp', [SmtpSettingController::class, 'update'])->name('smtp.update');
            Route::post('smtp/test', [SmtpSettingController::class, 'sendTestEmail'])->name('smtp.test');
            Route::get('legal-pages', [NewAdminController::class, 'getLegalPages'])->name('legal.index');
            Route::post('legal-pages', [NewAdminController::class, 'saveLegalPage'])->name('legal.save');
            Route::delete('legal-pages/{key}', [NewAdminController::class, 'deleteLegalPage'])->name('legal.delete');
        });
        Route::get('e-wallet-dashboard', [NewAdminController::class, 'eWalletDashboard'])->name('e-wallet-dashboard');
        Route::get('remittance-dashboard', [NewAdminController::class, 'remittanceDashboard'])->name('remittance-dashboard');
        Route::get('settlements-dashboard', [NewAdminController::class, 'settlementsDashboard'])->name('settlements-dashboard');
        Route::get('payout-ops-dashboard', [NewAdminController::class, 'payoutOpsDashboard'])->name('payout-ops-dashboard');
        Route::get('payroll-dashboard', [NewAdminController::class, 'payrollDashboard'])->name('payroll-dashboard');
        Route::get('pricing-dashboard', [NewAdminController::class, 'pricingDashboard'])->name('pricing-dashboard');
        Route::get('foundation-dashboard', [NewAdminController::class, 'foundationDashboard'])->name('foundation-dashboard');

        // Administration / Admin Users
        Route::prefix('administration')->name('administration.')->group(function () {
            Route::post('two-factor/setup', [NewAdminController::class, 'startTwoFactorSetup'])->middleware('throttle:admin-sensitive')->name('two-factor.setup');
            Route::post('two-factor/verify', [NewAdminController::class, 'verifyTwoFactorSetup'])->middleware('throttle:admin-sensitive')->name('two-factor.verify');
            Route::post('recovery-codes', [NewAdminController::class, 'generateRecoveryCodes'])->middleware('throttle:admin-sensitive')->name('recovery-codes.generate');

            Route::post('admin-users', [AdminUserController::class, 'store'])->middleware('throttle:admin-sensitive')->name('admin-users.store');
            Route::put('admin-users/{adminUser}', [AdminUserController::class, 'update'])->middleware('throttle:admin-sensitive')->name('admin-users.update');
            Route::delete('admin-users/{adminUser}', [AdminUserController::class, 'destroy'])->middleware('throttle:admin-sensitive')->name('admin-users.destroy');

            Route::post('roles', [RolePermissionController::class, 'store'])->middleware('throttle:admin-sensitive')->name('roles.store');
            Route::put('roles/{role}', [RolePermissionController::class, 'update'])->middleware('throttle:admin-sensitive')->name('roles.update');
            Route::delete('roles/{role}', [RolePermissionController::class, 'destroy'])->middleware('throttle:admin-sensitive')->name('roles.destroy');

            Route::put('event-fees', [EventFeeSettingController::class, 'update'])->middleware('throttle:admin-sensitive')->name('event-fees.update');
        });

        Route::prefix('trust/reviews')->name('trust.reviews.')->group(function () {
            Route::patch('{review}/status', [NewAdminController::class, 'updateReviewConcernStatus'])->middleware('throttle:admin-sensitive')->name('status');
            Route::delete('{review}', [NewAdminController::class, 'destroyReviewConcern'])->middleware('throttle:admin-sensitive')->name('destroy');
        });
        Route::patch('trust/kyc/{source}/{id}/status', [NewAdminController::class, 'updateKycReviewStatus'])->middleware('throttle:admin-sensitive')->name('trust.kyc.status');
        Route::patch('trust/kyc/{source}/{id}/documents/{document}/status', [NewAdminController::class, 'updateKycDocumentStatus'])->middleware('throttle:admin-sensitive')->name('trust.kyc.documents.status');
        Route::prefix('trust/flagged-users')->name('trust.flagged-users.')->group(function () {
            Route::patch('{flaggedUser}/status', [NewAdminController::class, 'updateFlaggedUserStatus'])->middleware('throttle:admin-sensitive')->name('status');
            Route::patch('{flaggedUser}/activation', [NewAdminController::class, 'updateFlaggedUserActivation'])->middleware('throttle:admin-sensitive')->name('activation');
        });

        // Finance / Taxes
        Route::prefix('finance/taxes')->name('finance.taxes.')->group(function () {
            Route::get('management', [TaxesController::class, 'management'])->name('management');
            Route::post('management', [TaxesController::class, 'store'])->name('store');
            Route::put('management/{tax}', [TaxesController::class, 'update'])->name('update');
            Route::delete('management/{tax}', [TaxesController::class, 'destroy'])->name('destroy');
            Route::get('dashboard', [TaxesController::class, 'dashboard'])->name('dashboard');
            Route::get('authority-apis', [TaxesController::class, 'authorityApis'])->name('authority-apis');
            Route::get('corporate-tax', [TaxesController::class, 'corporateTax'])->name('corporate-tax');
            Route::get('settings', [TaxesController::class, 'settings'])->name('settings');
            Route::post('settings', [TaxesController::class, 'saveSettings'])->middleware('throttle:admin-sensitive')->name('settings.save');
            Route::get('remittance-settings', [TaxesController::class, 'remittanceSettings'])->name('remittance-settings');
            Route::get('remittance-center', [TaxesController::class, 'remittanceCenter'])->name('remittance-center');
            Route::post('remittance-center', [TaxesController::class, 'saveRemittanceCenter'])->middleware('throttle:admin-sensitive')->name('remittance-center.save');
            Route::delete('remittance-center/{taxRemittanceCenter}', [TaxesController::class, 'deleteRemittanceCenter'])->middleware('throttle:admin-sensitive')->name('remittance-center.destroy');
        });

        // Event Management
        Route::get('events-list', [NewAdminController::class, 'eventsList'])->name('events-list');
        Route::get('event-categories', [NewAdminController::class, 'eventCategories'])->name('event-categories');
        Route::get('ticket-sales', [NewAdminController::class, 'ticketSales'])->name('ticket-sales');
        Route::get('event-sponsors', [NewAdminController::class, 'eventSponsors'])->name('event-sponsors');
        Route::get('event-coupons', [NewAdminController::class, 'eventCoupons'])->name('event-coupons');
        Route::get('tickets-report', [NewAdminController::class, 'ticketsReport'])->name('tickets-report');
        Route::get('cancel-tickets', [NewAdminController::class, 'cancelTickets'])->name('cancel-tickets');

        // Event Organizer
        Route::get('organizer-directory', [NewAdminController::class, 'organizerDirectory'])->name('organizer-directory');
        Route::get('promote-events', [NewAdminController::class, 'promoteEvents'])->name('promote-events');
        Route::controller(NewAdminController::class)->group(function () {
            Route::get('scanners-management', 'scannersManagement')->name('scanners-management');
            Route::post('scanners-management', 'storeScanner')->middleware('throttle:admin-sensitive')->name('new_admin.scanners.store');
            Route::put('scanners-management/{id}', 'updateScanner')->middleware('throttle:admin-sensitive')->name('new_admin.scanners.update');
            Route::delete('scanners-management/{id}', 'destroyScanner')->middleware('throttle:admin-sensitive')->name('new_admin.scanners.destroy');
        });
        Route::get('payout-list', [NewAdminController::class, 'payoutList'])->name('payout-list');

        // LinkUp Live
        Route::get('live-dashboard', [NewAdminController::class, 'liveDashboard'])->name('live-dashboard');
        Route::get('top-earners', [NewAdminController::class, 'topEarners'])->name('top-earners');
        Route::get('wellness-dashboard', [NewAdminController::class, 'wellnessDashboard'])->name('wellness-dashboard');

        // LinkUp Eats
        Route::prefix('commerce/eats')->name('commerce.eats.')->group(function () {
            Route::get('dashboard', [NewAdminController::class, 'eatsDashboard'])->name('dashboard');
            Route::get('live-app', [NewAdminController::class, 'eatsLiveApp'])->name('live-app');
            Route::get('restaurants', [NewAdminController::class, 'eatsRestaurants'])->name('restaurants');
            Route::get('menus', [NewAdminController::class, 'eatsMenus'])->name('menus');
            Route::get('orders', [NewAdminController::class, 'eatsOrders'])->name('orders');
            Route::get('drivers', [NewAdminController::class, 'eatsDrivers'])->name('drivers');
            Route::get('driver-payouts', [NewAdminController::class, 'eatsDriverPayouts'])->name('driver-payouts');
            Route::get('restaurant-payouts', [NewAdminController::class, 'eatsRestaurantPayouts'])->name('restaurant-payouts');
            Route::get('promotions', [NewAdminController::class, 'eatsPromotions'])->name('promotions');
            Route::get('delivery-zones', [NewAdminController::class, 'eatsDeliveryZones'])->name('delivery-zones');
            Route::get('fees-pricing', [NewAdminController::class, 'eatsFeesPricing'])->name('fees-pricing');
            Route::get('support', [NewAdminController::class, 'eatsSupport'])->name('support');
            Route::get('reviews', [NewAdminController::class, 'eatsReviews'])->name('reviews');
            Route::get('analytics', [NewAdminController::class, 'eatsAnalytics'])->name('analytics');
            Route::get('oversight', [NewAdminController::class, 'restaurantOversight'])->name('oversight');
            Route::get('onboarding', [NewAdminController::class, 'restaurantOnboarding'])->name('onboarding');
            Route::get('merchant-pay-onboarding', [NewAdminController::class, 'merchantPayOnboarding'])->name('merchant-pay-onboarding');
        });

        Route::prefix('commerce')->name('commerce.')->group(function () {
            Route::get('deliveries-dispatch', [NewAdminController::class, 'deliveriesDispatch'])->name('deliveries-dispatch');
            Route::get('driver-onboarding', [NewAdminController::class, 'driverOnboarding'])->name('driver-onboarding');
            Route::get('driver-policies', [NewAdminController::class, 'driverPolicies'])->name('driver-policies');
            Route::get('shipping', [NewAdminController::class, 'shippingManagement'])->name('shipping');
            Route::get('subscriptions', [NewAdminController::class, 'subscriptionSuite'])->name('subscriptions');
            Route::get('swipes', [NewAdminController::class, 'swipesManagement'])->name('swipes');
            Route::get('vibes-management', [NewAdminController::class, 'vibesManagement'])->name('vibes-management');

            Route::prefix('marketplace')->name('marketplace.')->group(function () {
                Route::get('dashboard', [NewAdminController::class, 'marketplaceDashboard'])->name('dashboard');
                Route::resource('products', \App\Http\Controllers\Admin\NewAdmin\MarketplaceProductController::class)->names([
                    'index' => 'products',
                ]);
                Route::resource('categories', \App\Http\Controllers\Admin\NewAdmin\MarketplaceCategoryController::class)->names([
                    'index' => 'categories',
                ]);
                Route::get('fees', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceSettingController::class, 'index'])->name('fees');
                Route::post('fees', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceSettingController::class, 'update'])->middleware('throttle:admin-sensitive')->name('fees.update');
                Route::get('orders', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceOrderController::class, 'index'])->name('orders');
                Route::get('orders/{order}', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceOrderController::class, 'show'])->name('orders.show');
                Route::post('orders/{order}/status', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceOrderController::class, 'updateStatus'])->middleware('throttle:admin-sensitive')->name('orders.update.status');

                Route::get('escrow', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceEscrowController::class, 'index'])->name('escrow');
                Route::post('escrow/{id}/release', [\App\Http\Controllers\Admin\NewAdmin\MarketplaceEscrowController::class, 'release'])->middleware('throttle:admin-sensitive')->name('escrow.release');

                Route::get('transfers', [NewAdminController::class, 'marketplaceSellerTransfers'])->name('transfers');
                Route::get('wallets', [NewAdminController::class, 'marketplaceWallets'])->name('wallets');
                Route::get('sellers', [NewAdminController::class, 'marketplaceSellers'])->name('sellers');
            });

            Route::prefix('merchants')->name('merchants.')->group(function () {
                Route::get('dashboard', [NewAdminController::class, 'merchantDashboard'])->name('dashboard');
                Route::get('directory', [NewAdminController::class, 'merchantDirectory'])->name('directory');
                Route::get('onboarding', [NewAdminController::class, 'merchantOnboarding'])->name('onboarding');
                Route::get('kyc', [NewAdminController::class, 'merchantKyc'])->name('kyc');
                Route::get('tax', [NewAdminController::class, 'merchantTax'])->name('tax');
            });
        });
    });
});
