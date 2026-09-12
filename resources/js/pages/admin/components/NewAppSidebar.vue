<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Activity,
    Archive,
    Badge,
    BadgeDollarSign,
    BadgePercent,
    Banknote,
    BarChart3,
    Bell,
    Bike,
    Bitcoin,
    Bot,
    Boxes,
    CalendarDays,
    CalendarPlus,
    ChevronDown,
    ChevronRight,
    ClipboardList,
    Code,
    Coins,
    FileBarChart,
    FileText,
    Flag,
    GalleryHorizontal,
    Gem,
    Globe2,
    Headphones,
    HeartHandshake,
    HeartPulse,
    IdCard,
    Image,
    KeyRound,
    Landmark,
    LayoutDashboard,
    LayoutGrid,
    LayoutPanelLeft,
    LockKeyhole,
    Mail,
    MailCheck,
    MapPin,
    Megaphone,
    MenuSquare,
    MessageSquareHeart,
    MousePointerClick,
    Music,
    Newspaper,
    PackagePlus,
    PiggyBank,
    QrCode,
    Radio,
    Receipt,
    ReceiptText,
    ScrollText,
    Send,
    SendHorizontal,
    Server,
    Settings,
    Shield,
    ShieldAlert,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    SlidersHorizontal,
    Smartphone,
    Star,
    Store,
    Table,
    Tags,
    TerminalSquare,
    Ticket,
    TicketX,
    ToggleLeft,
    TrendingUp,
    Trophy,
    UserCog,
    UserPlus,
    Users,
    Utensils,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    activeId: string;
}>();

const emit = defineEmits(['view-changed']);

// Icon mapping to Lucide Vue components
const iconMap: Record<string, any> = {
    'layout-dashboard': LayoutDashboard,
    gem: Gem,
    boxes: Boxes,
    utensils: Utensils,
    store: Store,
    'menu-square': MenuSquare,
    'shopping-bag': ShoppingBag,
    bike: Bike,
    banknote: Banknote,
    receipt: Receipt,
    'badge-percent': BadgePercent,
    'map-pin': MapPin,
    'sliders-horizontal': SlidersHorizontal,
    headphones: Headphones,
    star: Star,
    'bar-chart-3': BarChart3,
    'globe-2': Globe2,
    users: Users,
    flag: Flag,
    'id-card': IdCard,
    coins: Coins,
    send: Send,
    'receipt-text': ReceiptText,
    'piggy-bank': PiggyBank,
    'shield-check': ShieldCheck,
    landmark: Landmark,
    activity: Activity,
    bot: Bot,
    'trending-up': TrendingUp,
    bell: Bell,
    'layout-panel-left': LayoutPanelLeft,
    mail: Mail,
    'message-square-heart': MessageSquareHeart,
    newspaper: Newspaper,
    'mouse-pointer-click': MousePointerClick,
    'package-plus': PackagePlus,
    tags: Tags,
    'lock-keyhole': LockKeyhole,
    'file-text': FileText,
    image: Image,
    smartphone: Smartphone,
    code: Code,
    'shield-alert': ShieldAlert,
    shield: Shield,
    'toggle-left': ToggleLeft,
    server: Server,
    'user-cog': UserCog,
    settings: Settings,
    'chevron-down': ChevronDown,
    'chevron-right': ChevronRight,
    ticket: Ticket,
    'calendar-days': CalendarDays,
    'calendar-plus': CalendarPlus,
    trophy: Trophy,
    'heart-pulse': HeartPulse,
    'qr-code': QrCode,
    'user-plus': UserPlus,
    'send-horizontal': SendHorizontal,
    archive: Archive,
    bitcoin: Bitcoin,
    'clipboard-list': ClipboardList,
    'terminal-square': TerminalSquare,
    'key-round': KeyRound,
    table: Table,
    badge: Badge,
    'scroll-text': ScrollText,
    'heart-handshake': HeartHandshake,
    layers: LayoutDashboard, // fallback
    tag: Tags,
    list: Boxes,
    'scan-line': Boxes,
    'calendar-star': CalendarDays,
    'bell-ring': Bell,
    'plus-square': PackagePlus,
    plug: Server,
    'wallet-cards': Banknote,
    'badge-dollar-sign': BadgeDollarSign,
    'file-bar-chart': FileBarChart,
    'gallery-horizontal': GalleryHorizontal,
    music: Music,
    megaphone: Megaphone,
    wallet: Wallet,
    radio: Radio,
    'ticket-x': TicketX,
    'shopping-cart': ShoppingCart,
    'layout-grid': LayoutGrid,
    'mail-check': MailCheck,
};

const currentRole = ref(localStorage.getItem('linkupRole') || 'Super Admin');
const activeSidebarId = ref(props.activeId || 'executive');

