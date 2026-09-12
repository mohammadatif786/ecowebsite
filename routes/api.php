<?php

use App\Http\Controllers\Frontend\ProfileController as webUserProfile;
use App\Http\Controllers\Frontend\WalletController as WebWalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\ChatController;
use App\Http\Controllers\V1\NewsController;
use App\Http\Controllers\V1\EventController;
use App\Http\Controllers\V1\HomePageController;
use App\Http\Controllers\V1\TicketSaleController;
use App\Http\Controllers\V1\AppPolociesController;
use App\Http\Controllers\V1\AppWalletController;
use App\Http\Controllers\V1\NotificationsController;
use App\Http\Controllers\V1\Organizer\DashboardController;
use App\Http\Controllers\V1\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\V1\Organizer\EventReportController;
use App\Http\Controllers\V1\Organizer\EventTicketDrinkController;
use App\Http\Controllers\V1\Organizer\PayoutApiController;
use App\Http\Controllers\V1\Organizer\ProfileController as OrganizerProfileController;
use App\Http\Controllers\V1\Organizer\ReportController;
use App\Http\Controllers\V1\Organizer\ReviewController;
use App\Http\Controllers\V1\Organizer\ScannerController;
use App\Http\Controllers\V1\Organizer\PointOfSaleController;
use App\Http\Controllers\V1\Organizer\TicketController;
use App\Http\Controllers\V1\Organizer\DrinkPackageController;
use App\Http\Controllers\V1\UserMatchesComtroller;
use App\Http\Controllers\V1\SubscriptionController;
use App\Http\Controllers\Api\UserWizardController;


// profile tab section api on web end not for mobile end
Route::get('ticket-tab/{user}', [webUserProfile::class, 'ticketTab']);
Route::get('marke-tplace-tab/{user}', [webUserProfile::class, 'marketPlaceTab']);
Route::get('matches-tab/{user}', [webUserProfile::class, 'matchesTab']);
Route::get('subscription-tab/{user}', [webUserProfile::class, 'subscriptionTab']);
Route::get('linkup-coin-tab/{user}', [webUserProfile::class, 'LinkUpCoinTab']);
Route::get('organizer-tab/{user}', [webUserProfile::class, 'OrganizerTab']);
Route::get('wallet-tab/{user}', [webUserProfile::class, 'walletTab']);
Route::get('linkup-live-tab/{user}', [webUserProfile::class, 'linkUpLive']);
Route::get('news-tab/{user}', [webUserProfile::class, 'newsTab']);




// for sign-up
Route::post('/email-sign-up', [AuthController::class, 'signUpWithEmail']);
Route::post('/phone-sign-up', [AuthController::class, 'signUpWithPhone']);

