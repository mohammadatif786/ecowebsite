<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import CreateEditModal from '@/pages/admin/Commerce/components/CreateEditModal.vue';
import { Head, router } from '@inertiajs/vue3';
import { BadgePercent, Users, TrendingUp, CreditCard, Layers, ArrowUpRight, Plus, X, Check } from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

interface PlanFeatures {
    go_live: boolean
    clubs_restaurants: boolean
    marketplace: boolean
    boost_store: boolean
    boost_events: boolean
    video_voice_calls: boolean
    the_lab: boolean
}

interface PlanPerks {
    unlimited_swipes: boolean
    see_who_liked_you: boolean
    priority_matching_boost: boolean
    advanced_filters: boolean
    read_receipts: boolean
    weekly_profile_boost: boolean
    exclusive_plan_badge: boolean
    priority_support: boolean
}

interface SubscriptionPlan {
    id: number
    name: string
    emoji: string | null
    tagline: string | null
    description: string | null
    price: string
    billing_cycle: 'monthly' | 'yearly' | 'one_time' | 'lifetime'
    duration_days: number
    status: 'active' | 'inactive' | 'draft'
    features: PlanFeatures
    perks: PlanPerks
    stripe_product_id: string
    stripe_price_id: string
    subscriptions_count?: number
}