// Menu open states
const menuStates = ref<Record<string, boolean>>({
    event: localStorage.getItem('eventMenuOpen') === 'true',
    organizer: localStorage.getItem('organizerMenuOpen') === 'true',
    wallet: localStorage.getItem('walletMenuOpen') === 'true',
    marketplace: localStorage.getItem('marketplaceMenuOpen') === 'true',
    merchant: localStorage.getItem('merchantMenuOpen') === 'true',
    eats: localStorage.getItem('eatsMenuOpen') === 'true',
    ad: localStorage.getItem('adMenuOpen') === 'true',
    email: localStorage.getItem('emailMenuOpen') === 'true',
    tax: localStorage.getItem('taxMenuOpen') === 'true',
    news: localStorage.getItem('newsMenuOpen') === 'true',
    admin: localStorage.getItem('adminMenuOpen') === 'true',
    admins: localStorage.getItem('adminsMenuOpen') === 'true',
    settings: localStorage.getItem('settingsMenuOpen') === 'true',
    gov: localStorage.getItem('govMenuOpen') === 'true',
    feed: localStorage.getItem('feedMenuOpen') === 'true',
    fees: localStorage.getItem('feesMenuOpen') === 'true',
});

// Category open states
const catStates = ref<Record<string, boolean>>({
    overview: localStorage.getItem('cat_overview') === 'true' || true,
    events: localStorage.getItem('cat_events') === 'true' || false,
    commerce: localStorage.getItem('cat_commerce') === 'true' || false,
    finance: localStorage.getItem('cat_finance') === 'true' || false,
    government: localStorage.getItem('cat_government') === 'true' || false,
    marketing: localStorage.getItem('cat_marketing') === 'true' || false,
    trust: localStorage.getItem('cat_trust') === 'true' || false,
    admin: localStorage.getItem('cat_admin') === 'true' || false,
});

const roleAccess: Record<string, string[]> = {
    'Super Admin': ['overview', 'events', 'commerce', 'finance', 'marketing', 'trust', 'admin', 'government'],
    Finance: ['overview', 'finance', 'admin'],
    Marketing: ['overview', 'marketing'],
    'Commerce Ops': ['overview', 'commerce', 'events'],
    'Trust & Safety': ['overview', 'trust', 'admin'],
    'News Manager': ['overview', 'marketing'],
    'Gov Agency — NIB Bahamas': ['government'],
    'Gov Agency — NIS Jamaica': ['government'],
    'Gov Agency — Supérate (DR)': ['government'],
};

const setAdminRole = (role: string) => {
    currentRole.value = role;
    localStorage.setItem('linkupRole', role);
};

const toggleMenu = (key: string) => {
    menuStates.value[key] = !menuStates.value[key];
    localStorage.setItem(`${key}MenuOpen`, String(menuStates.value[key]));
};

const toggleCat = (key: string) => {
    catStates.value[key] = !catStates.value[key];
    localStorage.setItem(`cat_${key}`, String(catStates.value[key]));
};

const showView = (id: string) => {
    const settingsViews = [
        'logoCmsView', 'legalCmsView', 'driverPoliciesView', 'settingsAppCommand',
        'settingsAdsCommand', 'settingsSmtpCommand', 'settingsAICommand',
        'settingsWalletFundingCommand', 'settingsFeatureFlagsCommand', 'settingsSystemCommand'
    ];

    if (settingsViews.includes(id)) {
        if (route().current() !== 'admin.settings-dashboard') {
            router.visit(route('admin.settings-dashboard', { view: id }));
        } else {
            activeSidebarId.value = id;
            emit('view-changed', id);
        }
        return;
    }

    if (route().current() !== 'admin.e-wallet-dashboard') {
        localStorage.setItem('ewallet_active_view', id);
        router.visit(route('admin.e-wallet-dashboard'));
    } else {
        activeSidebarId.value = id;
        emit('view-changed', id);
    }
};

watch(
    () => props.activeId,
    (newId) => {
        if (newId) activeSidebarId.value = newId;
    },
);

// Pages data
const adManagementPages = [
    ['adDashboardCommand', 'layout-dashboard', 'Dashboard'],
    ['adAnalyticsCommand', 'bar-chart-3', 'Analytics'],
    ['adReportsCommand', 'file-bar-chart', 'Reports'],
    ['adAllAdsCommand', 'gallery-horizontal', 'All Ads'],
    ['adRestaurantsCommand', 'utensils', 'Restaurants'],
    ['adClubsFetesCommand', 'music', 'Clubs & Fetes'],
    ['adCampaignsCommand', 'megaphone', 'Campaigns'],
    ['adEmailSponsorCommand', 'mail', 'Email Sponsor'],
    ['adC360NewsCommand', 'newspaper', 'C360 News'],
    ['adWalletAdsCommand', 'wallet', 'Wallet Ads'],
    ['adMarketplaceAdsCommand', 'shopping-bag', 'Marketplace Ads'],
    ['adSponsoredEventsCommand', 'calendar-star', 'Sponsored Events'],
    ['adLiveStreamAdsCommand', 'radio', 'Live Stream Ads'],
    ['adPushNotificationAdsCommand', 'bell-ring', 'Push Notification Ads'],
    ['feedAdsCommand', 'image', 'Vibes Ads'],
    ['organizerPromoteCommand', 'megaphone', 'Promote & Advertise'],
    ['adPricingCommand', 'tag', 'Ad Pricing'],
];

