# User folder restructure - dry run

- files that move: **456** (php controllers 29, components 208, layouts 4, pages 214, routes 1)
- files whose text changes (imports, page names, route names, URLs, namespaces): **252**
- rewritten: 448 imports/paths, 88 Inertia page names, 198 route names, 29 URLs, 77 PHP namespaces
- Inertia page names that change: 210

## Must fix before apply
_nothing - clean_

## Warnings
_none_

## Shared files that are moving but are also imported from admin / organizer / legal code

These imports are rewritten too, but check the file really belongs in User:

- pages/organizer -> resources/js/components/new_frontend/modals/TicketDetailModal.vue  (1)

## Pre-existing problems (not caused by this change)

- relative/alias imports that already do not resolve today: **109** (see below, first 60)
  - resources/js/app.ts: ./pages/admin/
  - resources/js/app.ts: ./pages/admin/auth/
  - resources/js/app.ts: ./pages/admin/events/
  - resources/js/app.ts: ./pages/admin/taxes/
  - resources/js/client/models/Order.ts: @/types
  - resources/js/client/models/OrderItem.ts: @/types
  - resources/js/client/models/UserTable.ts: @/types/index
  - resources/js/components/admin/AppHeader.vue: @/types
  - resources/js/components/admin/AppShell.vue: @/types
  - resources/js/components/admin/AppSidebar.vue: @/types
  - resources/js/components/admin/AppSidebarHeader.vue: @/types
  - resources/js/components/admin/NavFooter.vue: @/types
  - resources/js/components/admin/NavMain.vue: @/types
  - resources/js/components/admin/NavUser.vue: @/types
  - resources/js/components/admin/UserInfo.vue: @/types
  - resources/js/components/admin/UserMenuContent.vue: @/types
  - resources/js/components/FooterLogo.vue: ./../../assets/images/logo.PNG
  - resources/js/composables/useCountryStateCity.ts: @/types
  - resources/js/layouts/admin/AppLayout.vue: @/types
  - resources/js/layouts/app/AppHeaderLayout.vue: @/types
  - resources/js/layouts/app/AppSidebarLayout.vue: @/types
  - resources/js/layouts/AuthenticatedLayoutBackup.vue: @/Components/Dropdown.vue
  - resources/js/layouts/AuthenticatedLayoutBackup.vue: @/Components/DropdownLink.vue
  - resources/js/layouts/settings/Layout.vue: @/types
  - resources/js/pages/admin/AskAIDashboard.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/BusinessUnitsDashboard.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/DeliveriesDispatch.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/DriverOnboarding.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/DriverPolicies.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsAnalytics.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsAppLive.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsDashboard.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsDeliveryZones.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsDriverPayouts.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsDrivers.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsFeesPricing.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsMenus.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsOrders.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsPromotions.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsRestaurantPayouts.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsRestaurants.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsReviews.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/LinkUpEats/EatsSupport.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceCategories.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceDashboard.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceEscrow.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceFees.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceOrders.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceOrderView.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceProductCreate.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceProductEdit.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceProducts.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceProductView.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceSellers.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceSellerTransfers.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Marketplace/MarketplaceWallets.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/MerchantPayOnboarding.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Merchants/MerchantDashboard.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Merchants/MerchantDirectory.vue: @/../../resources/css/new_admin.css
  - resources/js/pages/admin/Commerce/Merchants/MerchantKyc.vue: @/../../resources/css/new_admin.css
