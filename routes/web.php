<?php

use App\Http\Controllers\Admin\EmailAdsManagerController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\Frontend\AdTrackingController;
use App\Http\Controllers\Frontend\AppPlolicyController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\ChatController;
use App\Http\Controllers\Frontend\ClubController;
use App\Http\Controllers\Frontend\ConversationController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\EventTicketController;
use App\Http\Controllers\Frontend\FriendController;
use App\Http\Controllers\Frontend\FriendRequestController;
use App\Http\Controllers\Frontend\GiftsController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\LinkUpShop\CheckOutController;
use App\Http\Controllers\Frontend\LinkUpShop\OrderController;
use App\Http\Controllers\Frontend\LinkUpShop\ProductController;
use App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard\SellerCashOutController;
use App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard\SellerDashboardController;
use App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard\SellerEarningController;
use App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard\SellerOrderController;
use App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard\SellerStoreController;
use App\Http\Controllers\Frontend\LiveStreamGumletController;
use App\Http\Controllers\Frontend\MyEventController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\RestaurantController;
use App\Http\Controllers\Frontend\StripeController;
use App\Http\Controllers\Frontend\SubscriptionController;
use App\Http\Controllers\Frontend\TicketTypesController;
use App\Http\Controllers\Frontend\UserChatController;
use App\Http\Controllers\Frontend\UsersController;
use App\Http\Controllers\Frontend\WalletController;
use App\Http\Controllers\PushNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/sponsor/click/{ad}', [EmailAdsManagerController::class, 'trackClick'])->name('sponsor.click');

Route::get('/', [IndexController::class, 'index'])->name('user.home');
Route::get('/landing2', [IndexController::class, 'index2']);

// Legal Pages
Route::name('legal.')->group(function () {
    Route::get('/privacy-policy', [App\Http\Controllers\LegalPageController::class, 'showPrivacy'])->name('privacy');
    Route::get('/terms-of-service', [App\Http\Controllers\LegalPageController::class, 'showTerms'])->name('terms');
    Route::get('/acceptable-use-policy', [App\Http\Controllers\LegalPageController::class, 'showAcceptableUse'])->name('acceptable-use');
    Route::get('/prohibited-activities', [App\Http\Controllers\LegalPageController::class, 'showProhibited'])->name('prohibited');
    Route::get('/refund-policy', [App\Http\Controllers\LegalPageController::class, 'showRefund'])->name('refund');
    Route::get('/law-enforcement-guidelines', [App\Http\Controllers\LegalPageController::class, 'showLawEnforcement'])->name('law-enforcement');
    Route::get('/pricing-and-fees', [App\Http\Controllers\LegalPageController::class, 'showPricing'])->name('pricing');
    Route::get('/contact-and-customer-support', [App\Http\Controllers\LegalPageController::class, 'showSupport'])->name('support');
    Route::get('/vibes-acceptable-use-policy', [App\Http\Controllers\LegalPageController::class, 'showVibesPolicy'])->name('vibes-policy');
});

Route::get('/email/open/{token}.png', [EmailTrackingController::class, 'open'])
    ->where('token', '[0-9a-fA-F\-]{36}')
    ->name('email.open');

// Wizard routes: only 'auth', but with 'frontend.' names
Route::middleware(['auth', 'otp.verified'])->name('frontend.')->group(function () {
    Route::get('wizard', [UsersController::class, 'wizard'])->name('users.wizard');
    Route::post('wizard', [UsersController::class, 'wizardUpdate'])->name('wizard.update');
});