const eventManagementPages = [
    ['admin.events-list', 'calendar-days', 'Events', 'eventMgmtCommand'],
    ['admin.event-categories', 'tags', 'Categories', 'eventCategoriesCommand'],
    ['admin.ticket-sales', 'ticket', 'Ticket Sale', 'ticketSalesCommand'],
    ['admin.event-sponsors', 'badge-dollar-sign', 'Sponsors', 'eventSponsorsCommand'],
    ['admin.event-coupons', 'badge-percent', 'Coupons', 'eventCouponsCommand'],
    ['admin.tickets-report', 'clipboard-list', 'Tickets Report', 'ticketReportCommand'],
    ['admin.cancel-tickets', 'ticket-x', 'Cancel Tickets', 'cancelTicketsCommand'],
];

const organizerPages = [
    ['admin.organizer-directory', 'users', 'Organizer Directory', 'organizerDirectoryCommand'],
    ['admin.promote-events', 'megaphone', 'Promote Events', 'organizerPromoteCommand'],
    ['admin.scanners-management', 'scan-line', 'Scanners', 'organizerScannersCommand'],
    ['admin.payout-list', 'banknote', 'Payout List', 'organizerPayoutListCommand'],
];

const marketplacePages = [
    ['admin.commerce.marketplace.dashboard', 'layout-dashboard', 'Dashboard', 'marketplaceDashboardCommand'],
    ['admin.commerce.marketplace.products', 'package-plus', 'Products', 'marketplaceProductsCommand'],
    ['admin.commerce.marketplace.categories', 'tags', 'Categories', 'marketplaceCategoriesCommand'],
    ['admin.commerce.marketplace.fees', 'receipt', 'Fees', 'marketplaceFeesCommand'],
    ['admin.commerce.marketplace.orders', 'shopping-cart', 'Orders', 'marketplaceOrdersCommand'],
    ['admin.commerce.marketplace.escrow', 'lock-keyhole', 'Escrow & Releases', 'marketplaceEscrowCommand'],
    ['admin.commerce.marketplace.transfers', 'send', 'Seller Transfers', 'marketplaceSellerTransfersCommand'],
    ['admin.commerce.marketplace.wallets', 'wallet', 'Wallets', 'marketplaceWalletsCommand'],
    ['admin.commerce.marketplace.sellers', 'store', 'Sellers / Store Groups', 'marketplaceSellersCommand'],
];

const walletPages = [
    ['walletCommand', 'wallet', 'Mission Control'],
    ['walletTransactionsCommand', 'list', 'Transactions Ledger'],
    ['walletUsersCommand', 'users', 'Wallet Users'],
    ['walletBalancesCommand', 'landmark', 'Wallet Balances'],
    ['walletSettlementCenterCommand', 'receipt', 'Settlement Center'],
    ['asuDrawerCommand', 'archive', 'ASUE Drawer Data'],
    ['billGatewayCommand', 'receipt-text', 'Bill Gateway'],
    ['walletPayoutQueueCommand', 'banknote', 'Payout Queue'],
    ['coins', 'coins', 'Coins'],
    ['savingsCommand', 'piggy-bank', 'LinkUp Save'],
    ['cryptoCommand', 'bitcoin', 'Cryptocurrency'],
];

const trustPages = [
    ['reviewsConcernsCommand', 'message-square-heart', 'Reviews & Concerns'],
    ['kycCommand', 'id-card', 'KYC Review'],
    ['flaggedUsersCommand', 'flag', 'Flagged Users'],
    ['fraud', 'shield-alert', 'Fraud Center'],
    ['controlCommand', 'shield-check', 'Controls'],
    ['ops', 'activity', 'Operations'],
    ['partner', 'landmark', 'Partner Portal'],
];

