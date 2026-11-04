<?php

use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\DashboardController;
use App\Http\Controllers\Organizer\EventTicketDrinkController;
use App\Http\Controllers\Organizer\EventReportController;
use App\Http\Controllers\Organizer\PayoutController;
use App\Http\Controllers\Organizer\PointOfSaleController;
use App\Http\Controllers\Organizer\ProfileController;
use App\Http\Controllers\Organizer\ReviewController;
use App\Http\Controllers\Organizer\ScannerController;
use App\Http\Controllers\Organizer\TicketController;
use App\Http\Controllers\Organizer\TicketExtraSettingController;
use App\Http\Controllers\Organizer\WellnessSlotBlockController;
use App\Http\Controllers\DrinkPackageController;
use Illuminate\Support\Facades\Route;

Route::controller(ScannerController::class)->prefix('organizer/scanner')->name('organizer.scanner.')->group(function () {
    Route::get('/scan', 'scanView')->name('scan-view');
    Route::post('/scan-ticket', 'scanTicket')->name('scan-ticket');
});

Route::prefix('organizer')->middleware(['auth', 'otp.verified', 'isWizardComplete'])->name('organizer.')->group(function () {

    Route::controller(DashboardController::class)->middleware(['check.organizer'])->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/cookout/dashboard', 'cookoutDashboard')->name('cookout.dashboard');
        Route::get('/wallness-spa/dashboard', 'wallnessSpaDashboard')->name('wallness-spa.dashboard');
    });


    Route::controller(ProfileController::class)->prefix('/profile')->name('profile.')->group(function () {

        Route::get('/index', 'index')->name('index');
        Route::post('/update/{organizer_profile_type}', 'update')->name('update')->whereIn('organizer_profile_type', [
            'profileDetail',
            'AdditionalDetails',
            'profileContacts',
            'ProfileSetting',
            'profileBankAccounts',
            'profileKYC',
        ]);
    });

    Route::middleware(['check.organizer'])->group(function () {

        Route::middleware(['check.kyc'])->group(function () {

            Route::controller(EventController::class)->prefix('/event')->name('event.')->group(function () {

                Route::get('/index', 'index')->name('index')->middleware('can:organizer events');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store')->middleware('can:organizer events');
                // Keep static tax endpoints before /{event:slug}; otherwise
                // Laravel treats endpoint names as event slugs and returns 404.
                Route::post('/get-tax-rate', 'getTaxRate')->name('get-tax-rate')->middleware('can:organizer events');
                Route::get('/get-local-tax-countries', 'getLocalTaxCountries')->name('get-local-tax-countries')->middleware('can:organizer events');
                Route::get('/get-api-supported-countries', 'getApiSupportedCountries')->name('get-api-supported-countries')->middleware('can:organizer events');
                Route::get('/{event:slug}', 'show')->name('show')->middleware('can:organizer events');
                Route::get('/{event:slug}/edit', 'edit')->name('edit')->middleware('can:organizer events');
                Route::post('/{event}', 'update')->name('update')->middleware('can:organizer events');
                Route::delete('/{event}/delete', 'delete')->name('delete')->middleware('can:organizer events');
                Route::patch('/{event_id}/toggle-status', 'toggleStatus')->name('toggle-status')->middleware('can:organizer events');
                Route::post('/{event}/duplicate', 'duplicate')->name('duplicate')->middleware('can:organizer events');
                // Sponsor CRUD routes
                Route::get('/create/sponsor', 'sponsorCreate')->name('sponsor.create')->middleware('can:organizer sponsors');
                Route::get('/sponsors/index', 'sponsorIndex')->name('sponsor.index')->middleware('can:organizer sponsors');
                Route::get('/sponsor/{sponsor_id}', 'sponsorShow')->name('sponsor.show')->middleware('can:organizer sponsors');
                Route::get('/sponsor/{sponsor_id}/edit', 'sponsorEdit')->name('sponsor.edit')->middleware('can:organizer sponsors');
                Route::get('/get-sponsor/{event_id}', 'getSponsor')->name('sponsor.get')->middleware('can:organizer sponsors');
                Route::post('/sponsor/store', 'sponsorStore')->name('sponsor.store')->middleware('can:organizer sponsors');
                Route::post('/sponsor/{sponsor_id}/update', 'sponsorUpdate')->name('sponsor.update')->middleware('can:organizer sponsors');
                Route::delete('/sponsor/{sponsor_id}', 'sponsorDestroy')->name('sponsor.destroy')->middleware('can:organizer sponsors');
                Route::patch('/sponsor/{sponsor_id}/toggle-status', 'sponsorToggleStatus')->name('sponsor.toggle-status')->middleware('can:organizer sponsors');

                // Coupon CRUD routes
                Route::get('/create/coupons', 'couponsCreate')->name('coupons.create')->middleware('can:organizer coupons');
                Route::get('/coupons/index', 'couponIndex')->name('coupon.index')->middleware('can:organizer coupons');
                Route::get('/coupon/{coupon_id}', 'couponShow')->name('coupon.show')->middleware('can:organizer coupons');
                Route::get('/coupon/{coupon_id}/edit', 'couponEdit')->name('coupon.edit')->middleware('can:organizer coupons');
                Route::get('/get-coupon/{event_id}', 'getCoupons')->name('coupon.get')->middleware('can:organizer coupons');
                Route::post('/coupon/store', 'couponStore')->name('coupon.store')->middleware('can:organizer coupons');
                Route::post('/coupon/{coupons_id}/update', 'couponUpdate')->name('coupon.update')->middleware('can:organizer coupons');
                Route::delete('/coupon/{coupon_id}', 'couponDestroy')->name('coupon.destroy')->middleware('can:organizer coupons');
                Route::patch('/coupon/{coupon_id}/toggle-status', 'couponToggleStatus')->name('coupon.toggle-status')->middleware('can:organizer coupons');
            });

            // Event Report routes
            Route::controller(EventReportController::class)->prefix('/event')->name('event.report.')->middleware('can:organizer reports')->group(function () {
                Route::get('/{event:slug}/attendees', 'attendees')->name('attendees');
                Route::get('/{event:slug}/statistics', 'statistics')->name('statistics');
                Route::get('/{event:slug}/export', 'export')->name('export');
                Route::get('/{event:slug}/print', 'print')->name('print');
                Route::post('/{event:slug}/resend', 'resend')->name('resend');
                Route::post('/{event:slug}/message', 'message')->name('message');
                Route::get('/{event:slug}/drinks-inventory', 'drinksInventory')->name('drinks-inventory');
            });

            // Individual attendee actions
            Route::controller(EventReportController::class)->name('event.report.')->group(function () {
                Route::patch('/attendee/{ticketSale}/check-in', 'checkIn')->name('check-in')->middleware('can:organizer scan ticket');
            });

            Route::controller(TicketController::class)->prefix('/ticket')->name('ticket.')->middleware('can:organizer events')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/storeOrUpdate', 'storeOrUpdate')->name('store.update');
                Route::delete('/{ticket}', 'destroy')->name('destroy');
                Route::get('/getting/tickets', 'gettingTickets')->name('getting');
            });

            Route::controller(TicketExtraSettingController::class)->prefix('/ticket-extras')->name('ticket.extras.')->middleware('can:organizer events')->group(function () {
                Route::post('/bulk', 'bulk')->name('bulk');
                Route::post('/{ticket}', 'upsert')->name('upsert');
            });

            Route::controller(WellnessSlotBlockController::class)->prefix('/wellness-slot-blocks')->name('wellness.blocks.')->middleware('can:organizer events')->group(function () {
                Route::get('/{ticket}', 'index')->name('index');
                Route::post('/{ticket}', 'store')->name('store');
                Route::delete('/{ticket}/date', 'clearDate')->name('clearDate');
                Route::delete('/{ticket}/all', 'clearAll')->name('clearAll');
            });

            Route::controller(ScannerController::class)->prefix('/scanner')->name('scanner.')->group(function () {

                Route::get('/', 'index')->name('index')->middleware('can:organizer scanners');
                Route::get('/app-setting', 'appSetting')->name('app.setting')->middleware('can:organizer scanner app setting');
                Route::post('/app-setting', 'appSettingStore')->name('app.setting.store')->middleware('can:organizer scanner app setting');
                Route::post('/', 'store')->name('store')->middleware('can:organizer scanners');
                Route::put('/{scanner}', 'update')->name('update')->middleware('can:organizer scanners');
                Route::delete('/{scanner}', 'destroy')->name('destroy')->middleware('can:organizer scanners');
                Route::patch('/{scanner}/toggle', 'toggleStatus')->name('toggle')->middleware('can:organizer scanners');
                Route::post('/{scanner}/assign-role', 'assignRole')->name('assign-role')->middleware('can:organizer scanner assign role');
                Route::post('/{scanner}/remove-role', 'removeRole')->name('remove-role')->middleware('can:organizer scanner assign role');
                Route::get('/{scanner}/permissions', 'getPermissions')->name('permissions.get')->middleware('can:organizer scanners');
                Route::post('/{scanner}/permissions', 'assignPermissions')->name('permissions.assign')->middleware('can:organizer scanners');
            });

            Route::controller(PointOfSaleController::class)->prefix('/pos')->name('pos.')->middleware('can:organizer pos')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/fees', 'feeSummary')->name('fees');
                Route::post('/charge', 'charge')->name('charge')->middleware('throttle:admin-sensitive');
                Route::post('/', 'store')->name('store');
                Route::put('/{id}', 'update')->name('update');
                Route::patch('/{id}/toggle', 'toggleStatus')->name('toggle');
                Route::delete('/{id}', 'destroy')->name('destroy');
            });

            Route::controller(ReviewController::class)->prefix('/review')->name('review.')->middleware('can:organizer reviews')->group(function () {

                Route::get('/', 'index')->name('index');
            });

            Route::controller(PayoutController::class)->prefix('/payout')->name('payout.')->group(function () {

                Route::get('/request', 'request')->name('request')->middleware('can:organizer payouts');
                Route::get('/method', 'method')->name('method')->middleware('can:organizer payout methods');
                Route::post('/store/paypal', 'storePayPal')->name('store.paypal')->middleware('can:organizer payout methods');
                Route::post('/store/stripe', 'storeStripe')->name('store.stripe')->middleware('can:organizer payout methods');
                Route::post('/store/bank', 'storeBank')->name('store.bank')->middleware('can:organizer payout methods');
                Route::post('/create', 'store')->name('create')->middleware('can:organizer payouts');

                Route::get('/verify/transfer/{transfer_id}', 'verifyTransfer')->name('verify.transfer')->middleware('can:organizer payouts');
                Route::get('/verified/transfer/{payout_id}', 'verifiedTransfer')->name('verified.transfer')->middleware('can:organizer payouts');
                Route::get('/download/pdf/{payout_id}', 'downloadPayoutPdf')->name('download.pdf')->middleware('can:organizer payouts');
            });

            Route::controller(EventTicketDrinkController::class)->name('drink.')->middleware('can:organizer events')->group(function () {
                Route::post('/create-new', 'createUpdate')->name('createUpdate');
                Route::delete('/remove/{drink}', 'Remove')->name('remove');
                Route::put('/update/{id}', 'update')->name('update');
            });

            Route::controller(EventReportController::class)->name('report.')->middleware('can:organizer reports')->group(function () {
                Route::get('/statistics', 'Reportstatistics')->name('statistics');
                Route::get('/statistics/export', 'exportStatistics')->name('export');
            });

            Route::post('/send/message/attendees/broadcast', [EventReportController::class, 'sendMessageAttendeesBroadcast'])->name('send.message.attendees.broadcast')->middleware('can:organizer reports');

            Route::controller(DrinkPackageController::class)->name('drink.package.')->middleware('can:organizer events')->group(function () {
                Route::get('/packages', 'index')->name('index');
                Route::post('/save', 'store')->name('store');
                Route::get('/packages/{id}', 'show')->name('show');
                Route::post('/packages/{id}', 'update')->name('update');
                Route::delete('/packages/{id}', 'destroy')->name('destroy');
            });
        });
    });
});