- controllers that render a page file that does not exist: **141**
  - app/Http/Controllers/Admin/AdminController.php: admin/adminlist/Index
  - app/Http/Controllers/Admin/AdminController.php: admin/adminlist/Component/Create
  - app/Http/Controllers/Admin/AdminController.php: admin/adminlist/Component/Edit
  - app/Http/Controllers/Admin/AdminFeeController.php: admin/subscriptions/AdminFee/Index
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/Dashboard
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/Analytics
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/Reports
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/AllAds
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/RestaurantsAds
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/ClubsFetesAds
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/Campaigns
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/EmailSponsorAds
  - app/Http/Controllers/Admin/Ads/AdsController.php: admin/ads/C360News
  - app/Http/Controllers/Admin/AppSettingController.php: admin/settings/AppSetting
  - app/Http/Controllers/Admin/AppSettingController.php: admin/events/Setting
  - app/Http/Controllers/Admin/BankWithdrawalController.php: admin/BankWithdrawals/Index
  - app/Http/Controllers/Admin/CancelTicketController.php: admin/events/cancelTicket/Index
  - app/Http/Controllers/Admin/CashoutController.php: admin/cashout/Index
  - app/Http/Controllers/Admin/CashoutController.php: admin/cashout/Show
  - app/Http/Controllers/Admin/ClubFeteController.php: admin/clubfete/ClubsRestaurants
  - app/Http/Controllers/Admin/ClubFeteController.php: admin/clubfete/Create
  - app/Http/Controllers/Admin/ClubFeteController.php: admin/clubfete/Show
  - app/Http/Controllers/Admin/ClubFeteController.php: admin/clubfete/Edit
  - app/Http/Controllers/Admin/CouponController.php: admin/coupons/Index
  - app/Http/Controllers/Admin/Dashboards/EventDashboardController.php: admin/Dashboards/Event
  - app/Http/Controllers/Admin/Dashboards/FeeDashboardController.php: admin/Dashboards/Fee
  - app/Http/Controllers/Admin/Dashboards/LiveAnalyticsDashboardController.php: admin/Dashboards/LiveAnalytics
  - app/Http/Controllers/Admin/Dashboards/ModerationDashboardController.php: admin/Dashboards/Moderation
  - app/Http/Controllers/Admin/Dashboards/UserDashboardController.php: admin/Dashboards/Dashboard
  - app/Http/Controllers/Admin/Dashboards/WalletDashboardController.php: admin/Dashboards/Wallet
  - app/Http/Controllers/Admin/EmailAdsManagerController.php: admin/ads/EmailAdsManager
  - app/Http/Controllers/Admin/EmailController.php: admin/email/Index
  - app/Http/Controllers/Admin/EmailController.php: admin/email/Index
  - app/Http/Controllers/Admin/EmailController.php: admin/email/Index
  - app/Http/Controllers/Admin/EmailController.php: admin/email/Index
  - app/Http/Controllers/Admin/EventCategoryController.php: admin/categories/Index
  - app/Http/Controllers/Admin/EventController.php: admin/events/Index
  - app/Http/Controllers/Admin/EventController.php: admin/events/Categories
  - app/Http/Controllers/Admin/EventController.php: admin/events/EventFee/Index
  - app/Http/Controllers/Admin/EventOrganizerController.php: admin/organizer/Index
  - app/Http/Controllers/Admin/EventOrganizerController.php: admin/organizer/CreateEdit
  - app/Http/Controllers/Admin/EventOrganizerController.php: admin/organizer/ViewOrganizer
  - app/Http/Controllers/Admin/EventOrganizerController.php: admin/organizer/Edit
  - app/Http/Controllers/Admin/e_wallet/DashboardController.php: admin/e_wallet/Dashboard
  - app/Http/Controllers/Admin/e_wallet/DashboardController.php: admin/e_wallet/WalletSetting
  - app/Http/Controllers/Admin/e_wallet/DigiMoneyController.php: admin/e_wallet/Digimoney
  - app/Http/Controllers/Admin/e_wallet/MoneyRequestController.php: admin/e_wallet/moneyRequest
  - app/Http/Controllers/Admin/e_wallet/PaymentGatewayController.php: admin/e_wallet/CreatePaymentWay
  - app/Http/Controllers/Admin/e_wallet/PaymentGatewayController.php: admin/e_wallet/UserWalletKyc
  - app/Http/Controllers/Admin/e_wallet/TransactionController.php: admin/e_wallet/Transaction
  - app/Http/Controllers/Admin/e_wallet/WithdrawRequestController.php: admin/e_wallet/WithdrawRequest
  - app/Http/Controllers/Admin/InvoiceController.php: admin/invoices/Index
  - app/Http/Controllers/Admin/InvoiceController.php: admin/invoices/Create
  - app/Http/Controllers/Admin/KycUserController.php: admin/kycusers/Users
  - app/Http/Controllers/Admin/KycUserController.php: admin/kycusers/Organizers
  - app/Http/Controllers/Admin/KycUserController.php: admin/kycusers/Organizer
  - app/Http/Controllers/Admin/LiveStream/CallController.php: admin/liveStream/calls/Index
  - app/Http/Controllers/Admin/LiveStream/EncounterController.php: admin/liveStream/encounters/Index
  - app/Http/Controllers/Admin/LiveStream/GiftController.php: admin/liveStream/gifts/Index
  - app/Http/Controllers/Admin/LiveStream/LinkupLiveGiftController.php: admin/liveStream/linkUpLiveGift/Index
  - app/Http/Controllers/Admin/LiveStream/LinkupLiveGiftController.php: admin/liveStream/linkUpLiveGift/Create
  - app/Http/Controllers/Admin/LiveStream/LinkupLiveGiftController.php: admin/liveStream/linkUpLiveGift/Edit
  - app/Http/Controllers/Admin/LiveStream/LiveStreamController.php: admin/liveStream/liveStreams/Index
  - app/Http/Controllers/Admin/LiveStream/LiveStreamController.php: admin/liveStream/dashboard/Dashboard
  - app/Http/Controllers/Admin/LiveStream/LiveStreamController.php: admin/liveStream/feeAnalytics
  - app/Http/Controllers/Admin/LiveStream/LiveStreamController.php: admin/liveStream/liveAnalytics
  - app/Http/Controllers/Admin/LiveStream/LiveStreamController.php: admin/liveStream/moderation
  - app/Http/Controllers/Admin/LiveStream/LiveStreamController.php: admin/liveStream/categories/Index
  - app/Http/Controllers/Admin/LiveStream/MessageController.php: admin/liveStream/messages/Index
  - app/Http/Controllers/Admin/LiveStream/PaymentController.php: admin/liveStream/payments/Index
  - app/Http/Controllers/Admin/LiveStream/PayoutController.php: admin/liveStream/payouts/Index
  - app/Http/Controllers/Admin/NewsController.php: admin/news/Index
  - app/Http/Controllers/Admin/NewsController.php: admin/news/CreateEdit
  - app/Http/Controllers/Admin/NewsController.php: admin/news/NewsView
  - app/Http/Controllers/Admin/NewsController.php: admin/news/CreateEdit
  - app/Http/Controllers/Admin/PushNotificationController.php: admin/pushNotification/Create
  - app/Http/Controllers/Admin/Reports/TicketsReportController.php: admin/Reports/Tickets/Index
  - app/Http/Controllers/Admin/Reports/WalletReportController.php: admin/Reports/Wallet/Index
  - app/Http/Controllers/Admin/RestaurantController.php: admin/restaurant/Index
  - app/Http/Controllers/Admin/RestaurantController.php: admin/restaurant/Create
  - app/Http/Controllers/Admin/RestaurantController.php: admin/restaurant/Show
  - app/Http/Controllers/Admin/RestaurantController.php: admin/restaurant/Edit
  - app/Http/Controllers/Admin/RoleController.php: admin/roles/Index
  - app/Http/Controllers/Admin/RoleController.php: admin/roles/Create
  - app/Http/Controllers/Admin/RoleController.php: admin/roles/Edit
  - app/Http/Controllers/Admin/ScannerController.php: admin/scanners/Index
  - app/Http/Controllers/Admin/ScannerController.php: admin/scanners/CreateEdit
  - app/Http/Controllers/Admin/ScannerController.php: admin/scanners/CreateEdit
  - app/Http/Controllers/Admin/SellerCashOutController.php: admin/SellerCashOut/Index
  - app/Http/Controllers/Admin/Settings/AdsSettingController.php: admin/settings/adsSetting
  - app/Http/Controllers/Admin/Settings/PasswordController.php: admin/settings/Password
  - app/Http/Controllers/Admin/Settings/ProfileController.php: admin/settings/Profile
  - app/Http/Controllers/Admin/Settings/SmtpSettingController.php: admin/settings/smtpSetting
  - app/Http/Controllers/Admin/Shops/AdminGiftPurchases.php: admin/shops/purchaseGifts/Index
  - app/Http/Controllers/Admin/Shops/DashboardController.php: admin/shops/dashboard/Index
  - app/Http/Controllers/Admin/Shops/DashboardController.php: admin/shops/fee/Index
  - app/Http/Controllers/Admin/Shops/MerchantController.php: admin/shops/merchants/Index
  - app/Http/Controllers/Admin/Shops/OrderController.php: admin/shops/order/Index
  - app/Http/Controllers/Admin/Shops/OrderController.php: admin/shops/order/View
  - app/Http/Controllers/Admin/Shops/OrderController.php: admin/shops/escrow/EscrowReleases
  - app/Http/Controllers/Admin/Shops/OrderController.php: admin/shops/payouts/SellerTransfers
  - app/Http/Controllers/Admin/Shops/ProductCategoryController.php: admin/shops/categories/Index
  - app/Http/Controllers/Admin/Shops/ProductController.php: admin/shops/products/Index
  - app/Http/Controllers/Admin/Shops/ProductController.php: admin/shops/products/Create
  - app/Http/Controllers/Admin/Shops/ProductController.php: admin/shops/products/Edit
  - app/Http/Controllers/Admin/Shops/ProductController.php: admin/shops/products/View
  - app/Http/Controllers/Admin/Shops/WalletController.php: admin/shops/wallets/Index
  - app/Http/Controllers/Admin/SponsorController.php: admin/sponsors/Index
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/Dashboard
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/UsersPage
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/GoLive
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/ClubAndRestaurant
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/Marketplace
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/Events
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/Lab
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/SubscriptionsPage
  - app/Http/Controllers/Admin/Subscriptions/UserPlanController.php: admin/subscriptions/RevenuePage
  - app/Http/Controllers/Admin/SwipeController.php: admin/swipes/SwipesPage
  - app/Http/Controllers/Admin/SwipeController.php: admin/swipes/Create
  - app/Http/Controllers/Admin/SwipeController.php: admin/swipes/Edit
  - app/Http/Controllers/Admin/TaxesController.php: admin/taxes/Dashboard
  - app/Http/Controllers/Admin/TaxesController.php: admin/taxes/Index
  - app/Http/Controllers/Admin/TaxesController.php: admin/taxes/Show
  - app/Http/Controllers/Admin/TaxesController.php: admin/taxes/TaxSetting
  - app/Http/Controllers/Admin/TaxesController.php: admin/taxes/TaxRemittanceSetting
  - app/Http/Controllers/Admin/TicketController.php: admin/events/tickets/Index
  - app/Http/Controllers/Admin/TicketController.php: admin/events/tickets/Show
  - app/Http/Controllers/Admin/TicketSaleController.php: admin/events/ticketSale/Index
  - app/Http/Controllers/Admin/TicketSaleController.php: admin/events/ticketSale/Index
  - app/Http/Controllers/Admin/UserReportsController.php: admin/reportedusers/Index
  - app/Http/Controllers/Admin/UsersController.php: admin/users/Index
  - app/Http/Controllers/Admin/UsersController.php: admin/users/Create
  - app/Http/Controllers/Admin/UsersController.php: admin/users/view/Index
  - app/Http/Controllers/Admin/UsersController.php: admin/users/Edit
  - app/Http/Controllers/Auth/ConfirmablePasswordController.php: Auth/ConfirmPassword
  - app/Http/Controllers/Auth/EmailVerificationPromptController.php: Auth/VerifyEmail
  - app/Http/Controllers/Frontend/EventTicketController.php: User/Ticket/TicketDetails
  - app/Http/Controllers/Frontend/MyEventController.php: User/MyEvent/Show
  - app/Http/Controllers/Organizer/EventController.php: organizer/coupon/Show
  - app/Http/Controllers/Organizer/EventController.php: organizer/sponsor/Show
  - routes/admin/settings.php: admin/settings/Appearance