const eatsPages = [
    ['admin.commerce.eats.dashboard', 'layout-dashboard', 'Dashboard', 'eatsCommand'],
    ['admin.commerce.eats.live-app', 'smartphone', 'Eats App (Live)', 'eatsAppCommand'],
    ['admin.commerce.eats.restaurants', 'store', 'Restaurants', 'eatsRestaurantsCommand'],
    ['admin.commerce.eats.menus', 'menu-square', 'Menus', 'eatsMenusCommand'],
    ['admin.commerce.eats.orders', 'shopping-bag', 'Orders', 'eatsOrdersCommand'],
    ['admin.commerce.eats.drivers', 'bike', 'Drivers', 'eatsDriversCommand'],
    ['admin.commerce.eats.driver-payouts', 'banknote', 'Driver Payouts', 'eatsDriverPayoutsCommand'],
    ['admin.commerce.eats.restaurant-payouts', 'receipt', 'Restaurant Payouts', 'eatsRestaurantPayoutsCommand'],
    ['admin.commerce.eats.promotions', 'badge-percent', 'Promotions', 'eatsPromotionsCommand'],
    ['admin.commerce.eats.delivery-zones', 'map-pin', 'Delivery Zones', 'eatsDeliveryZonesCommand'],
    ['admin.commerce.eats.fees-pricing', 'sliders-horizontal', 'Fees & Pricing', 'eatsFeesPricingCommand'],
    ['admin.commerce.eats.support', 'headphones', 'Customer Support', 'eatsSupportCommand'],
    ['admin.commerce.eats.reviews', 'star', 'Reviews & Ratings', 'eatsReviewsCommand'],
    ['admin.commerce.eats.analytics', 'bar-chart-3', 'Analytics', 'eatsAnalyticsCommand'],
];
const emailPages = [
    ['sendEmailCommand', 'send', 'Send Emails'],
    ['emailTemplatesCommand', 'file-text', 'Email Templates'],
];
const newsPages = [
    ['newsFeedCommand', 'layout-grid', 'News Feed (Reader)'],
    ['newsDashboardCommand', 'layout-dashboard', 'News Dashboard'],
    ['newsListCommand', 'newspaper', 'News List'],
    ['newsCreateCommand', 'plus-square', 'Create Story'],
    ['newsCategoriesCommand', 'tags', 'Categories'],
    ['newsRegionsCommand', 'globe-2', 'Regions'],
    ['newsSponsoredCommand', 'badge-dollar-sign', 'Sponsored News'],
    ['newsAuthorsCommand', 'users', 'Authors'],
];
const taxPages = [
    ['admin.finance.taxes.management', 'receipt', 'Tax Management', 'taxManagementCommand'],
    ['admin.finance.taxes.dashboard', 'layout-dashboard', 'Dashboard', 'taxDashboardCommand'],
    ['admin.finance.taxes.authority-apis', 'plug', 'Tax Authority APIs', 'taxAuthorityCommand'],
    ['admin.finance.taxes.corporate-tax', 'landmark', 'LinkUp Corporate Tax', 'linkupCorpTaxCommand'],
    ['admin.finance.taxes.settings', 'sliders-horizontal', 'Tax Settings', 'taxSettingsCommand'],
    ['admin.finance.taxes.remittance-settings', 'settings', 'Tax Remittance Settings', 'taxRemittanceSettingsCommand'],
    ['admin.finance.taxes.remittance-center', 'landmark', 'Tax Remittance Center', 'taxRemittanceCenterCommand'],
];
const adminPages = [
    ['adminRolesCommand', 'shield-check', 'Roles & Permissions'],
    ['adminUsersCommand', 'users', 'Admin Users'],
    ['adminAccessMatrixCommand', 'table', 'Access Matrix'],
    ['settingsSecurityCommand', 'shield', 'Security & Access'],
    ['scotiaAccessCommand', 'key-round', 'Partner Access (Scotiabank)'],
    ['adminAuditCommand', 'clipboard-list', 'Admin Audit Trail'],
];
const settingsPages = [
    ['logoCmsView', 'badge', 'App Logo'],
    ['legalCmsView', 'scroll-text', 'Legal & Pages'],
    ['admin.commerce.driver-policies', 'shield-check', 'Driver Policies', 'driverPoliciesView'],
    ['settingsAppCommand', 'settings', 'App Settings'],
    ['settingsAdsCommand', 'badge-dollar-sign', 'Ads Settings'],
    ['settingsSmtpCommand', 'mail-check', 'SMTP Settings'],
    ['settingsAICommand', 'bot', 'AI Settings'],
    ['settingsWalletFundingCommand', 'banknote', 'Wallet Funding API'],
    ['settingsFeatureFlagsCommand', 'toggle-left', 'Feature Flags'],
    ['settingsSystemCommand', 'server', 'System Settings'],
];
const feesPages = [
    ['feesCenterCommand', 'sliders-horizontal', 'Fees Center'],
    ['scotiaPortal', 'landmark', 'Partner Portal'],
    ['eventFeeCommand', 'calendar-days', 'Event Fees'],
    ['eatsFeesPricingCommand', 'utensils', 'Eats Fees & Pricing'],
    ['marketplaceFeesCommand', 'shopping-bag', 'Marketplace Fees'],
    ['pricingCommand', 'tag', 'Pricing & Plans'],
];
const merchantPages = [
    ['admin.commerce.merchants.dashboard', 'layout-dashboard', 'Dashboard', 'merchants'],
    ['admin.commerce.merchants.directory', 'list', 'Directory', 'mxDirectory'],
    ['admin.commerce.merchants.onboarding', 'user-plus', 'Onboarding', 'mxOnboarding'],
    ['admin.commerce.merchants.kyc', 'shield-check', 'KYC & Verification', 'mxKyc'],
    ['admin.commerce.merchants.tax', 'receipt', 'Tax Reports', 'mxTax'],
];
const govPages = [
    ['govDashboardCommand', 'landmark', 'Gov Dashboard'],
    ['govBeneficiariesCommand', 'users', 'Beneficiary Registry'],
    ['govDisbursementsCommand', 'send', 'Disbursements'],
    ['govSettingsCommand', 'sliders-horizontal', 'Fees & Settings'],
];
const feedPages = [
    ['feedDashboardCommand', 'image', 'Feed Dashboard'],
    ['feedPreviewCommand', 'smartphone', 'App Preview'],
    ['feedCreatorsCommand', 'users', 'Creators'],
    ['feedAdsCommand', 'megaphone', 'Vibes Ads'],
    ['feedEngineCommand', 'sliders-horizontal', 'Feed Engine'],
    ['feedModerationCommand', 'shield-alert', 'Moderation'],
    ['feedDevCommand', 'code', 'Developer Export'],
];

