<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Calendar } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialEvents: any[];
    initialStats: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;
const eventRecords = ref([...props.initialEvents]);
const eventSearch = ref('');

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    renderAll();
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const getFilteredCountries = () => {
    return countries.filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const countryFin = (c: any) => {
    let gross = 0, platform = 0, bank = 0, cost = 0;
    units.forEach((u) => {
        const v = (Number(c[u.key]) || 0) * getScale();
        gross += v;
        platform += v * u.platformRate;
        bank += v * u.bankRate;
        cost += v * u.costRate;
    });
    return { gross, platform, bank, cost, net: platform - cost };
};

const getTotals = () => {
    const rs = getFilteredCountries();
    const fins = rs.map(countryFin);
    return {
        gross: fins.reduce((s, x) => s + x.gross, 0),
        platform: fins.reduce((s, x) => s + x.platform, 0),
        bank: fins.reduce((s, x) => s + x.bank, 0),
        cost: fins.reduce((s, x) => s + x.cost, 0),
        net: fins.reduce((s, x) => s + x.net, 0),
        users: rs.reduce((s, c) => s + (Number(c.users) || 0), 0) * getScale(),
        merchants: rs.reduce((s, c) => s + (Number(c.merchants) || 0), 0),
        organizers: rs.reduce((s, c) => s + (Number(c.organizers) || 0), 0),
        countries: rs.length,
    };
};

// Event Specific Logic
const filteredEvents = computed(() => {
    const q = eventSearch.value.toLowerCase();
    return eventRecords.value.filter(e => {
        const okCountry = filters.value.country === 'All Countries' || e.country === filters.value.country;
        // In initialCountries, we have region info. We should match e.country to find its region.
        const c = countries.find(x => x.country === e.country);
        const okRegion = filters.value.region === 'All' || c?.region === filters.value.region;
        const okSearch = !q || [e.event, e.organizer, e.email, e.category, e.city, e.status].join(' ').toLowerCase().includes(q);
        return okCountry && okRegion && okSearch;
    });
});

const eventMgmtRevenue = (e: any) => (Number(e.ticketRevenue) || 0) + (Number(e.drinkRevenue) || 0) + (Number(e.waterRevenue) || 0) + (Number(e.vipRevenue) || 0) + (Number(e.sponsorRevenue) || 0);

const stats = computed(() => {
    const rows = filteredEvents.value;
    return {
        total: rows.length,
        live: rows.filter(e => e.status.toLowerCase() === 'live').length,
        single: rows.filter(e => e.type.toLowerCase() === 'single').length,
        recurring: rows.filter(e => e.type.toLowerCase() === 'recurring').length
    };
});

const userRegion = (country: string) => {
    const c = countries.find(x => x.country === country);
    return c?.region || 'Other';
};

const openGroups = ref<Set<string>>(new Set());
const toggleGroup = (key: string) => {
    if (openGroups.value.has(key)) openGroups.value.delete(key);
    else openGroups.value.add(key);
};

const groupedEvents = computed(() => {
    const items = filteredEvents.value;
    const rm: any = {};
    items.forEach(it => {
        const c = it.country || 'Other';
        const r = userRegion(c);
        if (!rm[r]) rm[r] = {};
        if (!rm[r][c]) rm[r][c] = [];
        rm[r][c].push(it);
    });
    return rm;
});

const renderAll = async () => {
    await nextTick();
    const t = getTotals();
    const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
    const net = totalRev - t.cost;

    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('rGTV', fmt(t.gross));
    set('rLinkUp', fmt(t.platform));
    set('rBank', fmt(t.bank));
    set('rNet', fmt(net));
    set('rUsers', num(t.users));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(t.countries));
    set('sideGTV', fmt(t.gross));
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    // Default open some groups
    openGroups.value.add('R:Local');
    openGroups.value.add('R:Regional');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});

const toggleEventFeatured = (id: string) => {
    const e = eventRecords.value.find(x => x.id === id);
    if (!e) return;
    // @ts-ignore
    e.featuredHome = !e.featuredHome;
};

// Get event image URL
const getEventImage = (media: any) => {
    if (!media) return null;

    // Handle array of media paths
    const imagePath = Array.isArray(media) ? media[0] : media;

    if (!imagePath) return null;

    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    return `/storage/${imagePath}`;
};

const createEvent = () => {
    router.visit(route('admin.event.create'));
};

const handleEditEvent = (id: number) => {
    router.visit(route('admin.event.edit', id));
};
const handleEditTicket = (id: number) => {
    router.visit(route('admin.event.tickets', id));
};

const handleDeleteEvent = (id: number) => {
    deletingEventId.value = id;
    showDialog.value = true;
};

const deletingEventId = ref<number | null>(null);
const showDialog = ref(false);
const deleteForm = useForm({});

function deleteEvent() {
    if (!deletingEventId.value) return;
    const url = route('admin.event.destroy', deletingEventId.value);
    const id = deletingEventId.value;
    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            eventRecords.value = eventRecords.value.filter(e => e.id !== id);
            toast.success('Event deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete event');
        },
        onFinish: () => (showDialog.value = false),
    });
}
</script>

