<template>
    <div class="fade">
        <!-- Hero Header -->
        <div class="relative mb-5 overflow-hidden rounded-3xl p-6"
            style="background: linear-gradient(120deg, #2f9bef, #2563eb 60%, #6d5efc)">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-black text-white md:text-3xl">🎉 Events</h1>
                    <p class="font-semibold text-white/80">Fêtes, spas, cookouts &amp; more near you</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('organizer.dashboard')"
                        class="btn flex items-center gap-2 bg-white/15 px-4 py-2.5 text-sm text-white">
                        <i data-lucide="bar-chart-2" class="h-4 w-4"></i>Organizer Dashboard
                    </Link>
                    <button type="button" @click="openCreateEvent"
                        class="btn text-lkblue2 flex items-center gap-2 bg-white/95 px-4 py-2.5 text-sm font-black">
                        <i data-lucide="plus" class="h-4 w-4"></i>Create Event
                    </button>
                </div>
            </div>
            <!-- AI Search -->
            <div class="relative mt-4">
                <i data-lucide="sparkles"
                    class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-white/70"></i>
                <input v-model="searchQuery" @keyup.enter="doAiSearch" @input="
                    filters.search = searchQuery;
                handleSearch();
                " placeholder="Ask AI: free fêtes this weekend…"
                    class="w-full rounded-full bg-white/15 py-3 pr-28 pl-11 text-sm text-white placeholder-white/70 transition outline-none focus:bg-white/25" />
                <button @click="doAiSearch"
                    class="absolute top-1/2 right-1.5 -translate-y-1/2 rounded-full px-4 py-1.5 text-sm font-black text-slate-900"
                    style="background: var(--lk-yellow)">
                    ✨ AI
                </button>
            </div>
            <!-- Menu tabs -->
            <div class="mt-4 flex gap-2 overflow-x-auto">
                <button v-for="t in EV_MENU" :key="t" @click="
                    activeMenu = t;
                aiResults = null;
                "
                    :class="['shrink-0 rounded-full px-4 py-1.5 text-sm font-black transition', activeMenu === t ? 'text-slate-900' : 'text-white']"
                    :style="activeMenu === t ? 'background:var(--lk-yellow)' : 'background:rgba(255,255,255,.18)'">
                    {{ t }}
                </button>
            </div>

            <!-- Location Trigger (inside hero) -->
            <div v-if="activeMenu === 'Event'" class="mt-4 flex flex-wrap items-center gap-2">
                <button @click="openLocationSelector"
                    class="flex min-w-[180px] items-center gap-2 rounded-full border border-white/10 bg-white/15 px-4 py-2 text-xs font-black text-white transition-all hover:bg-white/25">
                    <template v-if="isLocatingLocal">
                        <div class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent">
                        </div>
                        <span>Finding location...</span>
                    </template>
                    <template v-else>
                        <i data-lucide="map-pin" class="h-3.5 w-3.5"></i>
                        <span>Browsing events in {{ currentCityName }}</span>
                        <i data-lucide="chevron-down" class="h-3 w-3 opacity-70"></i>
                    </template>
                </button>
                <button @click="clearLocationFilter" :class="[
                    'rounded-full border border-white/10 px-4 py-2 text-xs font-black transition-all',
                    !filters.city && !filters.lat && !filters.lng ? 'bg-white text-blue-600' : 'bg-white/15 text-white hover:bg-white/25',
                ]">
                    All
                </button>
            </div>
        </div>

        <!-- Category chips (Event tab only) -->
        <div v-if="activeMenu === 'Event'" class="mb-5 flex flex-col gap-3">
            <!-- Categories Scroll -->
            <div class="flex gap-2 overflow-x-auto scroll-smooth pb-1">
                <button @click="clearCategoryFilter"
                    :class="['chip flex items-center gap-1.5', !filters.category_id ? 'on' : '']">
                    <span>All</span>
                </button>
                <button v-for="category in props.allCategories" :key="category.id"
                    @click="selectedCategory(category.id)"
                    :class="['chip flex items-center gap-1.5', String(filters.category_id) === String(category.id) ? 'on' : '']">
                    <span>{{ category.name }}</span>
                </button>
            </div>
        </div>

        <!-- AI search results -->
        <template v-if="aiResults !== null">
            <div class="card mb-4 flex items-start gap-2 p-3" style="border-color: rgba(47, 155, 239, 0.35)">
                <i data-lucide="sparkles" class="text-lkblue mt-0.5 h-5 w-5 shrink-0"></i>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-black">AI Event Search</p>
                    <p class="text-[12px] text-slate-500">{{ aiSummary }}</p>
                </div>
                <button @click="
                    aiResults = null;
                searchQuery = '';
                " class="text-lkblue2 ml-auto shrink-0 text-xs font-black underline">
                    Clear
                </button>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <EventCard v-for="e in aiResults" :key="e.id" :event="e" @open="openEvent" @toggleFav="toggleFav" />
            </div>
        </template>

        <template v-else-if="activeMenu === 'Favorite'">
            <h3 class="mb-3 text-xl font-black">Favorite Events</h3>
            <template v-if="favoritedEvents.length">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <EventCard v-for="e in favoritedEvents" :key="e.id" :event="e" @open="openEvent"
                        @toggleFav="toggleFav" />
                </div>
            </template>
            <div v-else class="card p-10 text-center font-bold text-slate-400">No favorites yet — tap the ♥ on an event.
            </div>
        </template>

        <!-- BOOKING -->
        <template v-else-if="activeMenu === 'Booking'">
            <h3 class="mb-3 text-xl font-black">My Bookings</h3>
            <template v-if="bookingGroups.length">
                <div class="grid max-w-[760px] grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <TicketCard v-for="g in bookingGroups" :key="`active-${g.event_id || g.event}`" :group="g"
                        :cancelled="false" @qr="showTicketDetail" @cancel="cancelTicket" />
                </div>
            </template>
            <div v-else class="card p-10 text-center font-bold text-slate-400">No bookings yet.</div>
        </template>

        <!-- CANCELLED -->
        <template v-else-if="activeMenu === 'Canceled'">
            <h3 class="mb-3 text-xl font-black">Cancelled Bookings</h3>
            <template v-if="cancelledBookingGroups.length">
                <div class="grid max-w-[760px] grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <TicketCard v-for="g in cancelledBookingGroups" :key="`cancelled-${g.event_id || g.event}`"
                        :group="g" :cancelled="true" @qr="showTicketDetail" />
                </div>
            </template>
            <div v-else class="card p-10 text-center font-bold text-slate-400">No canceled events.</div>
        </template>

        <!-- EVENT main browse -->
        <template v-else>
            <!-- Loading state -->
            <div v-if="isLoading" class="card p-10 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="border-lkblue mb-4 h-12 w-12 animate-spin rounded-full border-4 border-t-transparent">
                    </div>
                    <p class="font-bold text-slate-500">Loading events...</p>
                </div>
            </div>

            <!-- Event content (hidden during loading) -->
            <template v-else>
                <!-- Filtered by category (backend filtered) -->
                <template v-if="filters.category_id">
                    <h3 class="mb-3 text-xl font-black">{{ selectedCategoryName }}</h3>
                    <div v-if="props.events.length" class="flex gap-4 overflow-x-auto pb-1">
                        <EventCard v-for="e in props.events" :key="e.id" :event="e" :horizontal="true" @open="openEvent"
                            @toggleFav="toggleFav" />
                    </div>
                    <div v-else class="card p-10 text-center font-bold text-slate-400">No events found for this
                        category.</div>
                </template>

                <!-- Rows of events (default view) -->
                <template v-else>
                    <div v-if="topTrending.length" class="mb-7">
                        <h3 class="mb-3 text-xl font-black">Top Trending Events</h3>
                        <div class="flex gap-4 overflow-x-auto pb-1">
                            <EventCard v-for="e in topTrending" :key="e.id" :event="e" :horizontal="true"
                                @open="openEvent" @toggleFav="toggleFav" />
                        </div>
                    </div>
                    <div v-if="under30.length" class="mb-7">
                        <h3 class="mb-3 text-xl font-black">Events $30 &amp; under</h3>
                        <div class="flex gap-4 overflow-x-auto pb-1">
                            <EventCard v-for="e in under30" :key="e.id" :event="e" :horizontal="true" @open="openEvent"
                                @toggleFav="toggleFav" />
                        </div>
                    </div>
                    <div v-if="freeEvents.length" class="mb-7">
                        <h3 class="mb-3 text-xl font-black">Free Events</h3>
                        <div class="flex gap-4 overflow-x-auto pb-1">
                            <EventCard v-for="e in freeEvents" :key="e.id" :event="e" :horizontal="true"
                                @open="openEvent" @toggleFav="toggleFav" />
                        </div>
                    </div>
                    <div v-if="eventsNear.length" class="mb-7">
                        <h3 class="mb-3 text-xl font-black">Events near {{ user.city }}</h3>
                        <div class="flex gap-4 overflow-x-auto pb-1">
                            <EventCard v-for="e in eventsNear" :key="e.id" :event="e" :horizontal="true"
                                @open="openEvent" @toggleFav="toggleFav" />
                        </div>
                    </div>
                    <div v-if="upcomingEvents.length" class="mb-7">
                        <h3 class="mb-3 text-xl font-black">Upcoming Events</h3>
                        <div class="flex gap-4 overflow-x-auto pb-1">
                            <EventCard v-for="e in upcomingEvents" :key="e.id" :event="e" :horizontal="true"
                                @open="openEvent" @toggleFav="toggleFav" />
                        </div>
                    </div>
                </template>
            </template>
        </template>

        <!-- Modals -->
        <EventDetailModal ref="detailModalRef" :all-events="allEventsData" @toggleFav="toggleFav"
            @buy="openTicketCheckout" />
        <TicketCheckoutModal ref="checkoutModalRef" :event-fee-settings="eventFeeSettings" :tax-rules="taxRules"
            @complete="onTicketPurchase" />
        <TicketDetailModal ref="ticketDetailModalRef" @qr="showQR" @cancel="cancelTicket" />
        <CreateEventModal ref="createEventModalRef" :categories="props.allCategories"
            :caribbeans="props.caribbeans || []" :scanners="props.scanners || []" :all-events="props.allEvents || []"
            :appURL="props.appURL || ''" :as-modal="true" />
        <TicketQRModal ref="qrModalRef" />
        <LocationSelectorModal ref="locationSelectorRef" @select="handleLocationSelect" />
        <CancelTicketConfirmModal ref="cancelConfirmModalRef" @cancel="submitCancelTicket"
            @contact="onContactPromoter" />
        <ContactPromoterModal ref="contactPromoterModalRef" @back="backToCancellation" />
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';
import EventCard from '../../../components/new_frontend/cards/EventCard.vue';
import TicketCard from '../../../components/new_frontend/cards/TicketCard.vue';
import { getUser } from '../../../components/new_frontend/MockDataStore';
import CancelTicketConfirmModal from '../../../components/new_frontend/modals/CancelTicketConfirmModal.vue';
import ContactPromoterModal from '../../../components/new_frontend/modals/ContactPromoterModal.vue';
import CreateEventModal from '../../organizer/event/Create.vue';
import EventDetailModal from '../../../components/new_frontend/modals/EventDetailModal.vue';
import LocationSelectorModal from '../../../components/new_frontend/modals/LocationSelectorModal.vue';
import TicketCheckoutModal from '../../../components/new_frontend/modals/TicketCheckoutModal.vue';
import TicketDetailModal from '../../../components/new_frontend/modals/TicketDetailModal.vue';
import TicketQRModal from '../../../components/new_frontend/modals/TicketQRModal.vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';

