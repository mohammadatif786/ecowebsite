<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AppSettingController;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\KycUserController;
use App\Http\Controllers\Admin\UserReportsController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\CashoutController;
use App\Http\Controllers\Admin\ClubFeteController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\AdminFeeController;
use App\Http\Controllers\Admin\BankWithdrawalController;
use App\Http\Controllers\Admin\CancelTicketController;
use App\Http\Controllers\Admin\Dashboards\EventDashboardController;
use App\Http\Controllers\Admin\Dashboards\FeeDashboardController;
use App\Http\Controllers\Admin\Dashboards\LiveAnalyticsDashboardController;
use App\Http\Controllers\Admin\Dashboards\ModerationDashboardController;
use App\Http\Controllers\Admin\Dashboards\UserDashboardController;
use App\Http\Controllers\Admin\Dashboards\WalletDashboardController;
use App\Http\Controllers\Admin\e_wallet\DashboardController;
use App\Http\Controllers\Admin\e_wallet\DigiMoneyController;
use App\Http\Controllers\Admin\e_wallet\MoneyRequestController;
use App\Http\Controllers\Admin\e_wallet\PaymentGatewayController;
use App\Http\Controllers\Admin\e_wallet\TransactionController;
use App\Http\Controllers\Admin\e_wallet\WithdrawRequestController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventOrganizerController;
use App\Http\Controllers\admin\InvoiceController;
use App\Http\Controllers\Admin\LiveStream\CallController;
use App\Http\Controllers\Admin\LiveStream\EncounterController;
use App\Http\Controllers\Admin\LiveStream\GiftController;
use App\Http\Controllers\Admin\LiveStream\LiveStreamController;
use App\Http\Controllers\Admin\LiveStream\MessageController;
use App\Http\Controllers\Admin\LiveStream\PaymentController;
use App\Http\Controllers\Admin\LiveStream\PayoutController;
use App\Http\Controllers\Admin\LiveStream\LinkupLiveGiftController;
use App\Http\Controllers\Admin\NewAdminController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\Reports\TicketsReportController;
use App\Http\Controllers\Admin\Reports\WalletReportController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\SellerCashOutController;
use App\Http\Controllers\Admin\Settings\AdsSettingController;
use App\Http\Controllers\Admin\Settings\SmtpSettingController;
use App\Http\Controllers\Admin\Shops\AdminGiftPurchases;
use App\Http\Controllers\Admin\Shops\DashboardController as ProductDashboardController;
use App\Http\Controllers\Admin\Shops\OrderController;
use App\Http\Controllers\Admin\Shops\ProductCategoryController;
use App\Http\Controllers\Admin\Shops\ProductController;
use App\Http\Controllers\Admin\Shops\MerchantController;
use App\Http\Controllers\Admin\TaxesController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\SwipeController;
use App\Http\Controllers\Admin\TicketSaleController;
use App\Http\Controllers\Admin\TaxApiController;
use App\Http\Controllers\Admin\TaxController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::group(['middleware' => ['guest']], function () {
        Route::get('/', function (Request $request) {
            return Inertia::render('admin/auth/Login', [
                'canResetPassword' => Route::has('admin.password.request'),
                'status' => $request->session()->get('status'),
            ]);
        })->name('home');

        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:admin-password-reset')->name('password.email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:admin-password-reset')->name('password.store');
    });

    Route::group(['middleware' => ['auth', 'role:admin', 'userType:admin']], function () {
        Route::group(['middleware' => ['auth', 'verified', 'permission:view dashboard']], function () {
            Route::get('dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        });

        Route::group(['middleware' => ['auth', 'verified', 'permission:view wallet dashboard']], function () {
            Route::get('dashboard/wallet', [WalletDashboardController::class, 'index'])->name('dashboard.wallet');
        });

        Route::group(['middleware' => ['auth', 'verified', 'permission:view events analytics']], function () {
            Route::get('dashboard/event', [EventDashboardController::class, 'index'])->name('dashboard.event');
        });

        Route::group(['middleware' => ['auth', 'verified', 'permission:view moderation']], function () {
            Route::get('dashboard/moderation', [ModerationDashboardController::class, 'index'])->name('dashboard.moderation');
        });

        Route::group(['middleware' => ['auth', 'verified', 'permission:view live analytics']], function () {
            Route::get('dashboard/live-analytics', [LiveAnalyticsDashboardController::class, 'index'])->name('dashboard.live-analytics');
        });

        Route::group(['middleware' => ['auth', 'verified', 'permission:view fee analytics']], function () {
            Route::get('dashboard/fee-analytics', [FeeDashboardController::class, 'index'])->name('dashboard.fee-analytics');
            Route::get('dashboard/fee-analytics/data', [FeeDashboardController::class, 'data'])->name('dashboard.fee-analytics.data');
        });

        Route::middleware('auth')->group(function () {
            Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
            Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:admin-verification-verify'])->name('verification.verify');
            Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:admin-verification-send')->name('verification.send');
            Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
            Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])->middleware('throttle:admin-password-confirm');
            Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        });

        // User Routes
        Route::middleware(['permission:view users'])->group(function () {
            Route::resource('users', UsersController::class);
            Route::controller(UsersController::class)->prefix('users')->name('users-')->group(function () {
                Route::get('live-messages/{user}', 'getUserLiveMessages')->name('messages');
                Route::get('live-stream/{user}', 'getUserLiveStream')->name('live.stream');
                Route::get('wallet-transactions/{user}', 'getwalletTransactions')->name('wallet');
                Route::post('change-status/{user}', 'changeStatus')->middleware('throttle:admin-sensitive')->name('change-status');
                Route::get('events/{user}', 'getEvents')->name('events');
                Route::post('add-user-balance/{user}', 'addUserBalance')->middleware('throttle:admin-sensitive')->name('add-wallet-balance');
            });
            Route::get('/users-wallet/{user}', [UsersController::class, 'getwalletTransactions'])->name('users.wallet');
            Route::get('/users-subscription/profile/{user}', [UsersController::class, 'getUserSubscription'])->name('user.subscription');
        });

        // Bank Withdrawals Routes
        Route::middleware(['permission:view users'])->group(function () {
            Route::get('bank-withdrawals', [BankWithdrawalController::class, 'index'])->name('bank-withdrawals.index');
            Route::post('bank-withdrawals/{id}/approve', [BankWithdrawalController::class, 'approve'])->middleware('throttle:admin-sensitive')->name('bank-withdrawals.approve');
            Route::post('bank-withdrawals/{id}/reject', [BankWithdrawalController::class, 'reject'])->middleware('throttle:admin-sensitive')->name('bank-withdrawals.reject');
        });

        // Seller Cash Out Routes
        Route::middleware(['permission:view users'])->group(function () {
            Route::get('seller-cash-out', [SellerCashOutController::class, 'index'])->name('seller-cash-out.index');
            Route::post('seller-cash-out/{id}/approve', [SellerCashOutController::class, 'approve'])->middleware('throttle:admin-sensitive')->name('seller-cash-out.approve');
            Route::post('seller-cash-out/{id}/reject', [SellerCashOutController::class, 'reject'])->middleware('throttle:admin-sensitive')->name('seller-cash-out.reject');
        });

        // Email Routes
        Route::middleware(['permission:view emails'])->group(function () {
            Route::controller(EmailController::class)->prefix('email')->name('email-')->group(function () {
                Route::get('compose', 'compose')->name('compose');
                Route::post('send', 'send')->name('send');

                Route::middleware(['permission:manage email templates'])->prefix('templates')->name('templates-')->group(function () {
                    Route::get('/', 'emailTemplatesIndex')->name('index');
                    Route::get('/create', 'emailTemplatesCreate')->name('create');
                    Route::post('/', 'emailTemplatesStore')->name('store');
                    Route::get('/{id}/edit', 'emailTemplatesEdit')->name('edit');
                    Route::put('/{emailTemplate}', 'emailTemplatesUpdate')->name('update');
                    Route::delete('/{id}', 'emailTemplatesDestroy')->name('destroy');
                });
            });
            Route::get('send-emails', [EmailController::class, 'sendEmail'])->name('send.emails');
            Route::post('send-emails', [EmailController::class, 'postSendEmail'])->name('post.send.emails');
        });

        // User reports routes
        Route::middleware(['permission:view flagged users'])->group(function () {
            Route::controller(UserReportsController::class)->prefix('user-reports')->name('user-reports-')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}/review', 'review')->name('review');
                Route::post('/{id}/review', 'saveReview')->name('review');
                Route::post('change-status/{flagged_user}',  'changeStatus')->name('status');
            });

            Route::get('/flagged-change-status', [UserReportsController::class, 'flaggedChangeStatus'])->name('flagged-change-status');
            Route::get('/flagged', [UserReportsController::class, 'index'])->name('flagged');
        });

        //kyc users
        Route::middleware(['permission:view kyc users'])->group(function () {
            Route::get('kyc-users', [KycUserController::class, 'users'])->name('kyc-users');
            Route::delete('kyc-users/{id}', [KycUserController::class, 'destroy'])->name('kyc-destroy');
            Route::post('kyc-users/status/{kyc}', [KycUserController::class, 'changeStatus'])->name('kyc-users.change-status');
            Route::get('kyc-organizers', [KycUserController::class, 'organizers'])->name('kyc-organizers');
            Route::post('kyc-organizers/{organizerKyc}/field-status', [KycUserController::class, 'updateOrganizerKycFieldStatus'])
                ->name('kyc-organizers.field-status');
        });

        //linkup events
        Route::middleware(['permission:view events'])->group(function () {
            Route::get('event', [NewAdminController::class, 'eventsList'])->name('event.index');
            Route::resource('event', EventController::class)->except('index');
            Route::post("event/update/{event}", [EventController::class, 'update'])->name('event.updated');
            Route::middleware(['permission:view sponsors'])->group(function () {
                Route::post('event/sponsor/store', [EventController::class, 'storeSponsor'])->name('event.sponsor.store');
                Route::post('event/sponsor/update/{sponsor_id}', [EventController::class, 'sponsorUpdate'])->name('event.sponsor.update');
            });
            Route::middleware(['permission:view coupons'])->group(function () {
                Route::post('event/coupons/store', [EventController::class, 'couponStore'])->name('event.coupon.store');
                Route::post('event/coupons/updated/{coupons_id}', [EventController::class, 'couponUpdate'])->name('event.coupon.update');
            });

            Route::get('/events/fee-settings', [EventController::class, 'feeSettings'])->name('events.fee-settings');
            Route::post('/fee-settings', [EventController::class, 'storefeeSettings'])->name('events.fee-settings.store');
            Route::resource('categories', EventCategoryController::class)->except(['edit', 'create', 'show']);
            Route::resource('sponsors', SponsorController::class)->except(['edit', 'create', 'show']);
            Route::resource('coupons', CouponController::class)->except(['edit', 'create', 'show']);
            Route::get('eventfee/setting', [AppSettingController::class, 'eventFeeShow'])->name('eventfee.setting');
        });

        // Taxes routes
        Route::middleware(['permission:view taxes'])->group(function () {
            Route::controller(TaxesController::class)->prefix('taxes')->name('taxes-')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::middleware(['permission:taxes dashboard'])->get('/dashboard', 'dashboard')->name('dashboard');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{uid}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');

                Route::middleware(['permission:taxes setting'])->get('/tax/setting', 'taxSetting')->name('setting');
                Route::middleware(['permission:taxes remittance setting'])->get('/tax/remittance-setting', 'taxRemittanceSetting')->name('remittance-setting');
                Route::middleware(['permission:taxes remittance center'])->get('/tax/remittance-center', 'taxRemittanceCenter')->name('remittance-center');
            });
        });

        // News Management
        Route::middleware(['permission:view news'])->group(function () {
            Route::resource('news', NewsController::class);
            Route::middleware(['permission:toggle news'])->put('news-status/{news}', [NewsController::class, 'toggleActive'])->name('news.status');
            Route::post('news/{news}/trending', [NewsController::class, 'incrementTrending'])->name('news.trending');
            Route::post('add-source', [NewsController::class, 'AddNewsSource'])->name('news.source');
            Route::delete('delete-source/{source}', [NewsController::class, 'DeleteNewsSource'])->name('news.source.delete');
        });

        // Product Management
        Route::middleware(['permission:view products'])->group(function () {
            Route::resource('products', ProductController::class);
            Route::post('save-extra-image/{product}', [ProductController::class, 'saveExtraImage'])->name('save.product.image');
            Route::post('remove-extra-image/{product}', [ProductController::class, 'removeExtraImage'])->name('remove.product.image');
            Route::get('get-images/{product}', [ProductController::class, 'getImages'])->name('get.product.images');

            // product Category
            Route::middleware(['permission:view product categories'])->group(function () {
                Route::resource('product-categories', ProductCategoryController::class);
            });
            Route::get('dashboard/product', [ProductDashboardController::class, 'index'])->name('dashboard.index');
            Route::middleware(['permission:fees'])->group(function () {
                Route::get('dashboard/product/fees', [ProductDashboardController::class, 'fees'])->name('products.fees');
                Route::post('dashboard/product/fees', [ProductDashboardController::class, 'storeFees'])->name('products.fees.store');
            });
        });

        // Orders
        Route::middleware(['permission:view orders'])->group(function () {
            Route::resource('orders', OrderController::class)->only('index', 'store', 'show', 'destroy');
            Route::middleware(['permission:view escrow releases'])->get('escrow-releases', [OrderController::class, 'escrowReleases'])->name('orders.escrow-releases');
            Route::middleware(['permission:view seller transfers'])->group(function () {
                Route::get('seller-transfers', [OrderController::class, 'sellerTransfers'])->name('orders.seller-transfers');
                Route::post('seller-transfers/{transfer}/approve', [OrderController::class, 'approveTransfer'])->middleware('throttle:admin-sensitive')->name('orders.seller-transfers.approve');
                Route::post('seller-transfers/{transfer}/reject', [OrderController::class, 'rejectTransfer'])->middleware('throttle:admin-sensitive')->name('orders.seller-transfers.reject');
                Route::post('seller-transfers/simulate', [OrderController::class, 'simulateTransfer'])->middleware('throttle:admin-sensitive')->name('orders.seller-transfers.simulate');
            });
            Route::post('update-status/{order}', [OrderController::class, 'updateStatus'])->middleware('throttle:admin-sensitive')->name('update.order.status');
        });

        // Merchants / Sellers
        Route::middleware(['permission:view sellers (Store/Group)'])->group(function () {
            Route::resource('merchants', MerchantController::class);
            Route::patch('merchants/{merchant}/status', [MerchantController::class, 'updateStatus'])->name('merchants.status');
        });

        Route::middleware(['permission:view wallets'])->group(function () {
            Route::get('shop-wallets', [App\Http\Controllers\Admin\Shops\WalletController::class, 'index'])->name('shop.wallets.index');
            Route::patch('shop-wallets/{user}/balance', [App\Http\Controllers\Admin\Shops\WalletController::class, 'updateBalance'])->middleware('throttle:admin-sensitive')->name('shop.wallets.update');
        });

        //swipes management
        Route::middleware(['permission:view swipes'])->group(function () {
            Route::resource('swipes', SwipeController::class)->except('show');
        });

        //restaurants management
        Route::middleware(['permission:view restaurants'])->group(function () {
            Route::resource('restaurant', RestaurantController::class);
        });

        //clubs management
        Route::middleware(['permission:view clubs'])->group(function () {
            Route::resource('clubfete', ClubFeteController::class);
        });

        // Live Streaming Management
        Route::middleware(['permission:view live streams'])->group(function () {
            Route::resource('live_streams', LiveStreamController::class);
            Route::controller(LiveStreamController::class)->prefix('live-streams')->name('live-streams.')->group(function () {
                Route::get('/dashboard', 'dashboard')->name('dashboard');
                Route::middleware(['permission:view fee analytics'])->get('/fee-analytics', 'feeAnalytics')->name('fee-analytics');
                Route::middleware(['permission:view live analytics'])->get('/live-analytics', 'liveAnalytics')->name('live-analytics');
                Route::middleware(['permission:view moderation'])->get('/moderation', 'moderation')->name('moderation');
                Route::get('/categories', 'categories')->name('categories');
                Route::post('/categories', 'storeCategory')->name('categories.store');
                Route::put('/categories/{category}', 'updateCategory')->name('categories.update');
                Route::delete('/categories/{category}', 'destroyCategory')->name('categories.destroy');
            });
        });

        // Messages
        Route::middleware(['permission:view messages'])->group(function () {
            Route::resource('messages', MessageController::class);
        });

        // Encounters
        Route::middleware(['permission:view encounters'])->group(function () {
            Route::resource('encounters', EncounterController::class);
        });

        // Calls
        Route::middleware(['permission:view calls'])->group(function () {
            Route::resource('calls', CallController::class);
        });

        // Gifts
        Route::middleware(['permission:view gifts'])->group(function () {
            Route::resource('gifts', GiftController::class);
            Route::get('gift-purchase', [AdminGiftPurchases::class, 'index'])->name('gift.purchase');
            Route::delete('delete-purchase/{gift}', [AdminGiftPurchases::class, 'destroy'])->name('gift.purchase.delete');
            Route::resource('linkup-live-gifts', LinkupLiveGiftController::class);
        });

        // Payments
        Route::middleware(['permission:view payments'])->group(function () {
            Route::resource('payments', PaymentController::class);
        });

        // Payouts
        Route::middleware(['permission:view payouts'])->group(function () {
            Route::resource('payouts', PayoutController::class);
            Route::post('payouts/{payout}/approve', [PayoutController::class, 'approve'])->middleware('throttle:admin-sensitive')->name('payouts.approve');
            Route::post('payouts/{payout}/reject', [PayoutController::class, 'reject'])->middleware('throttle:admin-sensitive')->name('payouts.reject');
            Route::post('payouts/{payout}/retry', [PayoutController::class, 'retry'])->middleware('throttle:admin-sensitive')->name('payouts.retry');
            Route::post('payouts/{payout}/move-to-processing', [PayoutController::class, 'moveToProcessing'])->middleware('throttle:admin-sensitive')->name('payouts.move-to-processing');
        });

        //app setting
        Route::middleware(['permission:view app settings'])->group(function () {
            Route::get('appsetting', [AppSettingController::class, 'index'])->name('appsetting');
            Route::post('saveappsetting', [AppSettingController::class, 'saveSetting'])->name('saveappsetting');
        });

        Route::middleware(['permission:view smtp settings'])->group(function () {
            Route::resource('smtp-setting', SmtpSettingController::class)->only(['show', 'update']);
        });

        Route::middleware(['permission:view ads settings'])->group(function () {
            Route::resource('ads-setting', AdsSettingController::class)->only(['show', 'update']);
        });

        //role management
        Route::middleware(['permission:view roles'])->group(function () {
            Route::resource('roles', RoleController::class);
        });

        // Organizer
        Route::middleware(['permission:view event organizers'])->group(function () {
            Route::controller(EventOrganizerController::class)->prefix('/profile')->name('organizer.profile.')->group(function () {
                Route::get('/organizer/lists', 'index')->name('index');
                Route::get('/organizer/create', 'create')->name('create');
                Route::post('/organizer/store', 'store')->name('store');
                Route::get('/organizer/edit/{id}', 'edit')->name('edit');
                Route::post('/organizer/update/{organizer_id}', 'update')->name('update');
                Route::delete('/organizer/destroy/{organizer_id}', 'destroy')->name('destroy');
                Route::get('/organizer/destroy/{organizer_id}', 'show')->name('show');
            });
        });

        //cashout
        Route::middleware(['permission:view payouts'])->group(function () {
            Route::resource('cashout', CashoutController::class);
        });

        // scanner
        Route::middleware(['permission:view scanners'])->group(function () {
            Route::resource('scanners', ScannerController::class);
        });

        // E-Wallet Management
        Route::middleware(['permission:view wallet dashboard'])->group(function () {
            Route::get('e_wallet/dashboard', [DashboardController::class, 'index'])->name('e.wallet.dashboard');
            Route::middleware(['permission:view digi money'])->group(function () {
                Route::get('e_wallet/digimoney', [DigiMoneyController::class, 'index'])->name('e.wallet.digimoney');
                Route::post('e_wallet/digimoney', [DigiMoneyController::class, 'store'])->name('e.wallet.digimoney.store');
            });
            Route::middleware(['permission:view transactions'])->group(function () {
                Route::get('e_wallet/transactions', [TransactionController::class, 'index'])->name('e.wallet.transaction');
                Route::get('e_wallet/money/requests', [MoneyRequestController::class, 'index'])->name('e.wallet.money.request');
                Route::get('e_wallet/withdraw/requests', [WithdrawRequestController::class, 'index'])->name('e.wallet.withdraw.request');
                Route::post('change-status/{moneyrequest}', [MoneyRequestController::class, 'changeStatus'])->middleware('throttle:admin-sensitive')->name('money.request.status');
            });
            Route::middleware(['permission:view wallet settings'])->group(function () {
                Route::get('e_wallet/settings', [DashboardController::class, 'saveWalletSetting'])->name('e.wallet.setting');
                Route::post('e_wallet/update/settings', [DashboardController::class, 'updateWalletSetting'])->name('e.update.setting');
            });
        });

        // push notification
        Route::middleware(['permission:view push notifications'])->group(function () {
            Route::get('pushnotification', [PushNotificationController::class, 'view'])->name('push.notification.view');
            Route::post('pushnotification', [PushNotificationController::class, 'send'])->name('send.push.notification');
        });

        // Admins
        Route::middleware(['permission:view admins'])->group(function () {
            Route::resource('admins', AdminController::class);
        });

        // Tickets
        Route::middleware(['permission:view ticket sales'])->group(function () {
            Route::resource('tickets', TicketController::class);
            Route::get('events/{id?}/tikets', [TicketController::class, 'evetTikets'])->name('event.tickets');
            Route::post('tickets/storeOrUpdate', [TicketController::class, 'storeOrUpdate'])->name('ticket.store.update');
            Route::get('tickets/getting/{event}', [TicketController::class, 'gettingTickets'])->name('ticket.getting');

            // Ticket sale report
            Route::get('ticket-sale', [TicketSaleController::class, 'index'])->name('ticket.sale');
            Route::post('ticket-sale', [TicketSaleController::class, 'report'])->name('ticket.sale.report');
        });

        // Payment Methods
        Route::middleware(['permission:view payment methods'])->group(function () {
            Route::resource('payment/gateway', PaymentGatewayController::class)->except(['show', 'edit']);
            Route::post('payment/gateway/stripe', [PaymentGatewayController::class, 'storeOrEditStripe'])->name('payment.gateway.stripe');
            Route::post('payment/gateway/paypal', [PaymentGatewayController::class, 'storeOrEditPaypal'])->name('payment.gateway.paypal');
            Route::get('payment/user/wallet/kyc', [PaymentGatewayController::class, 'userWalletKyc'])->name('user-wallet-kyc.index');
            Route::post('payment/user/wallet/kyc/{id}/status', [PaymentGatewayController::class, 'updateUserWalletKycStatus'])->middleware('throttle:admin-sensitive')->name('user-wallet-kyc.update-status');
        });

        // Invoices
        Route::middleware(['permission:view transactions'])->group(function () {
            Route::get('show-invoice/{id}', [InvoiceController::class, 'showInvoice'])->name('show.invoice');
            Route::get('add-invoice/{id}', [InvoiceController::class, 'addInovice'])->name('add.invoice');
            Route::post('store-invoice', [InvoiceController::class, 'store'])->name('store.invoice');
        });

        // Reports
        Route::middleware(['permission:view reports'])->group(function () {
            Route::get('ticket-report', [TicketsReportController::class, 'index'])->name('report.ticket.sale');
            Route::get('download-report', [TicketsReportController::class, 'downloadReport'])->name('download.report.ticket.sale');
            Route::get('wallet-report', [WalletReportController::class, 'index'])->name('report.wallet');
            Route::get('download-wallet-report', [WalletReportController::class, 'downloadReport'])->name('download.report.wallet');
        });

        // Admin Fee Settings
        Route::middleware(['permission:fees'])->group(function () {
            Route::get('admin-fee-settings', [AdminFeeController::class, 'index'])->name('admin_fee.index');
            Route::post('fees-save', [AdminFeeController::class, 'storeOrUpdate'])->name('fees.save');
        });

        // Tax Settings API
        Route::middleware(['permission:taxes setting'])->group(function () {
            Route::controller(TaxApiController::class)->prefix('tax-settings')->name('tax-settings.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::post('/test', 'testConnection')->name('test');
                Route::patch('/status', 'updateStatus')->name('update-status');
            });

            Route::controller(TaxController::class)->prefix('tax-setting')->name('tax-setting.')->group(function () {
                Route::get('/local-countries', 'getLocalTaxCountries')->name('get-local-tax-countries');
                Route::get('/api-supported-countries', 'getApiSupportedCountries')->name('get-api-supported-countries');
                Route::post('/get-rate', 'getTaxRate')->name('get-tax-rate');
            });
        });

        // Tax Remittance API
        Route::middleware(['permission:taxes remittance setting'])->group(function () {
            Route::controller(TaxController::class)->prefix('tax-remittances')->name('tax-remittances.')->group(function () {
                Route::get('/', 'remittanceIndex')->name('index');
                Route::post('/', 'remittanceStore')->name('store');
                Route::get('/{id}', 'remittanceShow')->name('show');
                Route::put('/{id}', 'remittanceUpdate')->name('update');
                Route::delete('/{id}', 'remittanceDestroy')->name('destroy');
            });
        });

        // Ticket Cancellation
        Route::middleware(['permission:view ticket sales'])->group(function () {
            Route::get('cancel-ticket', [CancelTicketController::class, 'index'])->name('cancel-ticket.index');
            Route::post('create-request', [CancelTicketController::class, 'CreateRequest'])->name('request.create');
        });
    });

    // Tax Remittance Center API
    Route::middleware(['auth', 'role:admin', 'userType:admin', 'permission:taxes remittance center'])->controller(TaxController::class)->prefix('tax-remittance-centers')->name('tax-remittance-centers.')->group(function () {
        Route::get('/', 'remittanceCenterIndex')->name('index');
        Route::post('/', 'remittanceCenterStore')->name('store');
        Route::get('/{id}', 'remittanceCenterShow')->name('show');
        Route::put('/{id}', 'remittanceCenterUpdate')->name('update');
        Route::delete('/{id}', 'remittanceCenterDestroy')->name('destroy');
    });
});

require __DIR__ . '/adsManager.php';
require __DIR__ . '/adminSubscription.php';