## Dynamic names to review by hand

- resources/js/components/User/cards/FeatureCard.vue:2  <Link :href="route('user.' + dest)"
- resources/js/components/User/SearchOverlay.vue:132  try { return route('user.' + dest); } catch { return '/' + dest; }
- resources/js/pages/User/Home/Index.vue:52  <Link v-for="q in quickLinks" :key="q.dest" :href="route('user.' + q.dest)" class="card hover:-translate-y-0.5 transition p-3 flex flex-col 
- WARP.md:53  **Member app (`User`):** one folder per area. Pages live in `resources/js/pages/User/<Feature>/` (`Home`, `Linkup`, `Wallet`, `Events`, `Liv

## Still mentions new_front / NewFrontend after the rewrite (4)
Comments, labels or things the rules deliberately do not touch:

- .artifacts/20260818-110606-203a2f92-8979-4439-9275-7199f9102c7f/implementation_plan.artifact.md:18  ### [Frontend] [Index.vue](file:///D:/atif projects/LinkupFinal/resources/js/pages/new_front/events/Index.vue)
- .artifacts/20260818-110606-203a2f92-8979-4439-9275-7199f9102c7f/walkthrough.artifact.md:8  - **New Frontend Index**: Replaced the simplified creation modal with the full form in `resources/js/pages/new_front/events/Index.vue`.
- .artifacts/20260818-110606-203a2f92-8979-4439-9275-7199f9102c7f/walkthrough.artifact.md:55  - **Profile Detail Trigger**: Added a modern eye icon button with a backdrop blur and semi-transparent border on the right side of the LinkU
- app/Http/Controllers/User/TicketPurchaseController.php:1055  return "new_frontend_stripe_cart:{$cartToken}";

## New layout of resources/js/pages/User

```
Caribbean360/Index.vue
Eats/Classic
Eats/Index.vue
Events/Bookings
Events/Classic
Events/Index.vue
Events/MyEvents
Events/Tickets
Events/UpcomingEvent.vue
Home/Classic
Home/Index.vue
Kyc/VerifyKyc.vue
Landing/Classic
Landing/Index.vue
Linkup/Chats.vue
Linkup/Classic
Linkup/Find.vue
Linkup/Friends.vue
Linkup/Index.vue
Linkup/Likes.vue
Linkup/Matches.vue
Linkup/Requests.vue
Live/Classic
Live/Index.vue
Live/Watch.vue
Live/components
Marketplace/Classic
Marketplace/Index.vue
NightLife/Clubs
NightLife/Index.vue
Notifications/Classic
Notifications/Index.vue
Onboarding/Wizard.vue
Profile/Account
Profile/Classic
Profile/Index.vue
Profile/modals
Settings/AppSetting.vue
Settings/Index.vue
UVibe/Index.vue
Vibes/Index.vue
Vibes/UserProfile.vue
Wallet/Classic
Wallet/Index.vue
Wallet/components
Wallet/modals
```

Full old -> new list: restructure-moves.csv