// for login
Route::post('/login', [AuthController::class, 'login']);
// for forgot password
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Stripe redirects the browser here after Checkout with no Authorization header,
// so it can't sit behind auth:sanctum — see EventController::buyTicketStripeSuccess
// for how the callback is trusted without one.
Route::get('/stripe/ticket-purchase/success', [EventController::class, 'buyTicketStripeSuccess'])->name('api.stripe.ticket.buy.success');




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });

    Route::get('/withdrawal-settings', [WebWalletController::class, 'withdrawalSettings']);

    // for logout
    Route::get('/logout', [AuthController::class, 'logout']);
    // for remove account
    Route::get('/remove-account', [AuthController::class, 'deleteAccount']);
    // for change password
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    // for update the user data
    Route::post('update-user', [AuthController::class, 'updateUser']);
    // for the user detail
    Route::get('user-detail', [AuthController::class, 'getUserDetail']);
    // for the specific user detail
    Route::get('specific-user/{id}', [AuthController::class, 'getSpecificUserDetail']);


    // for link-up matches routes
    Route::prefix('match/')->group(function () {
        // for find user matches
        Route::get('find', [UserMatchesComtroller::class, 'findmatches']);
        // for user matches
        Route::get('user-matches', [UserMatchesComtroller::class, 'getMatches']);
        // for link up user
        Route::get('linkup-user/{id}', [UserMatchesComtroller::class, 'getLinkupUser']);
        // for mutual matches
        Route::get('mutual', [UserMatchesComtroller::class, 'getMutualMatches']);
    });


    // for news routes
    Route::prefix('news/')->group(function () {
        // for find user matches
        Route::get('all', [NewsController::class, 'getAllNews']);
        // for user matches
        Route::get('specific/{id}', [NewsController::class, 'getSpecificNews']);
    });


    // for events routes
    Route::prefix('event/')->group(function () {
        // for getting all events
        Route::get('all', [EventController::class, 'getAllEvents']);
        // for event categories
        Route::get('category', [EventController::class, 'getAllEventCategories']);
        // for specific category events
        Route::get('specific-category/{id}', [EventController::class, 'getAllEventsByCategory']);
        // for searching events
        Route::post('search', [EventController::class, 'searchEvents']);
        // for getting specific event
        Route::get('specific-event/{id}', [EventController::class, 'getSpecificEvent']);
        // for getting specific event tickets
        Route::get('event-ticket/{id}', [TicketSaleController::class, 'getSpecificEventTickets']);
        // for creating payent intant of ticket
        Route::post('ticket-intant/', [TicketSaleController::class, 'ticketIntant']);
        // for saving successfull responce of stripe
        Route::post('success-stripe/', [TicketSaleController::class, 'successfullyTicket']);
        // for paying with wallet
        Route::post('wallet-payment/', [TicketSaleController::class, 'payWithWallet']);
        // for getting specific event coupon
        Route::get('specific-coupon/{id}', [EventController::class, 'getSpecificEventCoupon']);
        // for getting trending event
        Route::post('trending', [EventController::class, 'getTrendingEvent']);
        // for getting upcoming event
        Route::post('upcoming', [EventController::class, 'getUpcomingEvent']);
        // for getting near by event
        Route::get('near-by', [EventController::class, 'getNearByEvent']);
        //mark as a favourite
        Route::post('favourite', [EventController::class, 'markFavouriteEvent']);
        //get all favourite
        Route::get('favourite/all', [EventController::class, 'getFavouriteEvent']);
    });

    // for wallet routes
    Route::prefix('wallet/')->group(function () {
        // for getting all wallet detail
        Route::get('all', [AppWalletController::class, 'index']);
        // for getting balance
        Route::get('get-amount', [AppWalletController::class, 'addAmoutPage']);
        // for adding balance
        Route::post('add-amount', [AppWalletController::class, 'submitAmountToWallet']);
        // after successful payment data save into database
        Route::post('save-data', [AppWalletController::class, 'oneTimePaySuccess']);
        // coin purchase routes
        Route::post('coin/checkout', [AppWalletController::class, 'submitCoinToWallet']);
        Route::post('coin/save-data', [AppWalletController::class, 'oneTimePayCoinSuccess']);
    });

    Route::get('user/chat', [ChatController::class, 'index']);
    Route::get('/chat/{toUserId}', [ChatController::class, 'singleUser']);
    Route::post('/chat/{toUserId}/messages', [ChatController::class, 'store']);

    // for chat routes
    Route::prefix('subscription/')->group(function () {
        // for getting all subscriptions
        Route::get('all', [SubscriptionController::class, 'getSubscriptionPlans']);
        // successfull subscription
        Route::post('success', [AppWalletController::class, 'subscriptionSuccess']);
        // for getting  subscription payment intent
        Route::post('payment-intent', [AppWalletController::class, 'createSubscriptionPaymentIntent']);
        // for getting subscription status
        Route::get('get-status', [SubscriptionController::class, 'getSubscriptionStatus']);
    });


    // for getting bookings
    Route::post('all-booking', [TicketSaleController::class, 'getAllBooking']);
    //get event e ticket
    Route::get('eticket/{id}', [TicketSaleController::class, 'getETicket']);
    //update the booking status
    Route::post('update-booking', [TicketSaleController::class, 'updateBookingStatus']);

    // for getting all app polocies
    Route::get('app-polocies', [AppPolociesController::class, 'getAppPolocies']);
    Route::get('all-notifications', [NotificationsController::class, 'getAllNotifications'])->name('notifications.all');


    // organizer apis
    Route::middleware(['api.check.organizer'])->group(
        function () {

            Route::get('profile', [OrganizerProfileController::class, 'index']);

            Route::middleware(['api.check.organizer.kyc'])->group(function () {

                Route::get('dashboard', [DashboardController::class, 'index']);
                Route::get('dashboard/event/{eventId}', [DashboardController::class, 'eventDashboard']);
                Route::get('cookout/dashboard', [DashboardController::class, 'cookoutDashboard']);
                Route::get('wallness-spa/dashboard', [DashboardController::class, 'wallnessSpaDashboard']);
                // events
                Route::get('event', [OrganizerEventController::class, 'index']);
                Route::get('categories/scanners', [OrganizerEventController::class, 'getCategoriesScanners']);
                Route::get('event-createdata', [OrganizerEventController::class, 'createData']);
                Route::post('event-store', [OrganizerEventController::class, 'store']);
                Route::get('event/{event}', [OrganizerEventController::class, 'show']);
                Route::get('event-edit/{event}', [OrganizerEventController::class, 'edit']);
                Route::post('event-update/{event}', [OrganizerEventController::class, 'update']);
                Route::delete('event-destroy/{event}', [OrganizerEventController::class, 'destroy']);
                Route::patch('event-toggleStatus/{event_id}', [OrganizerEventController::class, 'toggleStatus']);
                Route::post('event-duplicate/{event}', [OrganizerEventController::class, 'duplicate']);
                Route::get('/get-tax-rate', [OrganizerEventController::class, 'getTaxRate']);

                // api for Sponsor CRUD routes
                Route::get('sponsors', [OrganizerEventController::class, 'sponsorIndex']);
                Route::post('sponsor/store', [OrganizerEventController::class, 'sponsorStore']);
                Route::post('sponsor/{sponsor_id}/update', [OrganizerEventController::class, 'sponsorUpdate']);
                Route::delete('sponsor/{sponsor_id}', [OrganizerEventController::class, 'sponsorDestroy']);
                Route::patch('sponsor/{sponsor_id}/toggle-status', [OrganizerEventController::class, 'sponsorToggleStatus']);

                //Api for Coupon CRUD routes
                Route::get('coupons', [OrganizerEventController::class, 'couponIndex']);
                Route::post('coupon/store', [OrganizerEventController::class, 'couponStore']);
                Route::post('coupon/{coupons_id}/update', [OrganizerEventController::class, 'couponUpdate']);
                Route::delete('coupon/{coupon_id}', [OrganizerEventController::class, 'couponDestroy']);
                Route::patch('coupon/{coupon_id}/toggle-status', [OrganizerEventController::class, 'couponToggleStatus']);

                // Event Report APIs
                Route::get('event/{event}/attendees', [EventReportController::class, 'attendees']);
                Route::get('event/{event}/attendees/export', [EventReportController::class, 'export']);
                Route::get('event/{event}/attendees/print', [EventReportController::class, 'print']);
                Route::post('event/{event}/resend', [EventReportController::class, 'resend']);
                Route::get('event/{event}/drinks-inventory', [EventReportController::class, 'drinksInventory']);
                Route::get('event/{event}/revenue', [EventReportController::class, 'revenue']);
                Route::get('ticket/{ticketSale}/check-in', [EventReportController::class, 'checkIn']);
                Route::post('event/{event}/message', [EventReportController::class, 'message']);
                Route::get('event/{event}/statistics', [EventReportController::class, 'eventStatistics']);
                Route::get('reports/statistics', [EventReportController::class, 'statistics']);
                Route::get('reports/statistics/export', [EventReportController::class, 'exportStatistics']);
                Route::get('report/event/{ticket_sale_id}/ticket', [EventReportController::class, 'printTicketSale']);
                Route::get('report/event/{ticket_sale_id}/payment/detail', [EventReportController::class, 'paymentDetail']);
                Route::get('report/event/{ticket_sale_id}/order/detail', [EventReportController::class, 'orderDetail']);

                //payout
                Route::controller(PayoutApiController::class)->prefix('/payout')->name('payout.')->group(function () {
                    Route::get('/request', 'request')->name('request');
                    Route::get('/method', 'method')->name('method');
                    Route::post('/store/paypal', 'storePayPal')->name('store.paypal');
                    Route::post('/store/stripe', 'storeStripe')->name('store.stripe');
                    Route::post('/store/bank', 'storeBank')->name('store.bank');
                    Route::post('/create', 'store')->name('create');
                });
                // Profile APIs
                Route::post('profile/details', [OrganizerProfileController::class, 'updateProfileDetails']);
                Route::post('profile/additional-details', [OrganizerProfileController::class, 'updateAdditionalDetails']);
                Route::post('profile/contacts', [OrganizerProfileController::class, 'updateContacts']);
                Route::post('profile/settings', [OrganizerProfileController::class, 'updateSettings']);
                Route::post('profile/bank-accounts', [OrganizerProfileController::class, 'updateBankAccounts']);
                Route::post('profile/kyc', [OrganizerProfileController::class, 'updateKYC']);

                // my Report APIs
                Route::get('report/event/{event}/attendees', [ReportController::class, 'attendees']);
                Route::get('report/event/{event}/statistics', [ReportController::class, 'statistics']);
                Route::get('report/event/{event}/export-attendees', [ReportController::class, 'exportAttendees']);
                Route::get('report/event/{event}/print-tickets', [ReportController::class, 'printTickets']);
                Route::get('report/sales-summary', [ReportController::class, 'salesSummary']);
                Route::get('report/revenue-trends', [ReportController::class, 'revenueTrends']);

                // Scanner APIs
                Route::get('scanners', [ScannerController::class, 'index']);
                Route::post('scanner/store', [ScannerController::class, 'store']);
                Route::post('scanner/{id}/update', [ScannerController::class, 'update']);
                Route::delete('scanner/{id}', [ScannerController::class, 'destroy']);
                Route::patch('scanner/{id}/toggle-status', [ScannerController::class, 'toggleStatus']);

                Route::post('scanner/{id}/assign-role', [ScannerController::class, 'assignRole']);
                Route::post('scanner/{id}/remove-role', [ScannerController::class, 'removeRole']);
                Route::get('scanner/{id}/permissions', [ScannerController::class, 'getPermissions']);
                Route::post('scanner/{id}/permissions', [ScannerController::class, 'assignPermissions']);

                // Mobile scanner APIs
                Route::post('scanner/mobile-login', [ScannerController::class, 'mobileLogin']);
                Route::post('scanner/scan-ticket', [ScannerController::class, 'scanTicket']);

                // Point of Sale APIs
                Route::get('points-of-sale', [PointOfSaleController::class, 'index']);
                Route::post('point-of-sale/store', [PointOfSaleController::class, 'store']);
                Route::post('point-of-sale/{id}/update', [PointOfSaleController::class, 'update']);
                Route::delete('point-of-sale/{id}', [PointOfSaleController::class, 'destroy']);
                Route::patch('point-of-sale/{id}/toggle-status', [PointOfSaleController::class, 'toggleStatus']);

                // Review APIs
                Route::get('reviews', [ReviewController::class, 'index']);

                // Ticket APIs
                Route::get('tickets/create-data', [TicketController::class, 'index']);
                Route::get('tickets', [TicketController::class, 'getAllTickets']);
                Route::get('tickets/getting', [TicketController::class, 'gettingTickets']);
                Route::get('tickets/event/{event_id}', [TicketController::class, 'getEventTickets']);
                Route::get('ticket/{id}', [TicketController::class, 'show']);
                Route::post('ticket/store-or-update', [TicketController::class, 'storeOrUpdate']);
                Route::delete('ticket/{id}', [TicketController::class, 'destroy']);

                // Ticket Extras
                Route::post('ticket-extras/bulk', [TicketController::class, 'bulkExtras']);
                Route::post('ticket-extras/{ticket}/upsert', [TicketController::class, 'upsertExtras']);

                // Wellness Slot Blocks
                Route::get('wellness-slot-blocks/{ticket}', [TicketController::class, 'getWellnessBlocks']);
                Route::post('wellness-slot-blocks/{ticket}', [TicketController::class, 'storeWellnessBlock']);
                Route::delete('wellness-slot-blocks/{ticket}/date', [TicketController::class, 'clearWellnessDate']);
                Route::delete('wellness-slot-blocks/{ticket}/all', [TicketController::class, 'clearAllWellnessBlocks']);

                // Ticket Drink APIs
                Route::get('ticket-drinks', [EventTicketDrinkController::class, 'index']);
                Route::post('ticket-drink/store', [EventTicketDrinkController::class, 'store']);
                Route::post('ticket-drink/{id}/update', [EventTicketDrinkController::class, 'update']);
                Route::delete('ticket-drink/{id}', [EventTicketDrinkController::class, 'destroy']);

                //Ticket package Apis
                Route::get('ticket-packages', [DrinkPackageController::class, 'index']);
                Route::post('ticket-package/store', [DrinkPackageController::class, 'store']);
                Route::get('ticket-package/{id}', [DrinkPackageController::class, 'show']);
                Route::post('ticket-package/{id}/update', [DrinkPackageController::class, 'update']);
                Route::delete('ticket-package/{id}', [DrinkPackageController::class, 'destroy']);
            });
        }
    );

});

require __DIR__ . '/regularUserApi.php';