const isAllowed = (key: string) => {
    const allowed = roleAccess[currentRole.value] || ['overview', 'events', 'commerce', 'finance', 'marketing', 'trust', 'admin'];
    return allowed.includes(key);
};

const isActive = (id: string) => activeSidebarId.value === id;

const eventActive = computed(() => eventManagementPages.some((p) => p[0] === activeSidebarId.value || p[3] === activeSidebarId.value));
const organizerActive = computed(() => organizerPages.some((p) => p[0] === activeSidebarId.value || p[3] === activeSidebarId.value));
const walletActive = computed(() => walletPages.some((p) => p[0] === activeSidebarId.value));
const marketplaceActive = computed(() => marketplacePages.some((p) => p[0] === activeSidebarId.value || p[3] === activeSidebarId.value));
const merchantActive = computed(() => merchantPages.some((p) => p[0] === activeSidebarId.value || p[3] === activeSidebarId.value));
const eatsActive = computed(() => eatsPages.some((p) => p[0] === activeSidebarId.value || p[3] === activeSidebarId.value));
const adActive = computed(() => adManagementPages.some((p) => p[0] === activeSidebarId.value));
const emailActive = computed(() => emailPages.some((p) => p[0] === activeSidebarId.value));
const taxActive = computed(() => taxPages.some((p) => p[0] === activeSidebarId.value || p[3] === activeSidebarId.value));
const newsActive = computed(() => newsPages.some((p) => p[0] === activeSidebarId.value));
const trustActive = computed(() => trustPages.some((p) => p[0] === activeSidebarId.value));
const adminActive = computed(() => adminPages.some((p) => p[0] === activeSidebarId.value));
const settingsActive = computed(() => settingsPages.some((p) => p[0] === activeSidebarId.value));
const govActive = computed(() => govPages.some((p) => p[0] === activeSidebarId.value));
const feedActive = computed(() => feedPages.some((p) => p[0] === activeSidebarId.value));
const feesActive = computed(() => feesPages.some((p) => p[0] === activeSidebarId.value));

const eventsCatActive = computed(
    () => eventActive.value || organizerActive.value || ['liveCommand', 'topEarnersView', 'wellnessView'].includes(activeSidebarId.value),
);
const commerceCatActive = computed(
    () =>
        eatsActive.value ||
        marketplaceActive.value ||
        feedActive.value ||
        [
            'merchants',
            'swipesCommand',
            'subscriptionSuiteCommand',
            'dispatchCommand',
            'shippingCommand',
            'driverKycView',
            'eatsAdminView',
            'restaurantOnboardingView',
            'merchantPayView',
        ].includes(activeSidebarId.value),
);
const financeCatActive = computed(
    () =>
        walletActive.value ||
        taxActive.value ||
        govActive.value ||
        [
            'coins',
            'admin.remittance-dashboard',
            'admin.settlements-dashboard',
            'admin.payout-ops-dashboard',
            'admin.payroll-dashboard',
            'admin.foundation-dashboard',
            'remittanceCommand',
            'settlementCommand',
            'payoutCommand',
            'payrollCommand',
            'savingsCommand',
            'pricingCommand',
            'foundationCommand',
            'scotiaPortal',
            'scotiaLogin',
        ].includes(activeSidebarId.value),
);
const marketingCatActive = computed(() => adActive.value || emailActive.value || newsActive.value || activeSidebarId.value === 'notifications');
const trustCatActive = computed(() => trustActive.value);
const adminCatActive = computed(() => adminActive.value || settingsActive.value || feesActive.value);
</script>

