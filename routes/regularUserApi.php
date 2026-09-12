<?php

use App\Http\Controllers\Api\UserWizardController;
use App\Http\Controllers\Frontend\LiveStreamGumletController;
use App\Http\Controllers\NewFrontend\NightLifeController;
use App\Http\Controllers\V1\AppWalletController;
use App\Http\Controllers\V1\ChatController;
use App\Http\Controllers\V1\EventController;
use App\Http\Controllers\V1\FavoriteSellerController;
use App\Http\Controllers\V1\FriendController;
use App\Http\Controllers\V1\FriendRequestController;
use App\Http\Controllers\V1\HomePageController;
use App\Http\Controllers\V1\LinkupController;
use App\Http\Controllers\V1\LiveStreamController;
use App\Http\Controllers\V1\NewsController;
use App\Http\Controllers\V1\Wallet\UserWalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('regular-user')->name('regularUserAPI.')->group(function () {

    Route::post('check-linkup-id', [UserWizardController::class, 'checkLinkupId']);

    Route::post('register', [UserWizardController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('regular-user')
        ->name('regularUserAPI.')
        ->group(function () {

            Route::post('send-otp', [UserWizardController::class, 'sendOtp']);

            Route::post('resend-otp/{token}', [UserWizardController::class, 'resendOtp']);

            Route::post('verify-otp/{token}', [UserWizardController::class, 'verifyOtp']);

            Route::post('wizard-update', [UserWizardController::class, 'wizardUpdate']);

            Route::get('home', [HomePageController::class, 'getReleatedUsers']);

            Route::post('send-gift', [HomePageController::class, 'storeGiftCoins']);

            Route::post('profile/like/{user}', [HomePageController::class, 'likeUser']);

            Route::post('profile/dislike/{user}', [HomePageController::class, 'dislikeUser']);

            Route::get('user/detail/{slug}/{user}', [HomePageController::class, 'getLinkupUser']);

            Route::post('profile/block-user', [HomePageController::class, 'blockUser']);

            Route::post('profile/unblock-user', [HomePageController::class, 'unblockUser']);

            Route::get('friend-request', [FriendRequestController::class, 'index'])->name('friend-request.index');

            Route::post('friend-request/{slug}/{user}', [FriendRequestController::class, 'sendRequest']);
            Route::post('accept-friend-request/{friendRequest}', [FriendRequestController::class, 'acceptRequest'])->name('friend-request.accept');

            Route::post('reject-friend-request/{friendRequest}/reject', [FriendRequestController::class, 'rejectRequest'])->name('friend-request.reject');

            Route::post('falg-user', [FriendRequestController::class, 'falgedUser']);

            Route::get('friend', [FriendController::class, 'index'])->name('friend.index');

            Route::post('friend/{id}/unfriend', [FriendController::class, 'unfriend'])->name('friend.unfriend');

            Route::get('all-matches', [HomePageController::class, 'getMatches'])->name('user.matches');

            // Linkup swipe and mutual-match APIs used by the new Linkup UI.
            Route::post('linkup/swipe', [LinkupController::class, 'swipe'])
                ->middleware('throttle:linkup-swipe')
                ->name('linkup.swipe');
            Route::get('linkup/matches', [LinkupController::class, 'matches'])
                ->name('linkup.matches');

            Route::prefix('dating')->name('dating.')->group(function () {
                Route::get('/', [LinkupController::class, 'index'])->name('index');
                Route::get('find', [LinkupController::class, 'find'])->name('find');
                Route::get('matches', [LinkupController::class, 'matches'])->name('matches');
                Route::get('friends', [LinkupController::class, 'friends'])->name('friends');
                Route::get('requests', [LinkupController::class, 'requests'])->name('requests');
                Route::get('likes', [LinkupController::class, 'likes'])->name('likes');
                Route::get('chats', [LinkupController::class, 'chats'])->name('chats');
                Route::post('swipe', [LinkupController::class, 'swipe'])->middleware('throttle:linkup-swipe')->name('swipe');
            });

            Route::get('likes', [HomePageController::class, 'getLikes'])->name('user.likes');

            Route::get('user/chat', [ChatController::class, 'index']);

            Route::get('user/chat/{slug}/{user}', [ChatController::class, 'startChat']);

            Route::post('/chat/toggle-unpin/{userId}', [ChatController::class, 'toggleUnPin']);

            Route::post('/chat/toggle-pin/{userId}', [ChatController::class, 'togglePin']);
            Route::get('/allnotifications', [HomePageController::class, 'userNotifation']);
            Route::post('/allnotifications/mark-all-read', [HomePageController::class, 'markAllNotificationsRead'])
                ->name('notifications.mark_all_read');
            Route::post('/allnotifications/{notification}/read', [HomePageController::class, 'markNotificationRead'])
                ->name('notifications.mark_read');
            Route::post('/allnotifications/settings', [HomePageController::class, 'saveNotificationSettings'])
                ->name('notifications.settings');

            Route::post('/send/{toUserId}/messages', [ChatController::class, 'store']);
            Route::get('user/wallet', [UserWalletController::class, 'index']);

            Route::prefix('wallet')->group(function () {
                Route::post('contact', [UserWalletController::class, 'addContact'])->name('wallet.contact');
                Route::post('send-money', [UserWalletController::class, 'sendMoney'])->name('wallet.send_money');
                Route::post('request-money', [UserWalletController::class, 'requestMoney'])->name('wallet.request_money');
                Route::post('requested-money/{id}/pay', [UserWalletController::class, 'payMoneyRequest'])->name('wallet.requested_money.pay');
                Route::post('requested-money/{id}/reject', [UserWalletController::class, 'rejectMoneyRequest'])->name('wallet.requested_money.reject');
                Route::post('requested-money/{id}/cancel', [UserWalletController::class, 'cancelMoneyRequest'])->name('wallet.requested_money.cancel');

                Route::post('asues/store', [UserWalletController::class, 'storeAsue'])->name('wallet.asues.store');
                Route::post('asues/{asue}/accept-participation', [UserWalletController::class, 'acceptAsueParticipation'])->name('wallet.asues.accept_participation');
                Route::post('asues/{asue}/simulate-cycle', [UserWalletController::class, 'simulateAsueCycle'])->name('wallet.asues.simulate_cycle');
                Route::post('asues/{asue}/simulate-accept', [UserWalletController::class, 'simulateAsueAccept'])->name('wallet.asues.simulate_accept');

                Route::get('get-amount', [AppWalletController::class, 'addAmoutPage'])->name('wallet.get_amount');
                Route::post('add-amount', [AppWalletController::class, 'submitAmountToWallet'])->name('wallet.add_amount');
                Route::post('save-data', [AppWalletController::class, 'oneTimePaySuccess'])->name('wallet.save_data');
                Route::get('activity', [AppWalletController::class, 'walletActivity'])->name('wallet.activity');

                Route::post('coin/checkout', [AppWalletController::class, 'submitCoinToWallet'])->name('wallet.coin_checkout');
                Route::post('coin/save-data', [AppWalletController::class, 'oneTimePayCoinSuccess'])->name('wallet.coin_save_data');
            });

            Route::name('frontend.')->group(function () {
                Route::post('go-live/token', [LiveStreamGumletController::class, 'generateToken'])->name('go-live.token');
                Route::post('go-live/start-agora', [LiveStreamGumletController::class, 'startAgoraStream'])->name('go-live.start-agora');
                Route::post('go-live/end', [LiveStreamGumletController::class, 'endAgoraStream'])->name('go-live.end');
                Route::post('go-live/update-settings/{stream}', [LiveStreamGumletController::class, 'updateStreamSettings'])->name('go-live.update-settings');
                Route::post('live/{stream}/gift', [LiveStreamGumletController::class, 'sendGift'])->name('live.gift');
                Route::post('live/{stream}/invite', [LiveStreamGumletController::class, 'inviteGuest'])->name('live.invite');
                Route::post('live/{stream}/invite/reply', [LiveStreamGumletController::class, 'replyInvite'])->name('live.invite.reply');
                Route::post('live/{stream}/guest/remove', [LiveStreamGumletController::class, 'removeGuest'])->name('live.guest.remove');
                Route::post('live/{stream}/reaction', [LiveStreamGumletController::class, 'sendReaction'])->name('live.reaction');
                Route::post('live/{stream}/comment', [LiveStreamGumletController::class, 'sendComment'])->name('live.comment');
                Route::post('live/{stream}/host/heartbeat', [LiveStreamGumletController::class, 'hostHeartbeat'])->name('live.host.heartbeat');
                Route::post('live/{stream}/viewer/join', [LiveStreamGumletController::class, 'joinViewer'])->name('live.viewer.join');
                Route::post('live/{stream}/viewer/heartbeat', [LiveStreamGumletController::class, 'viewerHeartbeat'])->name('live.viewer.heartbeat');
                Route::post('live/{stream}/viewer/leave', [LiveStreamGumletController::class, 'leaveViewer'])->name('live.viewer.leave');
                Route::get('live/{stream}/viewers', [LiveStreamGumletController::class, 'getViewers'])->name('live.viewers');
                Route::get('live/{stream}/stats', [LiveStreamGumletController::class, 'getStreamStats'])->name('live.stats');
                Route::post('live/{stream}/transfer-earnings', [LiveStreamGumletController::class, 'transferSessionEarnings'])->middleware('throttle:5,1')->name('live.transfer-earnings');
            });

            Route::prefix('live-streams')->group(function () {
                Route::get('network', [LiveStreamController::class, 'network']);
                Route::get('categories', [LiveStreamController::class, 'categories']);
            });

            Route::prefix('nightlife')->group(function () {
                Route::get('/', [NightLifeController::class, 'index'])->name('nightlife.index');
                Route::get('/{kind}/{placeId}', [NightLifeController::class, 'detail'])->name('nightlife.detail');
            });

            Route::prefix('events')->group(function () {
                Route::get('/', [EventController::class, 'homeData']);
                Route::get('/categories', [EventController::class, 'getAllEventCategories']);
                Route::get('/filter', [EventController::class, 'filterEvents']);
                Route::get('/search', [EventController::class, 'filterEvents']);
                Route::get('/favorites', [EventController::class, 'getFavouriteEvent']);
                Route::get('/invitation/users', [EventController::class, 'fetchUserForInvitation']);
                Route::post('/invitation/send', [EventController::class, 'sendUserInvitationEmail']);
                Route::post('/organizer/toggle-follow', [EventController::class, 'toggleFollowOrganizer']);
                Route::get('/bookings', [EventController::class, 'bookings'])->name('events.bookings.index');
                Route::get('/bookings/cancelled', [EventController::class, 'bookings'])->defaults('status', 'cancelled')->name('events.bookings.cancelled');
                Route::get('/bookings/{ticketSale}', [EventController::class, 'showBooking'])->name('events.bookings.show');
                Route::post('/bookings/{ticketSale}/cancel', [EventController::class, 'cancelBooking'])->name('events.bookings.cancel');
                Route::post('/bookings/{ticketSale}/cancel-request', [EventController::class, 'cancelBookingRequest'])->name('events.bookings.cancel_request');

                Route::get('/{event:slug}', [EventController::class, 'showEvent']);
                Route::get('/{event}/tickets', [EventController::class, 'eventTickets']);
                Route::post('/{event}/tickets/buy-wallet', [EventController::class, 'buyTicketWallet']);
                Route::post('/{event}/tickets/buy-stripe', [EventController::class, 'buyTicketStripeInitiate']);
                Route::post('/{event}/toggle-favorite', [EventController::class, 'toggleFavoriteByEvent']);

                Route::get('/{event}/reviews', [EventController::class, 'reviewsIndex']);
                Route::post('/{event}/reviews', [EventController::class, 'reviewStore']);
                Route::put('/{event}/reviews/{review}', [EventController::class, 'reviewUpdate']);
                Route::delete('/{event}/reviews/{review}', [EventController::class, 'reviewDestroy']);
            });

            Route::prefix('news')->name('news.')->group(function () {
                Route::get('/', [NewsController::class, 'newsIndex'])->name('index');
                Route::post('/', [NewsController::class, 'storeNews'])->name('store');
                Route::post('/{news}/trending', [NewsController::class, 'incrementNewsTrending'])->name('trending');
                Route::get('/{news}', [NewsController::class, 'showNews'])->name('show');
                Route::put('/{news}', [NewsController::class, 'updateNews'])->name('update');
                Route::delete('/{news}', [NewsController::class, 'destroyNews'])->name('destroy');
            });
            // Favorite sellers
            Route::post('favorite-sellers', [FavoriteSellerController::class, 'store'])->name('favorite-sellers.store');
            Route::put('news-status/{news}', [NewsController::class, 'toggleNewsStatus'])->name('news.status');
        });
});