<template>
    <Head title="Events Dashboard" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eventMgmtCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Events" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-purple-600">Events</h3>
                        <p class="text-slate-500">Manage and organize all LinkUp events.</p>
                    </div>
                    <div class="flex gap-2">
                        <input
                            v-model="eventSearch"
                            class="rounded-2xl border border-slate-200 px-4 py-2 w-80"
                            placeholder="Search events..."
                        />
                        <button
                            @click="createEvent"
                            class="rounded-2xl bg-purple-600 text-white px-5 py-2 font-black"
                        >
                            + Add Event
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center">
                            <Calendar class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-slate-500">Total Events</p>
                            <h3 class="text-3xl font-black">{{ num(stats.total) }}</h3>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-green-100 text-green-600 grid place-items-center">
                            <div class="w-3 h-3 rounded-full bg-green-600 animate-pulse"></div>
                        </div>
                        <div>
                            <p class="text-slate-500">Live Events</p>
                            <h3 class="text-3xl font-black">{{ num(stats.live) }}</h3>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center">
                            <span class="font-black text-xl">1</span>
                        </div>
                        <div>
                            <p class="text-slate-500">Single Events</p>
                            <h3 class="text-3xl font-black">{{ num(stats.single) }}</h3>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-blue-100 text-blue-600 grid place-items-center">
                            <span class="font-black text-xl">∞</span>
                        </div>
                        <div>
                            <p class="text-slate-500">Recurring Events</p>
                            <h3 class="text-3xl font-black">{{ num(stats.recurring) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <p class="text-xs text-slate-400 mt-1 mb-3">
                        📣 "Feature on Home" puts the event in the app's Home screen Featured carousel — activate this once the organizer has paid for homepage placement.
                    </p>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="py-3">Event</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Revenue</th>
                                    <th>Tickets</th>
                                    <th>Homepage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(regions, rName) in groupedEvents" :key="rName">
                                    <tr>
                                        <td
                                            colspan="9"
                                            @click="toggleGroup('R:' + rName)"
                                            class="cursor-pointer py-2 px-3 font-black text-white bg-gradient-to-r from-slate-800 to-slate-600"
                                        >
                                            {{ openGroups.has('R:' + rName) ? '▾' : '▸' }} 🌎 {{ rName }}
                                            <span class="opacity-60">({{ Object.values(regions).reduce((a: any, b: any) => a + b.length, 0) }})</span>
                                        </td>
                                    </tr>
                                    <template v-if="openGroups.has('R:' + rName)">
                                        <template v-for="(items, cName) in regions" :key="cName">
                                            <tr>
                                                <td
                                                    colspan="9"
                                                    @click="toggleGroup('C:' + cName)"
                                                    class="cursor-pointer py-2 px-6 font-black text-slate-700 bg-slate-100"
                                                >
                                                    {{ openGroups.has('C:' + cName) ? '▾' : '▸' }} {{ cName }}
                                                    <span class="opacity-50">({{ items.length }})</span>
                                                </td>
                                            </tr>
                                            <template v-if="openGroups.has('C:' + cName)">
                                                <tr v-for="e in items" :key="e.id" class="border-t hover:bg-slate-50 transition">
                                                    <td class="py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="h-12 w-12 rounded-2xl bg-slate-100 overflow-hidden flex-shrink-0 grid place-items-center text-xl">
                                                                <img
                                                                    v-if="getEventImage(e.image)"
                                                                    :src="getEventImage(e.image)"
                                                                    class="w-full h-full object-cover"
                                                                    :alt="e.event"
                                                                />
                                                                <div v-else class="w-full h-full bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white font-bold text-lg">
                                                                    {{ e.event?.charAt(0)?.toUpperCase() || '?' }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <b class="block truncate max-w-[200px]">{{ e.event }}</b>
                                                                <p class="text-xs text-slate-500">{{ e.email }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="e.type.toLowerCase() === 'recurring' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'">
                                                            {{ e.type }}
                                                        </span>
                                                    </td>
                                                    <td class="text-sm">{{ e.date }}</td>
                                                    <td class="text-sm">{{ e.time }}</td>
                                                    <td>
                                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="e.status.toLowerCase() === 'live' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600'">
                                                            {{ e.status }}
                                                        </span>
                                                    </td>
                                                    <td class="font-black">{{ fmt(eventMgmtRevenue(e)) }}</td>
                                                    <td class="text-sm font-bold">{{ num(e.ticketsSold) }} / {{ num(e.ticketsTotal) }}</td>
                                                    <td>
                                                        <button
                                                            @click="toggleEventFeatured(e.id)"
                                                            class="rounded-full px-3 py-1.5 text-xs font-black transition"
                                                            :class="e.featuredHome ? 'bg-amber-400 text-black shadow-lg shadow-amber-200' : 'bg-slate-100 text-slate-500'"
                                                        >
                                                            {{ e.featuredHome ? '📣 Featured' : 'Feature on Home' }}
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <div class="flex gap-1">
                                                            <button
                                                                @click="handleEditEvent(e.id)"
                                                                class="rounded-xl bg-slate-100 px-3 py-2 hover:bg-slate-200 transition"
                                                                title="Edit"
                                                            >
                                                                ✎
                                                            </button>
                                                            <!-- <button
                                                                @click="handleEditTicket(e.id)"
                                                                class="rounded-xl bg-slate-100 px-3 py-2 hover:bg-slate-200 transition"
                                                                title="Categories"
                                                            >
                                                                🏷
                                                            </button> -->
                                                            <button
                                                                @click="handleDeleteEvent(e.id)"
                                                                class="rounded-xl bg-slate-100 px-3 py-2 hover:bg-rose-50 hover:text-rose-600 transition"
                                                                title="Delete"
                                                            >
                                                                🗑
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <ConfirmDeleteDialog
                v-model="showDialog"
                :form="deleteForm"
                title="Delete Event"
                description="Are you sure you want to delete this event? This action cannot be undone."
                @submit="deleteEvent"
            />
        </main>

        <Toaster rich-colors position="top-right" />
    </div>
</template>
