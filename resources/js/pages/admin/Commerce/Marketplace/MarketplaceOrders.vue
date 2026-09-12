<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ShoppingCart, Search, FileText, Download, Filter, MapPin, ChevronDown, ChevronRight, Globe2 } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    orders: {
        data: any[];
        meta: any;
    };
    filters: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = ref(props.initialCountries);

const search = ref(props.filters.search || '');
watch(search, () => {
    debounceSearch();
});

let debounceTimeout: any = null;
const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.commerce.marketplace.orders'), {
            search: search.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
};

const collapsedGroups = ref<Record<string, boolean>>({});

const toggleGroup = (key: string) => {
    collapsedGroups.value[key] = !collapsedGroups.value[key];
};

const isCollapsed = (key: string) => !!collapsedGroups.value[key];

const groupedOrders = computed(() => {
    const groups: any = {};
    props.orders.data.forEach(o => {
        if (!groups[o.region]) {
            groups[o.region] = {
                name: o.region,
                count: 0,
                countries: {}
            };
        }
        groups[o.region].count++;

        if (!groups[o.region].countries[o.country]) {
            groups[o.region].countries[o.country] = {
                name: o.country,
                count: 0,
                items: []
            };
        }
        groups[o.region].countries[o.country].count++;
        groups[o.region].countries[o.country].items.push(o);
    });
    return groups;
});

const handleFilterChange = (newFilters: any) => { renderAll(); };

const getStatusClass = (s: string) => {
    const m: any = {
        'Paid': 'bg-slate-50 border text-slate-700',
        'Delivered': 'bg-green-50 text-green-700',
        'Buyer Received': 'bg-sky-50 text-sky-700',
        'Shipped': 'bg-indigo-50 text-indigo-700',
        'Disputed': 'bg-amber-50 text-amber-700',
        'Refunded': 'bg-rose-50 text-rose-700'
    };
    return m[s] || 'bg-slate-50 border';
};

const getEscrowClass = (e: string) => {
    const m: any = { 'Held': 'bg-amber-50 text-amber-700', 'Released': 'bg-green-50 text-green-700', 'Refunded': 'bg-rose-50 text-rose-700' };
    return m[e] || 'bg-slate-50 border';
};

const formatCurrency = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(n);
const formatNumber = (n: number) => new Intl.NumberFormat('en-US').format(n);

