<template>
    <aside style="background-color: royalblue;" v-if="isOrganizer"
        class="sticky top-4 h-fit max-h-[calc(100vh-2rem)] overflow-y-auto rounded-xl bg-[#0089cd]/80 p-6 shadow-lg backdrop-blur-md xl:w-[30rem]">
        <!-- Profile -->
        <Link :href="route('frontend.profile.edit')">
            <div class="flex flex-col items-center justify-center sm:gap-5 md:gap-1">
                <div class="h-28 w-28 rounded-full border-white sm:h-32 sm:w-32">
                    <ImagePreview v-if="authUser?.user?.avatar" :imageUrl="authUser.user.avatar" />
                    <ImagePreview v-else-if="authUser?.avatar" :imageUrl="authUser.avatar" />
                </div>
                <div class="text-center">

                    <h2 class="text-lg font-bold text-white capitalize sm:text-2xl lg:text-3xl">
                        {{ authUser?.user?.name ?? authUser?.name }}
                    </h2>
                    <p class="mt-1 text-sm text-white sm:text-base">
                        {{ page.props.auth?.user?.linkup_id ?? authUser?.user?.email ?? authUser?.email }}
                    </p>
                </div>
            </div>
        </Link>

        <!-- Navigation -->
        <nav class="mt-10 space-y-4">
            <ul class="space-y-3 text-white">
                <!-- Home -->
                <template v-if="hasPermission('organizer dashboard')">
                    <h1
                        class="flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <House color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Home
                    </h1>
                    <NavLink v-if="hasOtherCategories || (!hasCookouts && !hasWellness)"
                        :href="route('organizer.dashboard')"
                        :class="isActive('/organizer/dashboard') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <House color="white" class="h-4 w-4 inline mr-2" /> Events
                    </NavLink>
                    <NavLink v-if="hasCookouts" :href="route('organizer.cookout.dashboard')"
                        :class="isActive('/organizer/cookout/dashboard') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <House color="white" class="h-4 w-4 inline mr-2" /> Cookout dashboard
                    </NavLink>
                    <NavLink v-if="hasWellness" :href="route('organizer.wallness-spa.dashboard')"
                        :class="isActive('/organizer/wallness-spa/dashboard') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <House color="white" class="h-4 w-4 inline mr-2" /> Wellness and Spa dashboard
                    </NavLink>
                </template>

                <!-- Organizer Profile -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <UserCircle color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Organizer Profile
                </h1>
                <NavLink :href="route('organizer.profile.index')"
                    :class="isActive('/organizer/profile') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <UserCircle color="white" class="h-4 w-4 inline mr-2" /> My Profile
                </NavLink>

                <!-- Events -->
                <template
                    v-if="hasPermission('organizer events') || hasPermission('organizer sponsors') || hasPermission('organizer coupons')">
                    <h1
                        class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <CalendarCheck color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Events
                    </h1>
                    <NavLink v-if="hasPermission('organizer events')" :href="route('organizer.event.index')"
                        :class="isActive('/organizer/event') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                        <CalendarDays color="white" class="h-4 w-4 inline mr-2" /> My Events
                    </NavLink>
                    <NavLink v-if="hasPermission('organizer sponsors')" :href="route('organizer.event.sponsor.index')"
                        :class="isActive('/organizer/event/sponsors') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <Handshake color="white" class="h-4 w-4 inline mr-2" /> Sponsor
                    </NavLink>
                    <NavLink v-if="hasPermission('organizer coupons')" :href="route('organizer.event.coupon.index')"
                        :class="isActive('/organizer/event/coupons') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <TicketPercent color="white" class="h-4 w-4 inline mr-2" /> Coupons
                    </NavLink>
                </template>

                <!-- Scanner App -->
                <template
                    v-if="hasPermission('organizer scan ticket') || hasPermission('organizer scanners') || hasPermission('organizer scanner app setting')">
                    <h1
                        class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <ScanLine color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Scanner App
                    </h1>
                    <!-- <NavLink v-if="hasPermission('organizer scan ticket')" :href="route('organizer.scanner.scan-view')"
                        :class="isActive('/organizer/scanner/scan') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <ScanLine color="white" class="h-4 w-4 inline mr-2" /> Scan Ticket
                    </NavLink> -->
                    <NavLink v-if="hasPermission('organizer scanners')" :href="route('organizer.scanner.index')"
                        :class="isActive('/organizer/scanner') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                        <ScanLine color="white" class="h-4 w-4 inline mr-2" /> My Scanners
                    </NavLink>
                    <NavLink v-if="hasPermission('organizer scanner app setting')"
                        :href="route('organizer.scanner.app.setting')"
                        :class="isActive('/organizer/scanner/app-setting') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <Settings2 color="white" class="h-4 w-4 inline mr-2" /> Scanner App Setting
                    </NavLink>
                </template>

                <!-- My Point Of Sale -->
                <template v-if="hasPermission('organizer pos')">
                    <h1
                        class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <ShoppingCart color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> My Point Of Sale
                    </h1>
                    <NavLink :href="route('organizer.pos.index')"
                        :class="isActive('/organizer/pos') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                        <ShoppingCart color="white" class="h-4 w-4 inline mr-2" /> Point of Sale
                    </NavLink>
                </template>

                <!-- Reviews -->
                <template v-if="hasPermission('organizer reviews')">
                    <h1
                        class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <Star color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Review
                    </h1>
                    <NavLink :href="route('organizer.review.index')"
                        :class="isActive('/organizer/review') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                        <Star color="white" class="h-4 w-4 inline mr-2" /> Events Review
                    </NavLink>
                </template>

                <!-- Payouts -->
                <template v-if="hasPermission('organizer payouts') || hasPermission('organizer payout methods')">
                    <h1
                        class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <Wallet color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Payout
                    </h1>
                    <NavLink v-if="hasPermission('organizer payouts')" :href="route('organizer.payout.request')"
                        :class="isActive('/organizer/payout/request') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <Send color="white" class="h-4 w-4 inline mr-2" /> My Payout Request
                    </NavLink>
                    <NavLink v-if="hasPermission('organizer payout methods')" :href="route('organizer.payout.method')"
                        :class="isActive('/organizer/payout/method') ? 'text-primary font-bold' : 'text-white'"
                        class="ml-8">
                        <CreditCard color="white" class="h-4 w-4 inline mr-2" /> Payout Method
                    </NavLink>
                </template>

                <!-- Reports -->
                <template v-if="hasPermission('organizer reports')">
                    <h1
                        class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                        <BarChart3 color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Reports
                    </h1>
                    <NavLink :href="route('organizer.report.statistics')"
                        :class="isActive('/organizer/report') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                        <BarChart3 color="white" class="h-4 w-4 inline mr-2" /> My Reports
                    </NavLink>
                </template>

                <!-- Account -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <UserCog color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Account
                </h1>
                <!-- <NavLink :href="route('frontend.profile.edit')"
                    :class="isActive('/profile') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Settings color="white" class="h-4 w-4 inline mr-2" /> Settings
                </NavLink> -->

                <!-- Logout -->
                <NavLink :href="route('logout')" method="post" as="button"
                    class="mt-4 ml-8 text-white hover:text-red-300">
                    <KeyRound color="white" class="h-4 w-4 inline mr-2" /> Logout
                </NavLink>
            </ul>
        </nav>
    </aside>

    <aside style="background-color: royalblue;" v-else
        class="sticky top-4 h-fit max-h-[calc(100vh-2rem)] overflow-y-auto rounded-xl bg-[#0089cd]/80 p-6 shadow-lg backdrop-blur-md xl:w-[30rem]">
        <!-- Profile -->
        <Link :href="route('frontend.profile.edit')">
            <div
                class="flex items-center gap-4 rounded-2xl bg-white/10 p-4 border border-white/10 hover:bg-white/15 transition">

                <!-- Avatar -->
                <div class="relative shrink-0">
                    <div class="h-16 w-16 overflow-hidden rounded-full border-2 border-cyan-400">
                        <ImagePreview v-if="page.props.auth.user?.user?.avatar"
                            :imageUrl="page.props.auth.user.user.avatar" style="margin-left: -18%;margin-top: -15%;" />

                        <ImagePreview v-else-if="page.props.auth.user?.avatar" :imageUrl="page.props.auth.user.avatar"
                            style="margin-left: -18%;margin-top: -15%;" />
                    </div>

                    <div class="absolute bottom-0 right-0 h-4 w-4 rounded-full bg-green-500 border-2 border-[#4169E1]">
                    </div>
                </div>

                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-lg font-bold text-white">
                        {{ page.props.auth.user?.user?.name ?? page.props.auth.user?.name }}
                    </h2>

                    <p class="truncate text-xs text-white/70">
                        {{ page.props.auth?.user?.linkup_id }}
                    </p>

                    <div class="mt-2 flex items-center gap-2">
                        <span class="rounded-full bg-cyan-500/20 px-2 py-1 text-xs font-medium text-cyan-300">
                            Popularity:
                            {{ page.props.auth.user?.user?.popularity_level ??
                                page.props.auth.user?.popularity_level ??
                                'Low' }}
                        </span>
                    </div>
                </div>
            </div>
        </Link>
        <!-- Navigation -->
        <nav class="mt-10 space-y-4">
            <ul class="space-y-3 text-white">
                <!-- Home -->
                <h1
                    class="flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <House color="white" class="h-5 w-5 sm:h-6 sm:w-6" /> Home
                </h1>
                <NavLink :href="route('frontend.home.matches')"
                    :class="isActive('/home') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Home class="h-4 w-4 inline mr-2" />
                    Home
                </NavLink>

                <NavLink :href="route('frontend.user.allnotifications')" class="ml-8 text-white">
                    <Bell class="h-4 w-4 inline mr-2" />Notification
                </NavLink>
            </ul>
            <ul class="space-y-3 text-white">

                <!-- Matches & Social -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <Heart class="h-5 w-5 sm:h-6 sm:w-6" /> Matches & Social
                </h1>
                <NavLink :href="route('frontend.find.matches')"
                    :class="isActive('/findmatch') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Heart class="h-4 w-4 inline mr-2" />Find Matches
                </NavLink>
                <NavLink :href="route('frontend.user.matches')"
                    :class="isActive('/allmatches') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Users class="h-4 w-4 inline mr-2" />Matches
                </NavLink>
                <NavLink :href="route('frontend.friend.index')"
                    :class="isActive('/friend') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <HeartHandshake class="h-4 w-4 inline mr-2" />Friends
                </NavLink>
                <NavLink :href="route('frontend.friend-request.index')"
                    :class="isActive('/friend-request') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <UserPlus class="h-4 w-4 inline mr-2" />Friend
                    Request
                </NavLink>
                <NavLink :href="route('frontend.user.likes')"
                    :class="isActive('/likes') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Heart class="h-4 w-4 inline mr-2" />Likes
                </NavLink>
                <!-- <NavLink :href="route('frontend.gift.index')" class="ml-8 text-white">Gifts</NavLink> -->

                <NavLink :href="route('frontend.user.chat')"
                    :class="isActive('/user/chat') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <MessageCircle class="h-4 w-4 inline mr-2" />All Chats
                </NavLink>

                <!-- Linkup Live -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <Radio class="h-5 w-5 sm:h-6 sm:w-6" /> Linkup Live
                </h1>
                <button @click="openLiveDialog" class="ml-8 text-white hover:text-blue-200 transition-colors">
                    <Radio class="h-4 w-4 inline mr-2" />Go
                    Live
                </button>

                <NavLink :href="route('frontend.go.live.network')"
                    :class="isActive('go/live/network') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Users class="h-4 w-4 inline mr-2" />Live
                    Network
                </NavLink>
                <!-- Linkup Live -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <Store class="h-5 w-5 sm:h-6 sm:w-6" />Market Place
                </h1>
                <NavLink :href="route('frontend.products.index')"
                    :class="isActive('/products') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Store class="h-4 w-4 inline mr-2" />LinkUp Market
                    Place
                </NavLink>

                <!-- Clubs & Restaurants -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <Users class="h-5 w-5 sm:h-6 sm:w-6" /> Clubs & Restaurants
                </h1>
                <NavLink :href="route('frontend.clubs.index')"
                    :class="isActive('/clubs') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Martini class="h-4 w-4 inline mr-2" /> Clubs Near By
                </NavLink>
                <NavLink :href="route('frontend.restaurants.index')"
                    :class="isActive('/restaurants') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <UtensilsCrossed class="h-4 w-4 inline mr-2" />Restaurants
                    Near By
                </NavLink>

                <!-- Events -->
                <h1
                    class="mt-6 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <CalendarDays class="h-5 w-5 sm:h-6 sm:w-6" /> Events
                </h1>
                <NavLink :href="route('frontend.event.index')"
                    :class="isActive('/event') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <CalendarDays class="h-4 w-4 inline mr-2" />Event
                </NavLink>
                <NavLink :href="route('frontend.bookings.upcomming')"
                    :class="isActive('/bookings/upcomming') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Ticket class="h-4 w-4 inline mr-2" />Booking
                </NavLink>
                <NavLink :href="route('frontend.bookings.cancelled')"
                    :class="isActive('/bookings/cancelled') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <XCircle class="h-4 w-4 inline mr-2" />Canceled
                </NavLink>
                <NavLink :href="route('frontend.event.favorite')"
                    :class="isActive('/favorite-event') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Heart class="h-4 w-4 inline mr-2" />Favorite
                </NavLink>
                <NavLink :href="route('frontend.event.search')"
                    :class="isActive('/search-event') ? 'text-primary font-bold' : 'text-white'" class="ml-8"
                    style="display: none">Search Events</NavLink>

                <!-- Wallet & Membership -->
                <h1
                    class="mt-4 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <Wallet class="h-5 w-5 sm:h-6 sm:w-6" />Wallet & Membership
                </h1>
                <NavLink :href="route('frontend.user.wallet')"
                    :class="isActive('/user/wallet/amount') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Wallet class="h-4 w-4 inline mr-2" />Linkup Wallet
                </NavLink>
                <NavLink :href="route('frontend.user.wallet')"
                    :class="isActive('/user/wallet') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Receipt class="h-4 w-4 inline mr-2" />Transaction
                    History
                </NavLink>
                <NavLink :href="route('frontend.subscription.plans')"
                    :class="isActive('/subscriptionplans') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Crown class="h-4 w-4 inline mr-2" />Subscription
                </NavLink>

                <!-- Settings & Notifications -->
                <h1
                    class="mt-4 flex items-center gap-4 rounded-full bg-[#0089cd]/60 px-4 py-2 font-semibold text-white shadow-sm">
                    <Settings class="h-5 w-5 sm:h-6 sm:w-6" /> Settings & Notifications
                </h1>
                <NavLink :href="route('frontend.news.index')"
                    :class="isActive('/news') ? 'text-primary font-bold' : 'text-white'" class="ml-8">
                    <Bell class="h-4 w-4 inline mr-2" />
                    News
                </NavLink>
                <NavLink :href="route('logout')" method="post" as="button" class="mt-2 ml-8 text-white">
                    <LogOut class="h-4 w-4 inline mr-2" />Logout
                </NavLink>
            </ul>
        </nav>
    </aside>

    <!-- LinkUp Live Dialog -->
    <LinkUpLiveDialog ref="liveDialogRef" @subscribe="handleSubscribe" @continue="handleGoLive" />
