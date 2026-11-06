<?php

use App\Http\Controllers\User\ChatAttachmentController;
use App\Http\Controllers\User\ChatController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\EventController;
use App\Http\Controllers\User\Gifts\GiftCashOutController;
use App\Http\Controllers\User\LinkupController;
use App\Http\Controllers\User\LiveStreamController;
use App\Http\Controllers\User\MarketplaceAffiliateController;
use App\Http\Controllers\User\NightLifeController;
use App\Http\Controllers\User\ProfileSecurityController;
use App\Http\Controllers\User\ReelController;
use App\Http\Controllers\User\ReelInteractionController;
use App\Http\Controllers\User\TicketPurchaseController;
use App\Http\Controllers\User\VibeChatController;
use App\Http\Controllers\User\VibeController;
use App\Http\Controllers\User\VibeInteractionController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\UniversityController;
use App\Http\Controllers\User\UVibeController;
use App\Http\Controllers\User\UVibeOrganizationController;
use App\Http\Controllers\User\UVibePostController;
use App\Http\Controllers\User\UVibeSearchController;
use App\Http\Controllers\User\UVibeThankYouController;
use App\Http\Controllers\LegacyUrlRedirectController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->name('user.')->group(function () {
    // Public campus list (sign-up page + Campus Explorer).
    Route::get('/universities', [UniversityController::class, 'catalog'])->middleware('throttle:60,1,universities-catalog:')->name('universities.catalog');

    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [ProfileSecurityController::class, 'logout'])->name('logout');
    });

    Route::middleware(['auth', 'otp.verified', 'isWizardComplete'])->group(function () {
        // U Vibe campuses (same controller as the mobile API in routes/api/mobile.php)
        Route::prefix('universities')->name('universities.')->controller(UniversityController::class)->group(function () {
            Route::get('/state', 'state')->name('state');
            Route::post('/mine', 'addMine')->middleware('throttle:20,1,mine-store:')->name('mine.store');
            Route::delete('/mine/{university}', 'removeMine')->name('mine.destroy');
            Route::patch('/mine/{university}', 'setStatus')->name('mine.status');
            Route::post('/mine/{university}/main', 'makeMain')->name('mine.main');
            Route::post('/{university}/follow', 'follow')->name('follow');
            Route::delete('/{university}/follow', 'unfollow')->name('unfollow');
        });
        Route::post('/vibes', [VibeController::class, 'store'])->middleware('throttle:10,1,vibes-store:')->name('vibes.store');
        Route::delete('/vibes/{vibe}', [VibeController::class, 'destroy'])->name('vibes.destroy');
        Route::put('/vibes/{vibe}', [VibeController::class, 'update'])->name('vibes.update');
        Route::post('/vibes/{vibe}/update', [VibeController::class, 'update'])->name('vibes.update.post');
        Route::get('/vibes/{vibe}/copy-link', [VibeController::class, 'copyLink'])->name('vibes.copy-link');
        Route::get('/vibes/suggested-creators', [VibeController::class, 'suggestedCreators'])->name('vibes.suggested-creators');
        Route::post('/creators/{user}/toggle-follow', [VibeController::class, 'toggleFollowCreator'])->middleware('throttle:60,1,creators-toggle-follow:')->name('creators.toggle-follow');
        Route::post('/vibes/{vibe}/like', [VibeInteractionController::class, 'toggleLike'])->middleware('throttle:120,1,vibes-like:')->name('vibes.like');
        Route::get('/vibes/{vibe}/comments', [VibeInteractionController::class, 'indexComments'])->name('vibes.comments.index');
        Route::get('/vibes/{vibe}/top-comments', [VibeInteractionController::class, 'topComments'])->name('vibes.top-comments');
        Route::post('/vibes/{vibe}/comments', [VibeInteractionController::class, 'storeComment'])->middleware('throttle:30,1,vibes-comments-store:')->name('vibes.comments.store');
        Route::post('/vibes/{vibe}/share', [VibeInteractionController::class, 'share'])->name('vibes.share');
        Route::post('/vibes/{vibe}/repost', [VibeInteractionController::class, 'repost'])->middleware('throttle:20,1,vibes-repost:')->name('vibes.repost');
        Route::post('/vibes/{vibe}/bigup', [VibeInteractionController::class, 'sendBigUp'])->middleware('throttle:30,1,vibes-bigup:')->name('vibes.bigup');
        Route::post('/vibes/bigup', [VibeInteractionController::class, 'sendBigUp'])->middleware('throttle:30,1,vibes-send-vibe:')->name('vibes.send-vibe');
        Route::get('/vibes/activity', [DashboardController::class, 'activityApi'])->name('vibes.activity');
        Route::get('/vibes/gifts-received', [DashboardController::class, 'giftsReceivedApi'])->name('vibes.gifts-received');
        Route::post('/vibes/thank-sender', [DashboardController::class, 'thankSender'])->middleware('throttle:20,1,vibes-thank-sender:')->name('vibes.thank-sender');
        Route::get('/vibes/chats/conversations', [VibeChatController::class, 'getConversations'])->name('vibes.chats.conversations');
        Route::get('/vibes/chats/{recipient}/messages', [VibeChatController::class, 'getMessages'])->name('vibes.chats.messages');
        Route::post('/vibes/chats/{recipient}/messages', [VibeChatController::class, 'storeMessage'])->middleware('throttle:30,1,vibes-chats-messages-store:')->name('vibes.chats.messages.store');

        // Reels routes
        Route::post('/reels', [ReelController::class, 'store'])->middleware('throttle:10,1,reels-store:')->name('reels.store');
        Route::get('/reels/followed', [ReelController::class, 'getFollowedReels'])->name('reels.followed');
        Route::get('/reels/user/{user}', [ReelController::class, 'getUserReels'])->name('reels.user');
        Route::get('/reels/{reel}', [ReelController::class, 'show'])->name('reels.show');
        Route::delete('/reels/{reel}', [ReelController::class, 'destroy'])->name('reels.destroy');
        Route::post('/reels/{reel}/like', [ReelInteractionController::class, 'toggleLike'])->middleware('throttle:120,1,reels-like:')->name('reels.like');
        Route::post('/reels/{reel}/comments', [ReelInteractionController::class, 'storeComment'])->middleware('throttle:30,1,reels-comments-store:')->name('reels.comments.store');
        Route::get('/reels/{reel}/comments', [ReelInteractionController::class, 'indexComments'])->name('reels.comments.index');
        Route::post('/reels/{reel}/share', [ReelInteractionController::class, 'share'])->name('reels.share');
        Route::post('/reels/{reel}/save', [ReelInteractionController::class, 'toggleSave'])->name('reels.save');
        Route::post('/reels/{reel}/bigup', [ReelInteractionController::class, 'sendBigUp'])->middleware('throttle:30,1,reels-bigup:')->name('reels.bigup');
        Route::post('/reels/{reel}/gift', [ReelInteractionController::class, 'sendGift'])->middleware('throttle:30,1,reels-gift:')->name('reels.gift');
        Route::post('/reel-comments/{comment}/like', [ReelInteractionController::class, 'toggleCommentLike'])->middleware('throttle:120,1,reels-comments-like:')->name('reels.comments.like');
        Route::delete('/reel-comments/{comment}', [ReelInteractionController::class, 'destroyComment'])->middleware('throttle:60,1,reels-comments-destroy:')->name('reels.comments.destroy');
        // Buy the item tagged on a post: priced on the server, a real order, paid from the wallet.
        Route::post('/vibes/{vibe}/purchase/quote', [\App\Http\Controllers\User\VibeShopController::class, 'quote'])->middleware('throttle:60,1,vibes-purchase-quote:')->name('vibes.purchase.quote');
        Route::post('/vibes/{vibe}/purchase', [\App\Http\Controllers\User\VibeShopController::class, 'buy'])->middleware('throttle:10,1,vibes-purchase:')->name('vibes.purchase');
        Route::post('/vibes/{vibe}/hide', [VibeController::class, 'hide'])->middleware('throttle:60,1,vibes-hide:')->name('vibes.hide');
        Route::post('/vibes/{vibe}/report', [VibeController::class, 'report'])->middleware('throttle:10,1,vibes-report:')->name('vibes.report');
        Route::get('/vibes/report-reasons', [VibeController::class, 'reportReasons'])->name('vibes.report-reasons');
        Route::get('/vibes/search', [\App\Http\Controllers\User\VibeExploreController::class, 'search'])->middleware('throttle:120,1,vibes-search:')->name('vibes.search');
        Route::post('/vibes/earnings/release', [VibeInteractionController::class, 'releasePendingEarnings'])->middleware('throttle:6,1,vibes-earnings-release:')->name('vibes.earnings.release');
        Route::post('/vibes/earnings/transfer', [VibeInteractionController::class, 'transferToWallet'])->middleware('throttle:6,1,vibes-earnings-transfer:')->name('vibes.earnings.transfer');
        Route::post('/vibe-comments/{comment}/like', [VibeInteractionController::class, 'toggleCommentLike'])->middleware('throttle:120,1,vibes-comments-like:')->name('vibes.comments.like');
        Route::delete('/vibe-comments/{comment}', [VibeInteractionController::class, 'destroyComment'])->middleware('throttle:60,1,vibes-comments-destroy:')->name('vibes.comments.destroy');
        Route::post('/vibes/custom-publishers', [DashboardController::class, 'storeCustomPublisher'])->middleware('throttle:20,1,vibes-custom-publishers-store:')->name('vibes.custom-publishers.store');
        Route::get('/home', [DashboardController::class, 'home'])->name('feed');
        Route::get('/vibes', [DashboardController::class, 'vibes'])->name('vibes');
        Route::get('/vibes/profile/{user}', [DashboardController::class, 'vibeUserProfile'])->name('vibes.user-profile');
        Route::get('/uvibe', [DashboardController::class, 'uvibe'])->name('uvibe');
        // U Vibe campus feed + the "Post to U Vibe" button (same controller as the mobile API in routes/api/mobile.php)
        Route::get('/uvibe/posts', [UVibeController::class, 'index'])->middleware('throttle:60,1,uvibe-posts-index:')->name('uvibe.posts.index');
        Route::post('/uvibe/posts', [UVibeController::class, 'store'])->middleware('throttle:10,1,uvibe-posts-store:')->name('uvibe.posts.store');
        Route::get('/uvibe/options', [UVibeController::class, 'options'])->name('uvibe.options');
        // "Post as → An organization": the organizations a member posts for
        Route::get('/uvibe/organizations', [UVibeOrganizationController::class, 'index'])->name('uvibe.organizations.index');
        Route::post('/uvibe/organizations', [UVibeOrganizationController::class, 'store'])->middleware('throttle:20,1,uvibe-organizations-store:')->name('uvibe.organizations.store');
        // What a member can do with a post in the feed: edit, delete, hide, report, repost, Boost, and Campus Gig apply/pay
        Route::put('/uvibe/posts/{vibe}', [UVibePostController::class, 'update'])->middleware('throttle:30,1,uvibe-posts-update:')->name('uvibe.posts.update');
        Route::delete('/uvibe/posts/{vibe}', [UVibePostController::class, 'destroy'])->middleware('throttle:30,1,uvibe-posts-destroy:')->name('uvibe.posts.destroy');
        Route::post('/uvibe/posts/{vibe}/hide', [UVibePostController::class, 'hide'])->middleware('throttle:60,1,uvibe-posts-hide:')->name('uvibe.posts.hide');
        Route::post('/uvibe/posts/{vibe}/report', [UVibePostController::class, 'report'])->middleware('throttle:10,1,uvibe-posts-report:')->name('uvibe.posts.report');
        Route::post('/uvibe/posts/{vibe}/repost', [UVibePostController::class, 'repost'])->middleware('throttle:20,1,uvibe-posts-repost:')->name('uvibe.posts.repost');
        Route::get('/uvibe/boosts', [UVibePostController::class, 'boosts'])->name('uvibe.boosts');
        Route::post('/uvibe/posts/{vibe}/boost', [UVibePostController::class, 'boost'])->middleware('throttle:30,1,uvibe-posts-boost:')->name('uvibe.posts.boost');
        Route::post('/uvibe/posts/{vibe}/apply', [UVibePostController::class, 'apply'])->middleware('throttle:20,1,uvibe-posts-apply:')->name('uvibe.posts.apply');
        Route::get('/uvibe/posts/{vibe}/applicants', [UVibePostController::class, 'applicants'])->middleware('throttle:60,1,uvibe-posts-applicants:')->name('uvibe.posts.applicants');
        Route::post('/uvibe/posts/{vibe}/applications/{application}/pay', [UVibePostController::class, 'pay'])->middleware('throttle:6,1,uvibe-posts-pay:')->name('uvibe.posts.pay');
        // Header: the search window (people + campuses) and "Say Thank You" (Vibes gifts + Boosts you received)
        Route::get('/uvibe/search', [UVibeSearchController::class, 'index'])->middleware('throttle:120,1,uvibe-search:')->name('uvibe.search');
        Route::get('/uvibe/thank-you', [UVibeThankYouController::class, 'index'])->middleware('throttle:60,1,uvibe-thank-you-index:')->name('uvibe.thank-you.index');
        Route::post('/uvibe/thank-you', [UVibeThankYouController::class, 'store'])->middleware('throttle:20,1,uvibe-thank-you-store:')->name('uvibe.thank-you.store');
        Route::get('/eats', [DashboardController::class, 'eats'])->name('eats');
        Route::get('/live', [LiveStreamController::class, 'index'])->name('live');
        Route::get('/live/analytics-data', [LiveStreamController::class, 'analyticsData'])->middleware('throttle:30,1,live-analytics_data:')->name('live.analytics_data');
        Route::post('/live/transfer-earnings', [LiveStreamController::class, 'transferEarnings'])->middleware('throttle:5,1,live-transfer_earnings:')->name('live.transfer_earnings');
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
        Route::get('/wallet-hub', [DashboardController::class, 'wallet'])->name('wallet');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::get('/marketplace', [DashboardController::class, 'marketplace'])->name('marketplace');
        Route::get('/marketplace/ref/{token}', [MarketplaceAffiliateController::class, 'attribute'])->middleware('throttle:30,1,marketplace-affiliate-attribute:')->name('marketplace.affiliate.attribute');
        Route::post('/marketplace/affiliate/promotions/{product}', [MarketplaceAffiliateController::class, 'toggle'])->middleware('throttle:20,1,marketplace-affiliate-promotions-toggle:')->name('marketplace.affiliate.promotions.toggle');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');

        Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.mark_all_read');
        Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.mark_read');
        Route::post('/notifications/settings', [DashboardController::class, 'saveNotificationSettings'])->name('notifications.settings');

        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/convert-gifts-to-cash', [GiftCashOutController::class, 'store'])->middleware('throttle:6,1,profile-gifts-convert:')->name('profile.gifts.convert');
        Route::put('/profile/password', [ProfileSecurityController::class, 'updatePassword'])->name('profile.password.update');
        Route::put('/profile/transaction-pin', [ProfileSecurityController::class, 'updateTransactionPin'])->name('profile.transaction_pin.update');

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
            Route::post('/wallet-hub/checkout', [WalletController::class, 'submitAmountToWallet'])->name('wallet.checkout');
            Route::get('/wallet-hub/payment/success', [WalletController::class, 'oneTimePaySuccess'])->name('wallet.payment_success');
            Route::post('/wallet-hub/coin/checkout', [WalletController::class, 'submitCoinToWallet'])->name('wallet.coin.checkout');
            Route::get('/wallet-hub/coin/payment/success', [WalletController::class, 'oneTimePayCoinSuccess'])->name('wallet.coin_payment_success');
            Route::post('/wallet-hub/contact', [WalletController::class, 'addContact'])->name('wallet.contact');
            Route::delete('/wallet-hub/contact/{contact}', [WalletController::class, 'removeContact'])->name('wallet.contact.remove');
            Route::post('/wallet-hub/send-money', [WalletController::class, 'sendMoney'])->name('wallet.send_money');
            Route::post('/wallet-hub/request-money', [WalletController::class, 'requestMoney'])->name('wallet.request_money');
            Route::post('/wallet-hub/requested-money/{id}/pay', [WalletController::class, 'payMoneyRequest'])->name('wallet.requested_money.pay');
            Route::post('/wallet-hub/requested-money/{id}/reject', [WalletController::class, 'rejectMoneyRequest'])->name('wallet.requested_money.reject');
            Route::post('/wallet-hub/requested-money/{id}/cancel', [WalletController::class, 'cancelMoneyRequest'])->name('wallet.requested_money.cancel');
            Route::post('/wallet-hub/buy-ticket', [TicketPurchaseController::class, 'buyTicketWallet'])->name('wallet.buy-ticket');
            Route::post('/wallet-hub/bank-accounts', [WalletController::class, 'addBankAccount'])->middleware('throttle:10,1,wallet-bank-add:')->name('wallet.bank_accounts.store');
            Route::post('/wallet-hub/bank-withdrawal', [WalletController::class, 'withdrawToBank'])->middleware('throttle:6,1,wallet-bank-withdraw:')->name('wallet.bank_withdrawal');
        });
    });
});

// The member app used to live under /new_frontend. Every old address is redirected for good, so emailed links,
// push-notification links, Stripe returns and bookmarks keep working. Safe to delete once old links have aged out.
Route::any('new_frontend/{path?}', LegacyUrlRedirectController::class)->where('path', '.*')->name('legacy.new_frontend');