defineOptions({ layout: MainLayout });

const EV_MENU = ['Event', 'Booking', 'Canceled', 'Favorite'];

const props = defineProps({
    events: { type: Array, default: () => [] },
    allCategories: { type: Array, default: () => [] },
    newProvidence: { type: Array, default: () => [] },
    eventLowCost: { type: Array, default: () => [] },
    jamaicaEvent: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({}) },
    freeEvents: { type: Array, default: () => [] },
    activeTickets: { type: Array, default: () => [] },
    cancelledTickets: { type: Array, default: () => [] },
    eventFeeSettings: { type: Object, default: () => ({}) },
    taxRules: { type: Object, default: () => ({}) },
    countryTaxRules: { type: Array, default: () => [] },
    eventCountries: { type: Array, default: () => [] },
    caribbeans: { type: Array, default: () => [] },
    scanners: { type: Array, default: () => [] },
    allEvents: { type: Array, default: () => [] },
    appURL: { type: String, default: '' },
});
const user = props.user && Object.keys(props.user).length ? props.user : getUser();
const eventFeeSettings = computed(() => props.eventFeeSettings || {});
const taxRules = computed(() => props.taxRules || {});
const countryTaxRules = computed(() => props.countryTaxRules || []);
const eventCountries = computed(() => props.eventCountries || []);
const showToast = (msg) => {
    if (window.toast) window.toast(msg);
};