</template>

<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { BarChart3, Bell, CalendarCheck, CalendarDays, CreditCard, Crown, Handshake, Heart, HeartHandshake, Home, House, KeyRound, LogOut, Martini, MessageCircle, NotebookIcon, Radio, Receipt, ScanLine, Send, Settings, Settings2, ShoppingCart, Star, Store, Ticket, TicketPercent, UserCircle, UserCog, UserPlus, Users, UtensilsCrossed, Wallet, XCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import ImagePreview from './ImagePreview.vue';
import NavLink from './NavLink.vue';
import LinkUpLiveDialog from './LinkUpLiveDialog.vue';

const page = usePage<{
    auth: {
        user?: {
            name?: string;
            email?: string;
            avatar?: string;
            organizer_profile?: {
                categories?: string[];
            };
            user?: {
                name?: string;
                email?: string;
                avatar?: string;
            };
        };
    };
}>();

const authUser = computed(() => page.props.auth?.user);
const permissions = computed(() => page.props.auth?.permissions || []);
const organizerCategories = computed(() => authUser.value?.organizer_profile?.categories ?? []);
const normalizedOrganizerCategories = computed(() =>
    organizerCategories.value.map(cat => (cat ?? '').toString().trim().toLowerCase()),
);

const hasCookouts = computed(() => normalizedOrganizerCategories.value.includes('cookouts/food') || normalizedOrganizerCategories.value.includes('cookouts'));
const hasWellness = computed(() => normalizedOrganizerCategories.value.includes('wellness and spa'));
const hasOtherCategories = computed(() => {
    return normalizedOrganizerCategories.value.some(cat => cat !== 'cookouts/food' && cat !== 'cookouts' && cat !== 'wellness and spa');
});

const hasPermission = (permission: string) => {
    return permissions.value.includes(permission);
};

const isActive = (url: string) => {
    return page.url === url || page.url.startsWith(url + '?');
};

const isOrganizer = ref(window.sessionStorage.getItem('isOrganizer'));

// LinkUp Live Dialog
const liveDialogRef = ref();

const openLiveDialog = () => {
    liveDialogRef.value?.openDialog();
};

const handleSubscribe = (isSubscribed: boolean) => {
    console.log('Subscription status changed:', isSubscribed);
    // You can add additional logic here, like showing a toast notification
};

const handleGoLive = () => {
    // Navigate to the actual go-live page or open the live streaming interface
    router.visit(route('frontend.go-live.index'));
};
</script>

<style scoped>
aside::-webkit-scrollbar {
    width: 6px;
}

aside::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

aside::-webkit-scrollbar-track {
    background-color: transparent;
}

aside {
    margin-left: -14%;
    margin-top: -12%;
    width: 22rem;
}

@media (max-width: 1280px) {

    aside {
        margin-left: 0%;
        margin-top: 0%;
        width: 100%;

    }
}
</style>