<template>
    <aside id="appSidebar" class="sticky top-0 z-50 flex h-screen w-72 shrink-0 flex-col border-r border-slate-200 bg-white/95">
        <div class="border-b border-slate-100 p-6">
            <div class="flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-950 text-2xl font-black text-white">L</div>
                <div>
                    <h1 class="text-2xl font-black">LinkUp</h1>
                    <p class="text-xs text-slate-500">Global Operating System</p>
                </div>
            </div>
        </div>

        <nav class="scrollbar flex-1 space-y-1 overflow-y-auto p-3" id="sidebarNav">
            <!-- Role Switcher -->
            <div class="mb-1 px-3 pb-2">
                <p class="mb-1 text-[10px] font-black tracking-widest text-slate-400 uppercase">View As</p>
                <select
                    @change="setAdminRole(($event.target as HTMLSelectElement).value)"
                    class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-bold"
                >
                    <option v-for="role in Object.keys(roleAccess)" :key="role" :selected="role === currentRole">{{ role }}</option>
                </select>
            </div>

            <!-- Categories -->
            <div v-if="isAllowed('overview')" class="space-y-1">
                <button
                    @click="toggleCat('overview')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="activeSidebarId === 'executive' || activeSidebarId === 'feeRevenueCommand' ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><LayoutDashboard class="h-4 w-4" /> Overview</span>
                    <ChevronDown v-if="catStates.overview" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.overview" class="space-y-1 pb-1">
                    <Link
                        :href="route('admin.new-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('executive') }"
                        ><LayoutDashboard class="h-5 w-5" /> Dashboard</Link
                    >
                    <Link
                        :href="route('admin.fee-revenue-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('feeRevenueCommand') }"
                        ><TrendingUp class="h-5 w-5" /> Fee Revenue Dashboard</Link
                    >
                    <Link
                        :href="route('admin.users-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('users') }"
                        ><Users class="h-5 w-5" /> Users</Link
                    >
                    <Link
                        :href="route('admin.business-units-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('business') }"
                    >
                        <Boxes class="h-5 w-5" /> Business Units
                    </Link>
                    <Link
                        :href="route('admin.countries-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('countries') }"
                    >
                        <Globe2 class="h-5 w-5" /> Countries
                    </Link>
                    <Link
                        :href="route('admin.forecasting-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('forecast') }"
                    >
                        <TrendingUp class="h-5 w-5" /> Forecasting
                    </Link>
                    <Link
                        :href="route('admin.ask-ai-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('assistant') }"
                    >
                        <Bot class="h-5 w-5" /> Ask AI
                    </Link>
                </div>
            </div>

            <!-- Events Category -->
            <div v-if="isAllowed('events')" class="space-y-1">
                <button
                    @click="toggleCat('events')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="eventsCatActive ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><Ticket class="h-4 w-4" /> Events</span>
                    <ChevronDown v-if="catStates.events" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.events" class="space-y-1 pb-1">
                    <!-- Accordion: Event Management -->
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('event')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': eventActive }"
                        >
                            <span class="flex items-center gap-3"><CalendarDays class="h-5 w-5" /> Event Management</span>
                            <ChevronDown v-if="menuStates.event || eventActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.event || eventActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <Link
                                v-for="p in eventManagementPages"
                                :key="p[0]"
                                :href="route(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </Link>
                        </div>
                    </div>
                    <!-- Accordion: Event Organizer -->
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('organizer')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': organizerActive }"
                        >
                            <span class="flex items-center gap-3"><CalendarPlus class="h-5 w-5" /> Event Organizer</span>
                            <ChevronDown v-if="menuStates.organizer || organizerActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.organizer || organizerActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <Link
                                v-for="p in organizerPages"
                                :key="p[0]"
                                :href="route(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </Link>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.live-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('liveCommand') }"
                    >
                        <Radio class="h-5 w-5" /> LinkUp Live
                    </Link>
                    <Link
                        :href="route('admin.top-earners')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('topEarnersView') }"
                    >
                        <Trophy class="h-5 w-5" /> Top Coin Earners
                    </Link>
                    <Link
                        :href="route('admin.wellness-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('wellnessView') }"
                    >
                        <HeartPulse class="h-5 w-5" /> Wellness Providers
                    </Link>
                </div>
            </div>

            <!-- Commerce Category -->
            <div v-if="isAllowed('commerce')" class="space-y-1">
                <button
                    @click="toggleCat('commerce')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="commerceCatActive ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><ShoppingBag class="h-4 w-4" /> Commerce</span>
                    <ChevronDown v-if="catStates.commerce" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.commerce" class="space-y-1 pb-1">
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('eats')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': eatsActive }"
                        >
                            <span class="flex items-center gap-3"><Utensils class="h-5 w-5" /> LinkUp Eats</span>
                            <ChevronDown v-if="menuStates.eats || eatsActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.eats || eatsActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <Link
                                v-for="p in eatsPages"
                                :key="p[0]"
                                :href="route(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </Link>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('marketplace')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': marketplaceActive }"
                        >
                            <span class="flex items-center gap-3"><ShoppingBag class="h-5 w-5" /> Marketplace</span>
                            <ChevronDown v-if="menuStates.marketplace || marketplaceActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.marketplace || marketplaceActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <Link
                                v-for="p in marketplacePages"
                                :key="p[0]"
                                :href="route(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </Link>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.commerce.deliveries-dispatch')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('dispatchCommand') || isActive('admin.commerce.deliveries-dispatch') }"
                    >
                        <Bike class="h-5 w-5" /> Deliveries Dispatch
                    </Link>
                    <Link
                        :href="route('admin.commerce.driver-onboarding')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('driverKycView') || isActive('admin.commerce.driver-onboarding') }"
                    >
                        <IdCard class="h-5 w-5" /> Driver Onboarding
                    </Link>
                    <Link
                        :href="route('admin.commerce.shipping')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('shippingCommand') || isActive('admin.commerce.shipping') }"
                    >
                        <PackagePlus class="h-5 w-5" /> Shipping (USPS/FedEx/UPS)
                    </Link>
                    <Link
                        :href="route('admin.commerce.subscriptions')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('subscriptionSuiteCommand') || isActive('admin.commerce.subscriptions') }"
                    >
                        <BadgePercent class="h-5 w-5" /> Subscriptions
                    </Link>
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('merchant')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': merchantActive }"
                        >
                            <span class="flex items-center gap-3"><SlidersHorizontal class="h-5 w-5" /> Merchants</span>
                            <ChevronDown v-if="menuStates.merchant || merchantActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.merchant || merchantActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <Link
                                v-for="p in merchantPages"
                                :key="p[0]"
                                :href="route(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </Link>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.commerce.swipes')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('swipesCommand') || isActive('admin.commerce.swipes') }"
                    >
                        <MousePointerClick class="h-5 w-5" /> Swipes
                    </Link>
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('feed')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': feedActive }"
                        >
                            <span class="flex items-center gap-3"><Image class="h-5 w-5" /> LinkUp Vibes</span>
                            <ChevronDown v-if="menuStates.feed || feedActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.feed || feedActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <Link
                                v-for="p in feedPages"
                                :key="p[0]"
                                :href="route('admin.commerce.vibes-management', { tab: p[0] })"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finance Category -->
            <div v-if="isAllowed('finance')" class="space-y-1">
                <button
                    @click="toggleCat('finance')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="financeCatActive ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><Landmark class="h-4 w-4" /> Finance</span>
                    <ChevronDown v-if="catStates.finance || financeCatActive" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.finance || financeCatActive" class="space-y-1 pb-1">
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('wallet')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': walletActive }"
                        >
                            <span class="flex items-center gap-3"><SlidersHorizontal class="h-5 w-5" /> E-Wallet</span>
                            <ChevronDown v-if="menuStates.wallet || walletActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.wallet || walletActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in walletPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('tax')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': taxActive }"
                        >
                            <span class="flex items-center gap-3"><ReceiptText class="h-5 w-5" /> Taxes</span>
                            <ChevronDown v-if="menuStates.tax || taxActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.tax || taxActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <template v-for="p in taxPages" :key="p[0]">
                                <Link
                                    v-if="p[0].startsWith('admin.')"
                                    :href="route(p[0])"
                                    class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                    :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                                >
                                    <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                                </Link>
                                <button
                                    v-else
                                    @click="showView(p[0])"
                                    class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                    :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                                >
                                    <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                                </button>
                            </template>
                        </div>
                    </div>
                    <!-- More finance buttons with @click="showView" as per original -->
                    <Link
                        :href="route('admin.remittance-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('remittanceCommand') || route().current('admin.remittance-dashboard') }"
                    >
                        <Send class="h-5 w-5" /> Remittance
                    </Link>
                    <Link
                        :href="route('admin.settlements-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('settlementCommand') || route().current('admin.settlements-dashboard') }"
                    >
                        <Receipt class="h-5 w-5" /> Settlements
                    </Link>
                    <Link
                        :href="route('admin.payout-ops-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('payoutCommand') || route().current('admin.payout-ops-dashboard') }"
                    >
                        <Banknote class="h-5 w-5" /> Payout Ops
                    </Link>
                    <Link
                        :href="route('admin.payroll-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('payrollCommand') || route().current('admin.payroll-dashboard') }"
                    >
                        <Banknote class="h-5 w-5" /> Payroll
                    </Link>
                    <Link
                        :href="route('admin.pricing-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('pricingCommand') || route().current('admin.pricing-dashboard') }"
                    >
                        <SlidersHorizontal class="h-5 w-5" /> Pricing
                    </Link>
                    <Link
                        :href="route('admin.foundation-dashboard')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('foundationCommand') || route().current('admin.foundation-dashboard') }"
                    >
                        <HeartHandshake class="h-5 w-5" /> LinkUp Foundation
                    </Link>

                    <!-- Government sub-category under Finance -->
                    <div v-if="isAllowed('government')" class="space-y-1">
                        <button
                            @click="toggleMenu('gov')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': govActive }"
                        >
                            <span class="flex items-center gap-3"><Landmark class="h-5 w-5" /> Government Portal</span>
                            <ChevronDown v-if="menuStates.gov || govActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.gov || govActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in govPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>

                    <button
                        @click="showView('scotiaPortal')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('scotiaPortal') }"
                    >
                        <Landmark class="h-5 w-5" />Scotiabank Portal
                    </button>

                </div>
            </div>

            <!-- Marketing Category -->
            <div v-if="isAllowed('marketing')" class="space-y-1">
                <button
                    @click="toggleCat('marketing')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="marketingCatActive ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><TrendingUp class="h-4 w-4" /> Marketing & Media</span>
                    <ChevronDown v-if="catStates.marketing" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.marketing" class="space-y-1 pb-1">
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('ad')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': adActive }"
                        >
                            <span class="flex items-center gap-3"><LayoutPanelLeft class="h-5 w-5" /> Ad Management</span>
                            <ChevronDown v-if="menuStates.ad || adActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.ad || adActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in adManagementPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('email')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': emailActive }"
                        >
                            <span class="flex items-center gap-3"><Mail class="h-5 w-5" /> Emails</span>
                            <ChevronDown v-if="menuStates.email || emailActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.email || emailActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in emailPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('news')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': newsActive }"
                        >
                            <span class="flex items-center gap-3"><Newspaper class="h-5 w-5" /> Caribbean 360 News</span>
                            <ChevronDown v-if="menuStates.news || newsActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.news || newsActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in newsPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>
                    <button
                        @click="showView('notifications')"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive('notifications') }"
                    >
                        <Bell class="h-5 w-5" /> Push Notifications
                    </button>
                </div>
            </div>

            <!-- Trust & Operations Category -->
            <div v-if="isAllowed('trust')" class="space-y-1">
                <button
                    @click="toggleCat('trust')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="trustCatActive ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><ShieldCheck class="h-4 w-4" /> Trust & Operations</span>
                    <ChevronDown v-if="catStates.trust || trustCatActive" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.trust || trustCatActive" class="space-y-1 pb-1">
                    <button
                        v-for="p in trustPages"
                        :key="p[0]"
                        @click="showView(p[0])"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 font-bold"
                        :class="{ 'nav-active': isActive(p[0]) }"
                    >
                        <component :is="iconMap[p[1]]" class="h-5 w-5" /> {{ p[2] }}
                    </button>
                </div>
            </div>

            <div v-if="isAllowed('admin')" class="my-2 border-t border-slate-200"></div>

            <!-- Admin Category -->
            <div v-if="isAllowed('admin')" class="space-y-1">
                <button
                    @click="toggleCat('admin')"
                    class="mt-1 flex w-full items-center justify-between rounded-2xl px-3 py-2 text-xs font-black tracking-wide uppercase hover:bg-slate-50"
                    :class="adminCatActive ? 'text-purple-700' : 'text-slate-500'"
                >
                    <span class="flex items-center gap-2"><Settings class="h-4 w-4" /> Administration</span>
                    <ChevronDown v-if="catStates.admin" class="h-4 w-4" />
                    <ChevronRight v-else class="h-4 w-4" />
                </button>
                <div v-show="catStates.admin" class="space-y-1 pb-1">
                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('fees')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': feesActive }"
                        >
                            <span class="flex items-center gap-3"><SlidersHorizontal class="h-5 w-5" /> Fees</span>
                            <ChevronDown v-if="menuStates.fees || feesActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.fees || feesActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in feesPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('admins')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': adminActive }"
                        >
                            <span class="flex items-center gap-3"><UserCog class="h-5 w-5" /> Admins</span>
                            <ChevronDown v-if="menuStates.admins || adminActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.admins || adminActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <button
                                v-for="p in adminPages"
                                :key="p[0]"
                                @click="showView(p[0])"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                :class="{ 'nav-active': isActive(p[0]) }"
                            >
                                <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <button
                            @click="toggleMenu('settings')"
                            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 font-bold"
                            :class="{ 'nav-active': settingsActive }"
                        >
                            <span class="flex items-center gap-3"><Settings class="h-5 w-5" /> Settings</span>
                            <ChevronDown v-if="menuStates.settings || settingsActive" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <div v-show="menuStates.settings || settingsActive" class="ml-5 space-y-1 border-l border-slate-200 pl-3">
                            <template v-for="p in settingsPages" :key="p[0]">
                                <Link
                                    v-if="p[0].startsWith('admin.')"
                                    :href="route(p[0])"
                                    class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                    :class="{ 'nav-active': isActive(p[0]) || isActive(p[3]!) }"
                                >
                                    <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                                </Link>
                                <button
                                    v-else
                                    @click="showView(p[0])"
                                    class="flex w-full items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold"
                                    :class="{ 'nav-active': isActive(p[0]) }"
                                >
                                    <component :is="iconMap[p[1]]" class="h-4 w-4" /> {{ p[2] }}
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="border-t border-slate-100 p-4">
            <div class="ribbon rounded-3xl p-5 text-white">
                <p class="text-xs opacity-60">Global GTV</p>
                <h3 class="mt-1 text-3xl font-black">$14.2M</h3>
                <p class="mt-2 text-xs text-lime-300">Money moving through LinkUp</p>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.nav-active {
    background: linear-gradient(90deg, rgba(40, 168, 255, 0.1), rgba(217, 236, 16, 0.1));
    border-right: 4px solid #28A8FF;
    color: #07111f;
}
.ribbon {
    background: linear-gradient(90deg, #07111f, #0f2745, #07111f);
}
</style>