const activeMenu = ref('Event');
const searchQuery = ref('');
const aiResults = ref(null);
const aiSummary = ref('');
const isLoading = ref(false);

// Filter state from User/Event functionality
const searchParams = new URL(window.location.href).searchParams;

const filters = ref({
    search: searchParams.get('search') ?? '',
    category_id: searchParams.get('category_id') ?? '',
    lat: searchParams.get('lat') ?? '',
    lng: searchParams.get('lng') ?? '',
    city: searchParams.get('city') ?? '',
});

let debounceTimeout = 0;

const allEventsData = computed(() => {
    const all = [
        ...(props.events || []),
        ...(props.newProvidence || []),
        ...(props.eventLowCost || []),
        ...(props.jamaicaEvent || []),
        ...(props.freeEvents || []),
    ];
    const map = new Map();
    all.forEach((e) => {
        if (e && e.id) map.set(e.id, e);
    });
    return Array.from(map.values());
});

const currentCityName = computed(() => {
    if (filters.value.city) return filters.value.city;
    if (filters.value.lat && filters.value.lng) return 'Current Location';
    return 'All Locations';
});

const detailModalRef = ref(null);
const checkoutModalRef = ref(null);
const ticketDetailModalRef = ref(null);
const createEventModalRef = ref(null);
const qrModalRef = ref(null);
const locationSelectorRef = ref(null);
const cancelConfirmModalRef = ref(null);
const contactPromoterModalRef = ref(null);
const isLocatingLocal = ref(false);