const ribbonMetrics = computed(() => {
    const rs = countries.value;
    const t = rs.reduce((a, c) => {
        props.initialUnits.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
        a.users += (Number(c.users) || 0);
        a.merchants += (Number(c.merchants) || 0);
        a.organizers += (Number(c.organizers) || 0);
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    return {
        gtv: formatCurrency(t.gtv),
        linkupRev: formatCurrency(t.gtv * 0.12),
        procPool: formatCurrency(t.gtv * 0.03),
        netProfit: formatCurrency(t.gtv * 0.09),
        users: formatNumber(t.users),
        merchants: formatNumber(t.merchants),
        organizers: formatNumber(t.organizers),
        countries: formatNumber(rs.length)
    };
});

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const renderAll = async () => {
    for (let i = 0; i < 3; i++) {
        await nextTick();
        const rs = countries.value;
        const t = rs.reduce((a, c) => {
            props.initialUnits.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
            return a;
        }, { gtv: 0 });

        const set = (id: string, v: string) => {
            const e = document.getElementById(id);
            if (e) { e.textContent = v; return true; }
            return false;
        };
        set('sideGTV', fmt(t.gtv));
        if (set('rGTV', fmt(t.gtv))) break;
        await new Promise(r => setTimeout(r, 100));
    }
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Marketplace Orders" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceOrdersCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Marketplace Orders" :countries="countries" :metrics="ribbonMetrics" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3"><ShoppingCart class="w-8 h-8" /> Marketplace Orders</h3>
                        <p class="text-slate-500 font-medium mt-1">Track order status, payment, delivery, buyer confirmation and escrow.</p>
                    </div>
                    <button class="rounded-2xl bg-slate-950 text-white px-6 py-3.5 font-black flex items-center gap-2 shadow-lg shadow-blue-900/10 transition active:scale-95">
                        <Download class="w-5 h-5" /> Export CSV
                    </button>
                </div>

                <div class="card rounded-3xl bg-white border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input v-model="search" type="text" placeholder="Search order #, customer, seller, product..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-50 border-transparent focus:bg-white focus:border-sky-500 font-bold text-sm" />
                        </div>
                        <button class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 font-black flex items-center gap-2 transition hover:bg-slate-50">
                            <Filter class="w-4 h-4" /> Filters
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-separate border-spacing-0">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-4 border-b border-slate-100">Order #</th>
                                    <th class="px-6 py-4 border-b border-slate-100">Customer</th>
                                    <th class="px-6 py-4 border-b border-slate-100">Seller</th>
                                    <th class="px-6 py-4 border-b border-slate-100">Total</th>
                                    <th class="px-6 py-4 border-b border-slate-100">Payment</th>
                                    <th class="px-6 py-4 border-b border-slate-100">Status</th>
                                    <th class="px-6 py-4 border-b border-slate-100">Escrow</th>
                                    <th class="px-6 py-4 border-b border-slate-100 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <template v-for="(region, rName) in groupedOrders" :key="rName">
                                    <!-- Region Header -->
                                    <tr @click="toggleGroup(rName)" class="bg-[#1e293b] text-white cursor-pointer select-none group">
                                        <td colspan="8" class="px-4 py-3">
                                            <div class="flex items-center gap-2 font-black">
                                                <component :is="isCollapsed(rName) ? ChevronRight : ChevronDown" class="w-4 h-4" />
                                                <Globe2 class="w-4 h-4 text-sky-400" />
                                                {{ rName }} <span class="text-slate-400 ml-1">({{ region.count }})</span>
                                            </div>
                                        </td>
                                    </tr>

                                    <template v-if="!isCollapsed(rName)">
                                        <template v-for="(country, cName) in region.countries" :key="cName">
                                            <!-- Country Header -->
                                            <tr @click="toggleGroup(rName + cName)" class="bg-slate-50/50 cursor-pointer select-none border-b border-slate-100">
                                                <td colspan="8" class="px-10 py-2.5">
                                                    <div class="flex items-center gap-2 font-black text-slate-700">
                                                        <component :is="isCollapsed(rName + cName) ? ChevronRight : ChevronDown" class="w-3.5 h-3.5 text-slate-400" />
                                                        {{ cName }} <span class="text-slate-400 ml-1 font-bold">({{ country.count }})</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Order Rows -->
                                            <template v-if="!isCollapsed(rName + cName)">
                                                <tr v-for="o in country.items" :key="o.id" class="hover:bg-slate-50 transition group border-b border-slate-100/50">
                                                    <td class="px-6 py-5">
                                                        <div class="font-black text-slate-900">{{ o.id }}</div>
                                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter flex items-center gap-1 mt-0.5"><MapPin class="w-2.5 h-2.5" /> {{ o.city }}</div>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <div class="font-bold text-slate-800">{{ o.customer }}</div>
                                                        <div class="text-[10px] text-slate-400 font-medium">{{ o.email }}</div>
                                                    </td>
                                                    <td class="px-6 py-5 font-bold text-slate-700">{{ o.seller }}</td>
                                                    <td class="px-6 py-5 font-black text-slate-900">${{ o.total.toFixed(2) }}</td>
                                                    <td class="px-6 py-5">
                                                        <span class="font-bold text-slate-600">{{ o.payment }}</span>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <span :class="getStatusClass(o.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                                            {{ o.status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <span :class="getEscrowClass(o.escrow)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                                            {{ o.escrow }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-5 text-right">
                                                        <Link :href="route('admin.commerce.marketplace.orders.show', o.raw_id)" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 font-black text-xs hover:bg-slate-950 hover:text-white transition shadow-sm">
                                                            View
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </template>
                                </template>
                                <tr v-if="!orders.data.length">
                                    <td colspan="8" class="px-6 py-20 text-center">
                                        <div class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                            <ShoppingCart class="w-8 h-8" />
                                        </div>
                                        <h4 class="text-lg font-black text-slate-900">No orders found</h4>
                                        <p class="text-slate-500 font-medium text-sm mt-1">Try adjusting your filters or search query.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.data.length" class="p-6 border-t border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                            Showing {{ orders.data.length }} of {{ orders.meta.total }} orders
                        </p>
                        <div class="flex items-center gap-2">
                            <Link v-for="link in orders.meta.links" :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="h-10 px-4 rounded-xl flex items-center text-xs font-black uppercase tracking-widest transition"
                                :class="[
                                    link.active ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