const props = defineProps<{
    plans: SubscriptionPlan[];
    subscribers?: any[];
    initialCountries?: any[];
    initialUnits?: any[];
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model Helpers
const countriesData = computed(() => props.initialCountries || [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
]);

const unitsData = computed(() => props.initialUnits || [
    { key: 'tickets' }, { key: 'subscriptions' }, { key: 'marketplace' }, { key: 'eats' }, { key: 'merchantPay' }
]);

const subMembers = ref([
    { name: 'Aaliyah Clarke', plan: 'Carnaval', country: 'Bahamas', started: '2026-05-28', renews: '2026-06-28', status: 'Active' },
    { name: 'Marcus Johnson', plan: 'Calor', country: 'Bahamas', started: '2026-05-12', renews: '2026-06-12', status: 'Active' },
    { name: 'Brianna Smith', plan: 'Ritmo', country: 'Jamaica', started: '2026-04-30', renews: '2026-06-30', status: 'Active' },
    { name: 'David Miller', plan: 'El Dorado', country: 'United States', started: '2026-03-09', renews: '2026-06-09', status: 'Active' },
    { name: 'Devon Singh', plan: 'Calor', country: 'Trinidad & Tobago', started: '2026-02-22', renews: '2026-06-22', status: 'Past Due' }
]);

// Colors for Tiers
const TIER_COLORS = ['#0ea5e9', '#f59e0b', '#ec4899', '#16a34a', '#8b5cf6', '#06b6d4', '#ef4444'];
const getPlanColor = (index: number) => TIER_COLORS[index % TIER_COLORS.length];

// Helpers
const formatCurrency = (n: any) => {
    const val = typeof n === 'string' ? parseFloat(n) : n;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
};

const formatLargeCurrency = (n: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
};

const formatNumber = (n: number) => {
    return new Intl.NumberFormat('en-US').format(n);
};

const getIncludedFeatures = (plan: SubscriptionPlan) => {
    const labels: Record<string, string> = {
        go_live: 'Go Live streaming',
        clubs_restaurants: 'Clubs & restaurants nearby',
        marketplace: 'Marketplace buy & sell',
        boost_store: 'Boost store listings',
        boost_events: 'Boost events platform-wide',
        video_voice_calls: 'Video & voice calls',
        the_lab: 'The Lab early access'
    };
    const features = Object.entries(plan.features || {})
        .filter(([_, val]) => val)
        .map(([key, _]) => labels[key] || key);

    // Map description lines as additional features
    if (plan.description) {
        const descLines = plan.description.split('\n').map(l => l.trim()).filter(l => l && l.toLowerCase() !== "what's included");
        features.push(...descLines);
    }
    return features;
};

const getIncludedPerks = (plan: SubscriptionPlan) => {
    const labels: Record<string, string> = {
        unlimited_swipes: 'Unlimited swipes',
        see_who_liked_you: 'See who liked you',
        priority_matching_boost: 'Priority matching boost',
        advanced_filters: 'Advanced filters',
        read_receipts: 'Read receipts',
        weekly_profile_boost: 'Weekly profile boost',
        exclusive_plan_badge: 'Exclusive plan badge',
        priority_support: 'Priority support'
    };
    return Object.entries(plan.perks)
        .filter(([_, val]) => val)
        .map(([key, _]) => labels[key] || key);
};

// Computed Metrics
const totalSubscribers = computed(() => {
    // If backend provides a specific count, use it, otherwise sum up the plans
    const countFromPlans = (props.plans || []).reduce((s, p) => s + (p.subscriptions_count || 0), 0);
    return countFromPlans > 0 ? countFromPlans : (props.subscribers?.length || 0);
});
const mrr = computed(() => (props.plans || []).reduce((s, p) => s + (parseFloat(p.price || '0') * (p.subscriptions_count || 0)), 0));
const paidSubsCount = computed(() => (props.plans || []).filter(p => parseFloat(p.price || '0') > 0).reduce((s, p) => s + (p.subscriptions_count || 0), 0));
const arpu = computed(() => paidSubsCount.value ? mrr.value / paidSubsCount.value : 0);
const activePlansCount = computed(() => (props.plans || []).filter(p => p.status === 'active').length);

const ribbonMetrics = computed(() => {
    const rs = countriesData.value;
    const s = getScale();
    const t = rs.reduce((a, c) => {
        unitsData.value.forEach(u => {
            a.gtv += (Number(c[u.key]) || 0);
        });
        a.users += (Number(c.users) || 0);
        a.merchants += (Number(c.merchants) || 0);
        a.organizers += (Number(c.organizers) || 0);
        return a;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    return {
        gtv: formatLargeCurrency(t.gtv * s),
        linkupRev: formatLargeCurrency(t.gtv * s * 0.12),
        procPool: formatLargeCurrency(t.gtv * s * 0.03),
        netProfit: formatLargeCurrency(t.gtv * s * 0.09),
        users: formatNumber(t.users * s),
        merchants: formatNumber(t.merchants),
        organizers: formatNumber(t.organizers),
        countries: formatNumber(rs.length)
    };
});

const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const subSearch = ref('');
const filteredPlans = computed(() => {
    return (props.plans || []).filter(p => p.name.toLowerCase().includes(subSearch.value.toLowerCase()));
});

// Modal Logic
const openModalState = ref('');
const editingPlan = ref<SubscriptionPlan | null>(null);

const openNewPlanModal = () => {
    editingPlan.value = null;
    openModalState.value = 'open';
};

const openEditPlanModal = (plan: SubscriptionPlan) => {
    editingPlan.value = plan;
    openModalState.value = 'open';
};

const handleRefresh = () => {
    router.reload({ only: ['plans'] });
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const planMetrics = computed(() => {
    const totalSubs = subPlans.value.reduce((s, p) => s + p.subs, 0);
    const mrr = subPlans.value.reduce((s, p) => s + p.price * p.subs, 0);
    const paid = subPlans.value.filter(p => p.price > 0).reduce((s, p) => s + p.subs, 0);
    return {
        totalSubs,
        mrr,
        arpu: paid ? mrr / paid : 0,
        activePlans: subPlans.value.filter(p => p.status === 'Active').length,
        churn: '1.8%'
    };
});

// Mock header metrics
const headerMetrics = computed(() => ({
    gtv: '$14.2M',
    revenue: fmt(planMetrics.value.mrr),
    bank: '$2.1M',
    net: '$420k',
    users: num(planMetrics.value.totalSubs),
    merchants: '12.4k',
    organizers: '2.1k',
    countries: '15'
}));

const subPlanColor = (name: string) => {
    const p = subPlans.value.find(x => x.name === name);
    return p ? p.color : '#64748b';
};

// Modal Logic
const showModal = ref(false);
const editingIdx = ref<number | null>(null);
const modalForm = ref({ name: '', tagline: '', price: 9.99, billing: 'Monthly', subs: 0, features: '' });

const openModal = (index: number | null = null) => {
    editingIdx.value = index;
    if (index !== null) {
        const p = subPlans.value[index];
        modalForm.value = { name: p.name, tagline: p.tagline, price: p.price, billing: p.billing, subs: p.subs, features: (p.features || []).join('\n') };
    } else {
        modalForm.value = { name: '', tagline: '', price: 9.99, billing: 'Monthly', subs: 0, features: '' };
    }
    showModal.value = true;
};

const saveSubPlan = () => {
    const features = modalForm.value.features.split('\n').map(x => x.trim()).filter(x => x);
    if (editingIdx.value !== null) {
        const p = subPlans.value[editingIdx.value];
        Object.assign(p, { ...modalForm.value, features });
    } else {
        const palette = ['#0ea5e9', '#f59e0b', '#ec4899', '#16a34a', '#8b5cf6', '#06b6d4', '#ef4444'];
        subPlans.value.push({ ...modalForm.value, emoji: '⭐', status: 'Active', color: palette[subPlans.value.length % palette.length], popular: false, features });
    }
    showModal.value = false;
};

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Subscription Management" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="subscriptionSuiteCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Subscriptions Command Center" :countries="countriesData" :metrics="ribbonMetrics" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black">Subscription Management</h3>
                        <p class="text-slate-500 font-medium mt-1">Caribbean & Latin America-inspired tiers, feature gates, subscribers & recurring revenue.</p>
                    </div>
                    <button @click="openNewPlanModal" class="rounded-2xl bg-slate-950 text-white px-5 py-2 font-black flex items-center gap-2 shadow-lg shadow-blue-900/20 transition active:scale-95">
                        <Plus class="w-5 h-5" /> New Plan
                    </button>
                </div>

                <!-- Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
                    <div class="card metric dark rounded-3xl p-5">
                        <p class="text-slate-300 font-bold">Subscribers</p>
                        <h3 class="text-4xl font-black mt-1">{{ formatNumber(totalSubscribers) }}</h3>
                        <p class="text-emerald-300 font-bold text-sm">Active members</p>
                    </div>
                    <div class="card metric rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">MRR</p>
                        <h3 class="text-3xl font-black mt-1">{{ formatCurrency(mrr) }}</h3>
                        <p class="text-green-600 font-bold text-sm">Monthly recurring</p>
                    </div>
                    <div class="card metric rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">ARPU</p>
                        <h3 class="text-3xl font-black mt-1">{{ formatCurrency(arpu) }}</h3>
                        <p class="text-sky-500 font-bold text-sm">Avg / paid user</p>
                    </div>
                    <div class="card metric rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">Active Plans</p>
                        <h3 class="text-3xl font-black mt-1">{{ activePlansCount }}</h3>
                        <p class="text-purple-500 font-bold text-sm">Tiers live</p>
                    </div>
                    <div class="card metric rounded-3xl p-5">
                        <p class="text-slate-500 font-bold">Churn Rate</p>
                        <h3 class="text-3xl font-black mt-1">1.8%</h3>
                        <p class="text-rose-500 font-bold text-sm">30-day</p>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <div class="mb-5">
                        <h3 class="text-xl font-black">Subscription Tiers</h3>
                        <p class="text-slate-500">Each tier and exactly what members get. Use <b>Edit</b> to manage what a plan includes.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-stretch">
                        <div v-for="(p, i) in (plans || [])" :key="p.id" class="rounded-3xl border-2 p-5 flex flex-col bg-white" :style="{ borderColor: getPlanColor(i) + '33' }">
                            <div class="flex items-center justify-between">
                                <div class="h-11 w-11 rounded-2xl grid place-items-center text-2xl" :style="{ background: getPlanColor(i) + '1f' }">
                                    {{ p.emoji || '⭐' }}
                                </div>
                                <span v-if="p.status === 'active' && i === 2" class="text-xs font-black px-2 py-1 rounded-full text-white" :style="{ background: getPlanColor(i) }">POPULAR</span>
                            </div>
                            <h4 class="text-xl font-black mt-3">{{ p.name }}</h4>
                            <p class="text-xs text-slate-500 min-h-[32px]">{{ p.tagline || '' }}</p>
                            <div class="mt-2">
                                <span class="text-3xl font-black">{{ parseFloat(p.price) === 0 ? 'Free' : formatCurrency(p.price) }}</span>
                                <span v-if="parseFloat(p.price) !== 0" class="text-slate-400 text-sm font-bold"> /{{ p.billing_cycle }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mb-3">{{ formatNumber(p.subscriptions_count || 0) }} subscribers</div>
                            <ul class="space-y-2 flex-1">
                                <li v-for="feat in getIncludedFeatures(p)" :key="feat" class="flex items-start gap-2 text-sm">
                                    <Check class="w-4 h-4 mt-0.5 shrink-0" :style="{ color: getPlanColor(i) }" />
                                    <span>{{ feat }}</span>
                                </li>
                                <li v-for="perk in getIncludedPerks(p)" :key="perk" class="flex items-start gap-2 text-sm">
                                    <Check class="w-4 h-4 mt-0.5 shrink-0" :style="{ color: getPlanColor(i) }" />
                                    <span>{{ perk }}</span>
                                </li>
                            </ul>
                            <div class="flex gap-2 mt-4">
                                <button @click="openEditPlanModal(p)" class="flex-1 rounded-2xl border border-slate-200 px-3 py-2 font-black text-sm hover:bg-slate-50 transition">Edit</button>
                                <button @click="openEditPlanModal(p)" class="rounded-2xl px-3 py-2 font-black text-white text-sm" :style="{ background: getPlanColor(i) }">+ Element</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <h3 class="text-xl font-black">Plans Ledger</h3>
                        <input v-model="subSearch" class="rounded-2xl border border-slate-200 px-4 py-2 w-full md:w-64" placeholder="Search plans...">
                    </div>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="py-3">Plan</th>
                                    <th>Price</th>
                                    <th>Billing</th>
                                    <th>Status</th>
                                    <th>Subscribers</th>
                                    <th>MRR</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(p, i) in filteredPlans" :key="p.id" class="border-t hover:bg-slate-50 transition-colors">
                                    <td class="py-3 font-black"><span class="mr-2">{{ p.emoji || '⭐' }}</span>{{ p.name }}</td>
                                    <td>{{ parseFloat(p.price) === 0 ? 'Free' : formatCurrency(p.price) }}</td>
                                    <td class="capitalize">{{ p.billing_cycle }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black capitalize"
                                            :class="p.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-50 text-slate-700'">
                                            {{ p.status }}
                                        </span>
                                    </td>
                                    <td>{{ formatNumber(p.subscriptions_count || 0) }}</td>
                                    <td class="font-black">{{ formatCurrency(parseFloat(p.price) * (p.subscriptions_count || 0)) }}</td>
                                    <td>
                                        <button @click="openEditPlanModal(p)" class="text-purple-600 font-black hover:underline">Edit</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card rounded-3xl p-6">
                    <h3 class="text-xl font-black mb-4">Recent Subscribers</h3>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="py-3">Member</th>
                                    <th>Plan</th>
                                    <th>Country</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="m in (subscribers || subMembers)" :key="m.id || m.name" class="border-t hover:bg-slate-50 transition-colors">
                                    <td class="py-3 font-black">
                                        <div class="flex items-center gap-3">
                                            <div v-if="m.initials" class="w-8 h-8 rounded-full flex items-center justify-center text-[10px] text-white font-bold" :style="{ background: m.avatarBg }">
                                                {{ m.initials }}
                                            </div>
                                            <div>
                                                <div class="font-black">{{ m.name }}</div>
                                                <div v-if="m.email" class="text-[10px] text-slate-400 font-medium">{{ m.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="px-2 py-1 rounded-full text-xs font-black" :style="{ background: '#0ea5e91f', color: '#0ea5e9' }">
                                            {{ m.plan }}
                                        </span>
                                    </td>
                                    <td>{{ m.location || m.country }}</td>
                                    <td>{{ m.joined || m.started }}</td>
                                    <td>
                                        <span class="rounded-full px-3 py-1 text-xs font-black" :class="m.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                                            {{ m.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <CreateEditModal :openModal="openModalState" @closeModal="openModalState = ''" @planCreated="handleRefresh" :editingPlan="editingPlan" />
        </main>

        <!-- Plan Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-3 backdrop-blur-md">
            <div class="bg-white rounded-[40px] max-w-lg w-full shadow-2xl overflow-hidden">
                <div class="p-8 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-2xl font-black">{{ editingIdx !== null ? 'Edit' : 'New' }} Plan</h3>
                    <button @click="showModal = false"><X class="w-6 h-6" /></button>
                </div>
                <div class="p-8 space-y-4">
                    <input v-model="modalForm.name" placeholder="Plan Name" class="w-full rounded-xl border border-slate-200 p-4 font-bold">
                    <input v-model="modalForm.tagline" placeholder="Tagline" class="w-full rounded-xl border border-slate-200 p-4 font-bold">
                    <div class="grid grid-cols-2 gap-4">
                        <input v-model.number="modalForm.price" type="number" step="0.01" placeholder="Price" class="w-full rounded-xl border border-slate-200 p-4 font-bold">
                        <select v-model="modalForm.billing" class="w-full rounded-xl border border-slate-200 p-4 font-bold"><option>Monthly</option><option>Yearly</option><option>Free</option></select>
                    </div>
                    <textarea v-model="modalForm.features" rows="4" placeholder="Features (one per line)" class="w-full rounded-xl border border-slate-200 p-4 font-bold"></textarea>
                    <button @click="saveSubPlan" class="w-full rounded-2xl bg-slate-950 text-white py-4 font-black">Save Plan</button>
                </div>
            </div>
        </div>
    </div>
</template>