// All other routes: 'auth' + 'isWizardComplete' + 'frontend.' prefix
Route::middleware(['auth', 'otp.verified', 'isWizardComplete'])->group(function () {

    Route::name('frontend.')->group(function () {
        Route::resource('event', EventController::class)->except(['show']);
        Route::get('/event/{event:slug}', [EventController::class, 'show'])->name('event.show');
        Route::get('filter/events', [EventController::class, 'filterEvents'])->name('event.filter');
        Route::get('fetch-users-for-invitation', [EventController::class, 'fetchUserForInvitation'])->name('fetch.users.for.invitation');
        Route::post('send-users-invitation-email', [EventController::class, 'sendUserInvitationEmail'])->name('send.users.invitation.email');
        Route::get('event/{event}/tickets', [EventController::class, 'eventTickets'])->name('event.tickets');

        // Ticket Addon Routes
        Route::get('event/{event}/addons', [App\Http\Controllers\Frontend\TicketAddonController::class, 'getEventAddons'])->name('event.addons');
        Route::get('ticket/{ticket}/addons', [App\Http\Controllers\Frontend\TicketAddonController::class, 'getTicketAddons'])->name('ticket.addons');
        Route::post('addons/calculate-pricing', [App\Http\Controllers\Frontend\TicketAddonController::class, 'calculateAddonPricing'])->name('addons.calculate-pricing');
        Route::post('event/{event}/toggle-favorite', [EventController::class, 'toggleFavorite'])->name('event.toggle-favorite');
        Route::post('event/toggle-follow-organizer', [EventController::class, 'toggleFollowOrganizer'])->name('event.toggle-follow-organizer');
        Route::get('search-event', [EventController::class, 'searchEvents'])->name('event.search');
        Route::get('favorite-event', [EventController::class, 'favoriteEvents'])->name('event.favorite');

        // Review routes
        Route::get('event/{event}/reviews', [App\Http\Controllers\Frontend\ReviewController::class, 'index'])->name('event.reviews.index');
        Route::post('event/{event}/reviews', [App\Http\Controllers\Frontend\ReviewController::class, 'store'])->name('event.reviews.store');
        Route::put('event/{event}/reviews/{review}', [App\Http\Controllers\Frontend\ReviewController::class, 'update'])->name('event.reviews.update');
        Route::delete('event/{event}/reviews/{review}', [App\Http\Controllers\Frontend\ReviewController::class, 'destroy'])->name('event.reviews.destroy');
        Route::resource('ticket', EventTicketController::class);
        Route::get('eticket', [EventTicketController::class, 'eticket'])->name('eticket.create');
        Route::get('ticket/buy', [EventTicketController::class, 'buyTicket'])->name('ticket.buy');
        Route::get('ticket/types', [TicketTypesController::class, 'ticketTypes'])->name('ticket.types');
        Route::get('users', [UsersController::class, 'index'])->name('users.index');
        // profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/hide-profile', [ProfileController::class, 'HideProfile'])->name('profile.hide');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/users-messages/profile/{user}', [ProfileController::class, 'getUserLiveMessages'])->name('user.messages.profile');
        Route::get('/users-wallet/profile/{user}', [ProfileController::class, 'getwalletTransactions'])->name('user.wallet.profile');
        Route::get('/users-events/profile/{user}', [ProfileController::class, 'getEvents'])->name('user.events.profile');
        Route::get('/users-edit/profile', [ProfileController::class, 'editUser'])->name('user.edit.profile');
        Route::get('/cancel-subscription/{plan}', [ProfileController::class, 'cancelSubscription'])->name('user.subscription.cancel');

        Route::get('/users-live-streams/profile/{user}', [ProfileController::class, 'getUserLiveStream'])->name('user.live.stream.profile');
        Route::get('/users-subscription/profile/{user}', [ProfileController::class, 'getUserSubscription'])->name('user.subscription.profile');

        Route::get('home', [IndexController::class, 'homematches'])->name('home.matches');
        Route::get('findmatch', [IndexController::class, 'findmatches'])->name('find.matches');
        Route::post('profile/like/{user}', [IndexController::class, 'likeUser'])->name('profile.like');
        Route::post('profile/dislike/{user}', [IndexController::class, 'dislikeUser'])->name('profile.dislike');

        Route::get('allmatches', [IndexController::class, 'getMatches'])->name('user.matches');
        Route::get('likes', [IndexController::class, 'getLikes'])->name('user.likes');

        Route::get('linkup/user/{slug}/{user}', [IndexController::class, 'getLinkupUser'])->name('linkup.user.details');
        Route::post('profile/hide-from-user', [IndexController::class, 'hideFromUser'])->name('profile.hide.from.user');
        Route::post('profile/block-user', [IndexController::class, 'blockUser'])->name('profile.block.user');
        Route::post('profile/unblock-user', [IndexController::class, 'unblockUser'])->name('profile.unblock.user');

        Route::get('friend-request', [FriendRequestController::class, 'index'])->name('friend-request.index');
        Route::post('friend-request/{slug}/{user}', [FriendRequestController::class, 'sendRequest'])->name('friend-request.send');
        Route::post('friend-request/{friendRequest}', [FriendRequestController::class, 'acceptRequest'])->name('friend-request.accept');
        Route::post('friend-request/{friendRequest}/reject', [FriendRequestController::class, 'rejectRequest'])->name('friend-request.reject');

        Route::get('friend', [FriendController::class, 'index'])->name('friend.index');
        Route::post('friend/{id}/unfriend', [FriendController::class, 'unfriend'])->name('friend.unfriend');

        Route::get('clubs', [ClubController::class, 'index'])->name('clubs.index');
        Route::get('clubs/{place_id}', [ClubController::class, 'show'])->name('clubs.show');

        Route::get('restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
        Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

        Route::get('news', [NewsController::class, 'index'])->name('news.index');
        Route::post('news', [NewsController::class, 'store'])->name('news.store');
        Route::post('news/{news}/trending', [NewsController::class, 'incrementTrending'])->name('news.trending');
        Route::get('news/{news}', [NewsController::class, 'show'])->name('news.show');
        Route::put('news/{news}', [NewsController::class, 'update'])->name('news.update');
        Route::delete('news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
        Route::put('news-status/{news}', [NewsController::class, 'toggleActive'])->name('news.status');

        Route::get('subscription-plans', [SubscriptionController::class, 'index'])->name('subscription.plans');
        Route::post('subscription/choose-plan', [SubscriptionController::class, 'redircetCheckout'])->name('subscription.choose.plan');
        Route::get('subscription/success', [SubscriptionController::class, 'subscriptionSuccess'])->name('subscription.success');
        Route::get('subscription/cancel', [SubscriptionController::class, 'subscriptionCancel'])->name('subscription.cancel');

        Route::get('user/wallet', [WalletController::class, 'index'])->name('user.wallet');

        Route::middleware(['check.user_wallet_kyc'])->group(function () {
            Route::get('user/wallet/kyc', [WalletController::class, 'kyc'])->name('user.wallet.kyc');
            Route::post('user/wallet/kyc', [WalletController::class, 'storeKyc'])->name('user.wallet.kyc.store');
            Route::post('user/send/money', [WalletController::class, 'sendMoney'])->name('user.send.money');
            Route::get('user/coin', [WalletController::class, 'coinSystemhead'])->name('user.coin.system');
            Route::get('user/wallet/amount', [WalletController::class, 'addAmoutPage'])->name('user.wallet.add');
            Route::post('/gift-coins', [WalletController::class, 'sendCoins'])->name('user.send.gift');

            Route::post('user/wallet/checkout', [WalletController::class, 'submitAmountToWallet'])->name('wallet.checkout');
            Route::get('user/onetimepay/success', [WalletController::class, 'oneTimePaySuccess'])->name('oneTimePay.success');
            Route::post('user/wallet/buy-ticket', [WalletController::class, 'buyTicket'])->name('user.wallet.buy-ticket');
            Route::post('add/contact', [WalletController::class, 'AddToContact'])->name('user.wallet.contact');
            Route::get('user/bank-details', [WalletController::class, 'getBankAccounts'])->name('user.bank-details.get');
            Route::post('user/bank-details', [WalletController::class, 'storeBankDetails'])->name('user.bank-details.store');
            Route::post('user/bank-withdrawal', [WalletController::class, 'initiateBankWithdrawal'])->name('user.bank-withdrawal.initiate');
            Route::post('user/bank-withdrawal/cancel/{id}', [WalletController::class, 'cancelBankWithdrawal'])->name('user.bank-withdrawal.cancel');
            Route::get('search/linkup-id', [WalletController::class, 'searchByLinkupId'])->name('user.wallet.search.linkup');
            Route::post('send/money/request', [WalletController::class, 'SendMoneyRequest'])->name('user.money.request');
            Route::post('/{id}/respond', [WalletController::class, 'respond'])->name('user.money.respond');
            Route::delete('/{id}/cancel', [WalletController::class, 'cancel'])->name('user.money.cancel');

            Route::delete('remove/contact/{id}', [WalletController::class, 'removeContact'])->name('user.remove.contact');

            Route::get('money-request', [WalletController::class, 'moneyRequestList'])->name('user.wallet.money.request.list');
            Route::post('send/requested/money/{id}', [WalletController::class, 'sendRequestedMoney'])->name('user.send.requested.money');
            Route::get('user/reject/money/request/{id}', [WalletController::class, 'rejectMoneyRequest'])->name('user.reject.money.request');
            Route::get('user/cancel/money/request/{id}', [WalletController::class, 'cancelMoneyRequest'])->name('user.cancel.money.request');

            // ASUE routes
            Route::post('user/asues/store', [WalletController::class, 'storeAsue'])->name('user.asues.store');
            Route::post('user/asues/{asue}/accept-participation', [WalletController::class, 'acceptAsueParticipation'])->name('user.asues.accept-participation');
            Route::post('user/asues/{asue}/simulate-cycle', [WalletController::class, 'simulateAsueCycle'])->name('user.asues.simulate-cycle');
            Route::post('user/asues/{asue}/simulate-accept', [WalletController::class, 'simulateAsueAccept'])->name('user.asues.simulate-accept');

            // coins route
            Route::get('user/wallet/coin', [WalletController::class, 'addCoinPage'])->name('user.wallet.coin');
            Route::get('user/onetimepaycoin/success', [WalletController::class, 'oneTimePayCoinSuccess'])->name('oneTimePayCoin.success');
            Route::post('user/wallet/coin/checkout', [WalletController::class, 'submitCoinToWallet'])->name('wallet.coin.checkout');
        });

        Route::post('stripe/buy-ticket', [StripeController::class, 'buyTicket'])->name('stripe.buy-ticket');
        Route::get('stripe/buy-ticket/success', [StripeController::class, 'buyTicketSuccess'])->name('stripe.buy-ticket.success');

        Route::get('user/chat', [UserChatController::class, 'index'])->name('user.chat');
        Route::get('user/chat/{slug}/{user}', [UserChatController::class, 'startChat'])->name('user.start.chat');
        Route::post('/chat/toggle-unpin/{userId}', [UserChatController::class, 'toggleUnPin'])->name('user.toggle.unpin');
        Route::post('/chat/toggle-pin/{userId}', [UserChatController::class, 'togglePin'])->name('user.toggle.pin');

        Route::get('/chat/{toUserId}', [ConversationController::class, 'index'])->name('chat.index');
        Route::post('/chat/{toUserId}/messages', [ConversationController::class, 'store'])->name('messages.store');

        // Public Chat Routes
        Route::get('/public-chat', function () {
            return inertia('User/Chat');
        })->name('public.chat');
        Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/chat/recent', [ChatController::class, 'getRecentMessages'])->name('chat.recent');

        Route::resource('myevents', MyEventController::class);
        // for go live
        Route::post('go-live/token', [LiveStreamGumletController::class, 'generateToken'])->name('go-live.token');
        Route::post('go-live/start-agora', [LiveStreamGumletController::class, 'startAgoraStream'])->name('go-live.start-agora');
        Route::post('go-live/end', [LiveStreamGumletController::class, 'endAgoraStream'])->name('go-live.end');
        Route::post('go-live/update-settings/{stream}', [LiveStreamGumletController::class, 'updateStreamSettings'])->name('go-live.update-settings');
        Route::resource('go-live', LiveStreamGumletController::class);
        Route::post('update/stream/{stream}', [LiveStreamGumletController::class, 'update'])->name('go-live.updated');
        Route::post('start/stream', [LiveStreamGumletController::class, 'startStream'])->name('go-live.start');
        Route::get('start/end/{id}', [LiveStreamGumletController::class, 'status'])->name('go-live.end');
        Route::get('join/stream/{id}', [LiveStreamGumletController::class, 'joinLiveStreams'])->name('livestream.join');
        Route::get('go/live/network', [LiveStreamGumletController::class, 'goLiveNetwork'])->name('go.live.network');

        // live realtime actions
        Route::post('live/{stream}/gift', [LiveStreamGumletController::class, 'sendGift'])->name('live.gift');
        Route::post('live/{stream}/invite', [LiveStreamGumletController::class, 'inviteGuest'])->name('live.invite');
        Route::post('live/{stream}/invite/reply', [LiveStreamGumletController::class, 'replyInvite'])->name('live.invite.reply');
        Route::post('live/{stream}/guest/remove', [LiveStreamGumletController::class, 'removeGuest'])->name('live.guest.remove');
        Route::post('live/{stream}/reaction', [LiveStreamGumletController::class, 'sendReaction'])->name('live.reaction');
        Route::post('live/{stream}/comment', [LiveStreamGumletController::class, 'sendComment'])->name('live.comment');
        Route::post('live/unsubscribe', [LiveStreamGumletController::class, 'unsubscribe'])->name('live.unsubscribe');

        Route::post('live/{stream}/polls', [LiveStreamGumletController::class, 'createPoll'])->name('live.poll.create');
        Route::post('live/{stream}/polls/{poll}/vote', [LiveStreamGumletController::class, 'votePoll'])->name('live.poll.vote');
        Route::post('live/{stream}/polls/{poll}/end', [LiveStreamGumletController::class, 'endPoll'])->name('live.poll.end');

        Route::post('live/{stream}/qna', [LiveStreamGumletController::class, 'submitQna'])->name('live.qna.submit');
        Route::post('live/{stream}/qna/{qna}/answer', [LiveStreamGumletController::class, 'answerQna'])->name('live.qna.answer');
        Route::delete('live/{stream}/qna/{qna}', [LiveStreamGumletController::class, 'deleteQna'])->name('live.qna.delete');

        Route::get('live/{stream}/state', [LiveStreamGumletController::class, 'getStreamState'])->name('live.state');
        Route::get('live/config', [LiveStreamGumletController::class, 'getLiveConfig'])->name('live.config');

        // Viewer tracking
        Route::post('live/{stream}/viewer/join', [LiveStreamGumletController::class, 'joinViewer'])->name('live.viewer.join');
        Route::get('live/{stream}/viewers', [LiveStreamGumletController::class, 'getViewers'])->name('live.viewers');
        Route::post('live/{stream}/viewer/heartbeat', [LiveStreamGumletController::class, 'viewerHeartbeat'])->name('live.viewer.heartbeat');
        Route::post('live/{stream}/viewer/leave', [LiveStreamGumletController::class, 'leaveViewer'])->name('live.viewer.leave');
        Route::post('live/cleanup-stale-viewers', [LiveStreamGumletController::class, 'cleanupStaleViewers'])->name('live.cleanup-stale-viewers');
        Route::post('live/{stream}/host/heartbeat', [LiveStreamGumletController::class, 'hostHeartbeat'])->name('live.host.heartbeat');

        // Live Stream Followers Routes
        Route::post('live/stream/{stream}/follow', [LiveStreamGumletController::class, 'toggleFollowStream'])
            ->name('live.follow');
        Route::post('live/{stream}/unfollow', [LiveStreamGumletController::class, 'unfollowStream'])
            ->name('live.stream.unfollow');
        Route::get('live/{stream}/followers', [LiveStreamGumletController::class, 'getStreamFollowers'])->name('live.stream.followers');
        Route::get('live/{stream}/followers-count', [LiveStreamGumletController::class, 'getFollowersCount'])->name('live.stream.followers-count');

        Route::get('live/{stream}/stats', [LiveStreamGumletController::class, 'getStreamStats'])->name('live.stats');
        Route::get('live/analytics', [LiveStreamGumletController::class, 'getUserAnalytics'])->middleware('throttle:30,1')->name('live.analytics');
        Route::post('live/{stream}/transfer-earnings', [LiveStreamGumletController::class, 'transferSessionEarnings'])->middleware('throttle:5,1')->name('live.transfer-earnings');

        Route::post('live/{stream}/subscribe', [LiveStreamGumletController::class, 'subscribe'])->name('live.subscribe');
        Route::get('live/subscribe/success', [LiveStreamGumletController::class, 'subscriptionSuccess'])->name('live.subscribe.success');

        Route::middleware(['check.kyc'])->group(function () {
            Route::resource('myevents', MyEventController::class);
        });

        // flaged user
        Route::post('falg-user', [UsersController::class, 'falgedUser'])->name('flag.user');

        Route::get('bookings/upcomming', [BookingController::class, 'upcomming'])->name('bookings.upcomming');
        Route::get('bookings/completed', [BookingController::class, 'completed'])->name('bookings.completed');
        Route::get('bookings/cancelled', [BookingController::class, 'cancelled'])->name('bookings.cancelled');
        Route::get('bookings/{ticketSale}/eticket', [BookingController::class, 'eticket'])->name('bookings.eticket');
        Route::post('bookings/{ticketSale}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('verify/kyc', [UsersController::class, 'verifyKyc'])->name('user.verify.kyc');
        Route::post('verify/kyc/update', [UsersController::class, 'submitVerifyKyc'])->name('user.kyc.update');

        Route::get('/allnotifications', [UsersController::class, 'userNotifation'])->name('user.allnotifications');
        Route::get('app-polocies/{key}', [AppPlolicyController::class, 'getSpecificAppPolocies'])->name('app.policy');
        Route::delete('/notifications/{id}', [UsersController::class, 'destroy'])->name('notifications.destroy');
        Route::patch('/notifications/{id}/read', [UsersController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [UsersController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::post('/notifications/bulk-delete', [UsersController::class, 'bulkDelete'])->name('notifications.bulkDelete');
        Route::post('/notifications/bulk-read', [UsersController::class, 'bulkMarkAsRead'])->name('notifications.bulkRead');

        // for linkup shop
        Route::middleware('auth')->group(function () {
            Route::get('/checkout', [CheckOutController::class, 'index'])->name('checkout.index');
            Route::post('/checkout/quote', [CheckOutController::class, 'quote'])->middleware('throttle:30,1')->name('checkout.quote');
            Route::post('/checkout', [CheckOutController::class, 'store'])->name('checkout.store');
        });
        Route::middleware('auth')->prefix('seller')->name('seller.')->group(function () {
            // Analytics Dashboard
            Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard.index');
            Route::get('-dashboard', [SellerDashboardController::class, 'index'])->name('dashboard.legacy'); // keep old URL

            // Earnings
            Route::get('/earnings', [SellerEarningController::class, 'earnings'])->name('earnings');
            Route::get('/wallet-balance', [SellerEarningController::class, 'getWalletBalance'])->name('earnings.wallet-balance');
            Route::get('/reports/sales', [SellerEarningController::class, 'getSalesReport'])->name('earnings.reports.sales');
            Route::get('/reports/cash-out', [SellerEarningController::class, 'getCashOutReport'])->name('earnings.reports.cash-out');
            Route::get('/reports/wallet-funding', [SellerEarningController::class, 'getWalletFundingReport'])->name('earnings.reports.wallet-funding');
            Route::get('/reports/invoice/{orderId}', [SellerEarningController::class, 'generateInvoice'])->name('earnings.reports.invoice');

            // Cash Out Requests
            Route::post('/cash-out', [SellerCashOutController::class, 'store'])->name('cash-out.store');
            Route::post('/cash-out/{id}/cancel', [SellerCashOutController::class, 'cancel'])->name('cash-out.cancel');
            Route::get('/cash-out', [SellerCashOutController::class, 'index'])->name('cash-out.index');
            Route::get('/cash-out/available', [SellerCashOutController::class, 'getAvailable'])->name('cash-out.available');

            // Orders
            Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [SellerOrderController::class, 'update'])->name('orders.update');

            // Store / Listings
            Route::get('/store', [SellerStoreController::class, 'index'])->name('store.index');
            Route::patch('/store/products/{product}', [SellerStoreController::class, 'update'])->name('store.product.update');
            Route::delete('/store/products/{product}', [SellerStoreController::class, 'destroy'])->name('store.product.destroy');
        });

        Route::controller(ProductController::class)->group(function () {

            Route::get('products/search', 'search')->name('products.search');
            Route::post('save/store', 'saveStore')->name('save.store');
            Route::put('update/store/{id}', 'updateStore')->name('update.store');
            Route::delete('destroy/store/{id}', 'destroyStore')->name('destroy.store');

            Route::post('products/{seller_id}/toggle-favorite', 'toggleFavorite')->name('products.toggle-favorite');
        });

        Route::post('orders/{order}/buyer-received', [OrderController::class, 'buyerReceived'])->name('orders.buyer-received');

        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class);
        Route::resource('products', ProductController::class);
        Route::get('user/product/success', [CheckOutController::class, 'oneTimePaySuccess'])->name('product.oneTimePay.success');
        Route::resource('orders', OrderController::class);

        // gifts
        Route::get('gifts', [GiftsController::class, 'index'])->name('gift.index');
        Route::post('/gifts/buy', [GiftsController::class, 'buyGifts'])->name('gifts.buy');

        Route::post('/gift-coins', [IndexController::class, 'storeGiftCoins'])->name('gift.coins.store');

        // API routes for gift receiver functionality
        Route::get('/api/user/received-gifts', [IndexController::class, 'getReceivedGifts']);
        Route::post('/api/user/gifts/{giftId}/mark-read', [IndexController::class, 'markGiftAsRead']);
        Route::post('/api/user/gift-reply', [IndexController::class, 'storeGiftReply']);
        Route::get('/api/user/recent-notifications', [IndexController::class, 'getRecentNotifications']);
        Route::get('/api/user/notifications/unread-count', [IndexController::class, 'getUnreadNotificationCount']);
        Route::post('/api/user/notifications/{id}/mark-read', [IndexController::class, 'markNotificationAsRead']);

        // Convert gifts to cash route
        Route::post('/convert-gifts-to-cash', [ProfileController::class, 'convertToCash'])->name('gifts.convert.to.cash');

        // Swipe-ad tracking
        Route::post('/api/ads/{advertisement}/track', [AdTrackingController::class, 'trackEvent'])->name('ads.track');

        // Test route for debugging
        Route::post('/test-csrf', function () {
            return response()->json([
                'success' => true,
                'message' => 'CSRF test successful',
                'timestamp' => now(),
            ]);
        });
    });
});

Route::post('/save-player-id', [PushNotificationController::class, 'savePlayerId'])->name('save.player.id');
Route::post('/send-notification', [PushNotificationController::class, 'sendNotification'])->name('send.notification');

Route::middleware(['auth', 'otp.verified', 'isWizardComplete', 'throttle:linkup-agora'])->get('/agora/token', [App\Http\Controllers\AgoraController::class, 'generate'])->name('agora.token');

Route::get('/proxy/countries', function () {
    $response = \Illuminate\Support\Facades\Http::get('https://countriesnow.space/api/v0.1/countries');

    return $response->json();
});

Route::post('/proxy/states', function (\Illuminate\Http\Request $request) {
    $country = $request->input('country');
    $response = \Illuminate\Support\Facades\Http::post('https://countriesnow.space/api/v0.1/countries/states', [
        'country' => $country,
    ]);

    return $response->json();
});

Route::post('/proxy/cities', function (\Illuminate\Http\Request $request) {
    $country = $request->input('country');
    $state = $request->input('state');
    $response = \Illuminate\Support\Facades\Http::post('https://countriesnow.space/api/v0.1/countries/state/cities', [
        'country' => $country,
        'state' => $state,
    ]);

    return $response->json();
});

Route::get('/test-hide-debug', function () {
    return view('test_hide_debug');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/organizer.php';
require __DIR__.'/NewAdminRoutes.php';
require __DIR__.'/new_front.php';

Route::get('/{slug}', [App\Http\Controllers\LegalPageController::class, 'show'])->name('legal.dynamic');
