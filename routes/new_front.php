<?php

use App\Http\Controllers\NewFrontend\ChatAttachmentController;
use App\Http\Controllers\NewFrontend\ChatController;
use App\Http\Controllers\NewFrontend\DashboardController;
use App\Http\Controllers\NewFrontend\EventController;
use App\Http\Controllers\NewFrontend\LinkupController;
use App\Http\Controllers\NewFrontend\LiveStreamController;
use App\Http\Controllers\NewFrontend\MarketplaceAffiliateController;
use App\Http\Controllers\NewFrontend\NightLifeController;
use App\Http\Controllers\NewFrontend\ProfileSecurityController;
use App\Http\Controllers\NewFrontend\ReelController;
use App\Http\Controllers\NewFrontend\ReelInteractionController;
use App\Http\Controllers\NewFrontend\TicketPurchaseController;
use App\Http\Controllers\NewFrontend\VibeController;
use App\Http\Controllers\NewFrontend\VibeInteractionController;
use App\Http\Controllers\NewFrontend\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('new_frontend')->name('new_frontend.')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [ProfileSecurityController::class, 'logout'])->name('logout');
    });

    Route::middleware(['auth', 'otp.verified', 'isWizardComplete'])->group(function () {
        Route::post('/vibes', [VibeController::class, 'store'])->middleware('throttle:10,1')->name('vibes.store');
        Route::delete('/vibes/{vibe}', [VibeController::class, 'destroy'])->name('vibes.destroy');
        Route::post('/vibes/{vibe}/like', [VibeInteractionController::class, 'toggleLike'])->name('vibes.like');
        Route::get('/vibes/{vibe}/comments', [VibeInteractionController::class, 'indexComments'])->name('vibes.comments.index');
        Route::get('/vibes/{vibe}/top-comments', [VibeInteractionController::class, 'topComments'])->name('vibes.top-comments');
        Route::post('/vibes/{vibe}/comments', [VibeInteractionController::class, 'storeComment'])->name('vibes.comments.store');
        Route::post('/vibes/{vibe}/share', [VibeInteractionController::class, 'share'])->name('vibes.share');
        Route::post('/vibes/{vibe}/bigup', [VibeInteractionController::class, 'sendBigUp'])->name('vibes.bigup');
        
        // Reels routes
        Route::post('/reels', [ReelController::class, 'store'])->name('reels.store');
        Route::get('/reels/followed', [ReelController::class, 'getFollowedReels'])->name('reels.followed');
        Route::get('/reels/user/{user}', [ReelController::class, 'getUserReels'])->name('reels.user');
        Route::get('/reels/{reel}', [ReelController::class, 'show'])->name('reels.show');
        Route::delete('/reels/{reel}', [ReelController::class, 'destroy'])->name('reels.destroy');
        Route::post('/reels/{reel}/like', [ReelInteractionController::class, 'toggleLike'])->name('reels.like');
        Route::post('/reels/{reel}/comments', [ReelInteractionController::class, 'storeComment'])->name('reels.comments.store');
        Route::get('/reels/{reel}/comments', [ReelInteractionController::class, 'indexComments'])->name('reels.comments.index');
        Route::post('/reels/{reel}/share', [ReelInteractionController::class, 'share'])->name('reels.share');
        Route::post('/reels/{reel}/save', [ReelInteractionController::class, 'toggleSave'])->name('reels.save');
        Route::post('/reels/{reel}/bigup', [ReelInteractionController::class, 'sendBigUp'])->name('reels.bigup');
        Route::post('/reels/{reel}/gift', [ReelInteractionController::class, 'sendGift'])->name('reels.gift');
        Route::post('/reel-comments/{comment}/like', [ReelInteractionController::class, 'toggleCommentLike'])->name('reels.comments.like');
        Route::post('/vibes/{vibe}/purchase', [VibeInteractionController::class, 'purchaseAttachment'])->name('vibes.purchase');
        Route::post('/vibes/earnings/release', [VibeInteractionController::class, 'releasePendingEarnings'])->name('vibes.earnings.release');
        Route::post('/vibes/earnings/transfer', [VibeInteractionController::class, 'transferToWallet'])->name('vibes.earnings.transfer');
        Route::post('/vibe-comments/{comment}/like', [VibeInteractionController::class, 'toggleCommentLike'])->name('vibes.comments.like');
        Route::post('/vibes/custom-publishers', [DashboardController::class, 'storeCustomPublisher'])->name('vibes.custom-publishers.store');
        Route::get('/home', [DashboardController::class, 'home'])->name('home');
        Route::get('/vibes', [DashboardController::class, 'vibes'])->name('vibes');
        Route::get('/uvibe', [DashboardController::class, 'uvibe'])->name('uvibe');
        Route::get('/eats', [DashboardController::class, 'eats'])->name('eats');
        Route::get('/live', [LiveStreamController::class, 'index'])->name('live');
        Route::get('/live/analytics-data', [LiveStreamController::class, 'analyticsData'])->middleware('throttle:30,1')->name('live.analytics_data');
        Route::post('/live/transfer-earnings', [LiveStreamController::class, 'transferEarnings'])->middleware('throttle:5,1')->name('live.transfer_earnings');
        Route::get('/live/watch/{stream?}', [LiveStreamController::class, 'watch'])->name('live.watch');
        Route::post('/live/start', [LiveStreamController::class, 'start'])->middleware('throttle:live-stream-create')->name('live.start');
        Route::post('/live/{stream}/credentials', [LiveStreamController::class, 'credentials'])->middleware('throttle:live-stream-join')->name('live.credentials');
        Route::post('/live/{stream}/end', [LiveStreamController::class, 'end'])->middleware('throttle:live-stream-end')->name('live.end');
        Route::get('/dating', [LinkupController::class, 'index'])->name('dating');
        Route::get('/dating/find', [LinkupController::class, 'find'])->name('dating.find');
        Route::get('/dating/matches', [LinkupController::class, 'matches'])->name('dating.matches');
        Route::get('/dating/friends', [LinkupController::class, 'friends'])->name('dating.friends');
        Route::get('/dating/requests', [LinkupController::class, 'requests'])->name('dating.requests');
        Route::get('/dating/likes', [LinkupController::class, 'likes'])->name('dating.likes');
        Route::get('/dating/chats', [LinkupController::class, 'chats'])->name('dating.chats');
        Route::get('/linkup', [LinkupController::class, 'index'])->name('linkup');
        Route::get('/nightlife', [NightLifeController::class, 'index'])->name('nightlife');
        Route::get('/nightlife/{kind}/{placeId}', [NightLifeController::class, 'detail'])->name('nightlife.detail');
        Route::get('/news', [DashboardController::class, 'news'])->name('news');
        Route::get('/wallet', [DashboardController::class, 'wallet'])->name('wallet');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::get('/marketplace', [DashboardController::class, 'marketplace'])->name('marketplace');
        Route::get('/marketplace/ref/{token}', [MarketplaceAffiliateController::class, 'attribute'])->middleware('throttle:30,1')->name('marketplace.affiliate.attribute');
        Route::post('/marketplace/affiliate/promotions/{product}', [MarketplaceAffiliateController::class, 'toggle'])->middleware('throttle:20,1')->name('marketplace.affiliate.promotions.toggle');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');

        Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.mark_all_read');
        Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.mark_read');
        Route::post('/notifications/settings', [DashboardController::class, 'saveNotificationSettings'])->name('notifications.settings');

        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/convert-gifts-to-cash', [DashboardController::class, 'convertToCash'])->name('profile.gifts.convert');
        Route::put('/profile/password', [ProfileSecurityController::class, 'updatePassword'])->name('profile.password.update');

        Route::post('/linkup/swipe', [LinkupController::class, 'swipe'])->middleware('throttle:linkup-swipe')->name('linkup.swipe');
        Route::post('/linkup/request', [LinkupController::class, 'handleRequest'])->middleware('throttle:linkup-request')->name('linkup.request');

        Route::get('/dating/chats/{recipient}/messages', [ChatController::class, 'messages'])->name('dating.chats.messages');
        Route::get('/dating/chats/attachments/{message}', [ChatAttachmentController::class, 'show'])->name('dating.chats.attachments.show');
        Route::post('/dating/chats/{recipient}/messages', [ChatController::class, 'store'])->middleware('throttle:linkup-message')->name('dating.chats.messages.store');
        Route::post('/dating/chats/{recipient}/pin', [ChatController::class, 'togglePin'])->middleware('throttle:linkup-message')->name('dating.chats.pin');

        Route::get('/events', [EventController::class, 'events'])->name('events');
        Route::post('/events/tax-rate', [EventController::class, 'taxRate'])->name('events.tax-rate');
        Route::post('/events/{event}/toggle-favorite', [EventController::class, 'toggleFavorite'])->name('events.toggle-favorite');
        Route::post('/events/toggle-follow-organizer', [App\Http\Controllers\Frontend\EventController::class, 'toggleFollowOrganizer'])->name('events.toggle-follow-organizer');
        Route::post('/events/bookings/{ticketSale}/cancel', [EventController::class, 'cancelBooking'])->name('events.bookings.cancel');
        Route::post('/events/bookings/{ticketSale}/cancel-request', [EventController::class, 'cancelRequest'])->name('events.bookings.cancel-request');
        Route::get('/events/{event}/reviews', [EventController::class, 'reviewsIndex'])->name('events.reviews.index');
        Route::post('/events/{event}/reviews', [EventController::class, 'reviewStore'])->name('events.reviews.store');

        Route::post('/stripe/buy-ticket', [TicketPurchaseController::class, 'buyTicketStripe'])->name('stripe.buy-ticket');
        Route::get('/stripe/buy-ticket/success', [TicketPurchaseController::class, 'stripeSuccess'])->name('stripe.buy-ticket.success');

        Route::middleware(['check.user_wallet_kyc'])->group(function () {
            Route::post('/live/start', [LiveStreamController::class, 'start'])->name('live.start');
            Route::post('/wallet/checkout', [WalletController::class, 'submitAmountToWallet'])->name('wallet.checkout');
            Route::get('/wallet/payment/success', [WalletController::class, 'oneTimePaySuccess'])->name('wallet.payment_success');
            Route::post('/wallet/coin/checkout', [WalletController::class, 'submitCoinToWallet'])->name('wallet.coin.checkout');
            Route::get('/wallet/coin/payment/success', [WalletController::class, 'oneTimePayCoinSuccess'])->name('wallet.coin_payment_success');
            Route::post('/wallet/contact', [WalletController::class, 'addContact'])->name('wallet.contact');
            Route::delete('/wallet/contact/{contact}', [WalletController::class, 'removeContact'])->name('wallet.contact.remove');
            Route::post('/wallet/send-money', [WalletController::class, 'sendMoney'])->name('wallet.send_money');
            Route::post('/wallet/request-money', [WalletController::class, 'requestMoney'])->name('wallet.request_money');
            Route::post('/wallet/requested-money/{id}/pay', [WalletController::class, 'payMoneyRequest'])->name('wallet.requested_money.pay');
            Route::post('/wallet/requested-money/{id}/reject', [WalletController::class, 'rejectMoneyRequest'])->name('wallet.requested_money.reject');
            Route::post('/wallet/requested-money/{id}/cancel', [WalletController::class, 'cancelMoneyRequest'])->name('wallet.requested_money.cancel');
            Route::post('/wallet/buy-ticket', [TicketPurchaseController::class, 'buyTicketWallet'])->name('wallet.buy-ticket');
        });
    });
});