// Filter functions from User/Event functionality
const reloadWithFilters = () => {
    router.visit(route('new_frontend.events', filters.value), {
        preserveScroll: true,
        onStart: () => {
            isLoading.value = true;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const openLocationSelector = () => {
    if (locationSelectorRef.value) locationSelectorRef.value.open();
};

const handleLocationSelect = (loc) => {
    isLocatingLocal.value = false;
    if (loc.type === 'coordinates') {
        filters.value.lat = loc.lat;
        filters.value.lng = loc.lng;
        filters.value.city = 'Current Location';
    } else {
        filters.value.city = loc.name;
        filters.value.lat = '';
        filters.value.lng = '';
    }
    reloadWithFilters();
};

const clearLocationFilter = () => {
    filters.value.city = '';
    filters.value.lat = '';
    filters.value.lng = '';
    reloadWithFilters();
};

const requestAutoLocation = () => {
    if (filters.value.city || filters.value.lat || filters.value.lng) return;

    if (navigator.geolocation) {
        isLocatingLocal.value = true;
        navigator.geolocation.getCurrentPosition(
            (position) => {
                handleLocationSelect({
                    type: 'coordinates',
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                });
            },
            (err) => {
                console.warn('Geolocation error:', err);
                isLocatingLocal.value = false;
            },
            { timeout: 8000 },
        );
    }
};

onMounted(() => {
    // requestAutoLocation();
});

const selectedCategory = (categoryId) => {
    filters.value.category_id = categoryId;
    router.visit(route('new_frontend.events', filters.value), {
        onStart: () => {
            isLoading.value = true;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const clearCategoryFilter = () => {
    filters.value.category_id = '';
    router.visit(route('new_frontend.events', filters.value), {
        onStart: () => {
            isLoading.value = true;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const handleSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        reloadWithFilters();
    }, 400);
};

onMounted(() => { });

const topTrending = computed(() => props.newProvidence || []);
const under30 = computed(() => props.eventLowCost || []);
const freeEvents = computed(() => props.freeEvents || []);
const eventsNear = computed(() => props.jamaicaEvent || []);

const upcomingEvents = computed(() => {
    const now = new Date();
    now.setHours(0, 0, 0, 0); // Start of today
    return allEventsData.value.filter(e => {
        // Look for start_time or other date markers
        const dateStr = e.start_time || e.event_date || e.date;
        if (!dateStr) return true;
        return new Date(dateStr) >= now;
    });
});

const selectedCategoryName = computed(() => {
    if (!filters.value.category_id) return '';
    const category = props.allCategories.find((c) => c.id === filters.value.category_id);
    return category ? category.name : '';
});

const favoritedEvents = computed(() => {
    return allEventsData.value.filter((e) => e.auth_user_favorite !== null);
});

const bookingGroups = computed(() => props.activeTickets || []);
const cancelledBookingGroups = computed(() => props.cancelledTickets || []);

const toggleFav = (id) => {
    const event = allEventsData.value.find((e) => e.id === id);
    const isCurrentlyFavorited = event?.auth_user_favorite !== null;

    router.post(
        route('new_frontend.events.toggle-favorite', id),
        {},
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                showToast(isCurrentlyFavorited ? '♥ Removed from Favorites' : '♥ Added to Favorites');
            },
            onError: () => {
                showToast('Failed to update favorites');
            },
        },
    );
};

const onTicketPurchase = (ticketObj) => {
    showToast('Ticket purchased successfully!');
    // Handle ticket purchase completion
    console.log('Ticket purchased:', ticketObj);
};

const openEvent = (event) => {
    if (detailModalRef.value) {
        detailModalRef.value.open(event);
        nextTick(() => {
            if (window.lucide) window.lucide.createIcons();
        });
    }
};

const openTicketCheckout = (event) => {
    if (checkoutModalRef.value) checkoutModalRef.value.open(event);
};

const openCreateEvent = () => {
    if (user.type === 'organizer' || user.type === 'admin') {
        if (createEventModalRef.value) createEventModalRef.value.open();
    } else {
        showToast('Please complete your Organizer KYC to create events.');
    }
};

const showQR = ({ id, event }) => {
    if (qrModalRef.value) qrModalRef.value.open(id, event);
};

const showTicketDetail = (sale) => {
    if (ticketDetailModalRef.value) ticketDetailModalRef.value.open(sale);
};

const cancelTicket = (ticketIdOrSale) => {
    // Find the full sale object if only ID was passed
    const sale =
        typeof ticketIdOrSale === 'object'
            ? ticketIdOrSale
            : allEventsData.value.flatMap((e) => e.tickets || []).find((t) => t.id === ticketIdOrSale) ||
            bookingGroups.value.flatMap((g) => g.tickets || []).find((t) => t.id === ticketIdOrSale);

    if (!sale) return;

    if (cancelConfirmModalRef.value) cancelConfirmModalRef.value.open(sale);
};

const submitCancelTicket = ({ id, refund, reason }) => {
    router.post(
        route('new_frontend.events.bookings.cancel-request', id),
        {
            amount: refund,
            reason: reason,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast('Cancellation request submitted');
                if (cancelConfirmModalRef.value) cancelConfirmModalRef.value.close();
                if (ticketDetailModalRef.value) ticketDetailModalRef.value.close();
            },
            onError: () => showToast('Failed to submit cancellation request'),
        },
    );
};

const onContactPromoter = (sale) => {
    if (cancelConfirmModalRef.value) cancelConfirmModalRef.value.close();
    if (contactPromoterModalRef.value) contactPromoterModalRef.value.open(sale);
};

const backToCancellation = (sale) => {
    if (contactPromoterModalRef.value) contactPromoterModalRef.value.close();
    if (cancelConfirmModalRef.value) cancelConfirmModalRef.value.open(sale);
};

// AI search logic
const doAiSearch = () => {
    const q = searchQuery.value.trim();
    if (!q) {
        aiResults.value = null;
        return;
    }
    const s = q.toLowerCase();
    let list = [...allEventsData.value];
    const filters = [];

    const under = s.match(/(?:under|below|less than|max)\s*\$?\s*(\d+)/);
    if (/\bfree\b/.test(s)) {
        list = list.filter((e) => e.price === 0);
        filters.push('free');
    } else if (under) {
        const cap = +under[1];
        list = list.filter((e) => e.price <= cap);
        filters.push('under $' + cap);
    } else if (/cheap|budget|affordable/.test(s)) {
        list = list.filter((e) => e.price <= 30);
        filters.push('under $30');
    }
    if (/spa|wellness|massage|relax/.test(s)) {
        list = list.filter((e) => /wellness|spa/i.test((e.category || '') + e.title));
        filters.push('wellness & spa');
    }
    if (/cookout|bbq|grill|food/.test(s)) {
        list = list.filter((e) => /cookout|food/i.test((e.category || '') + e.title));
        filters.push('cookouts & food');
    }
    if (/fete|party|soca|carnival|dance/.test(s)) {
        list = list.filter((e) => /party|fete|dancing|soca|carnival/i.test((e.category || '') + e.title));
        filters.push('fêtes & parties');
    }
    if (/online|virtual|stream/.test(s)) {
        list = list.filter((e) => e.kind === 'online');
        filters.push('online');
    }

    const cards = list.length ? list : allEventsData.value.slice(0, 4);
    aiSummary.value = list.length
        ? `Found ${list.length} event${list.length > 1 ? 's' : ''}${filters.length ? ' · ' + filters.join(' · ') : ''} for "${q}"`
        : `No exact match for "${q}" — popular picks you might like`;
    aiResults.value = cards;
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};
</script>
