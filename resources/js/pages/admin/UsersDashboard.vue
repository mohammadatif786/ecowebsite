<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { Pencil, Trash2, X, Upload } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialUserProfiles: any[];
    initialSubscriptionPlans: any[];
    initialGrowthStats: any[];
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Canvas Refs
const countryChartRef = ref<HTMLCanvasElement | null>(null);
const genderChartRef = ref<HTMLCanvasElement | null>(null);

const SCOTIA_SHARE = 0.4;

const countries = ref(props.initialCountries);
const userProfiles = ref(props.initialUserProfiles);
const subPlans = ref(props.initialSubscriptionPlans);
const growthStats = ref(props.initialGrowthStats);

const LINKUP_NOW = new Date();

// --- Helper Functions ---
const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

const getScale = () => {
    const p = filters.value.period;
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
};

const getFilteredCountries = () => {
    return countries.value.filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const totals = computed(() => {
    const rs = getFilteredCountries();
    const sc = getScale();
    const platformSum =
        rs.reduce(
            (s, c) =>
                s +
                (Number(c.tickets) * 0.065 +
                    Number(c.subscriptions) +
                    Number(c.marketplace) * 0.05 +
                    Number(c.eats) * 0.075 +
                    Number(c.merchantPay) * 0.02 +
                    Number(c.wallet) * 0.025 +
                    Number(c.live) * 0.5 +
                    Number(c.ads) +
                    Number(c.wellness) * 0.0675 +
                    Number(c.cookouts) * 0.0675 +
                    Number(c.linkup360)),
            0,
        ) * sc;
    return {
        users: rs.reduce((s, c) => s + (Number(c.users) || 0), 0) * sc,
        platform: platformSum,
        gross:
            rs.reduce(
                (s, c) =>
                    s +
                    (Number(c.tickets) +
                        Number(c.subscriptions) +
                        Number(c.marketplace) +
                        Number(c.eats) +
                        Number(c.merchantPay) +
                        Number(c.wallet) +
                        Number(c.live) +
                        Number(c.ads) +
                        Number(c.wellness) +
                        Number(c.cookouts) +
                        Number(c.linkup360)),
                0,
            ) * sc,
        bank: rs.reduce((s, c) => s + Number(c.wallet) * 0.0175, 0) * sc,
        cost:
            rs.reduce(
                (s, c) =>
                    s +
                    (Number(c.tickets) * 0.01 +
                        Number(c.subscriptions) * 0.04 +
                        Number(c.marketplace) * 0.01 +
                        Number(c.eats) * 0.025 +
                        Number(c.merchantPay) * 0.007 +
                        Number(c.wallet) * 0.008 +
                        Number(c.live) * 0.08 +
                        Number(c.ads) * 0.12 +
                        Number(c.wellness) * 0.015 +
                        Number(c.cookouts) * 0.015 +
                        Number(c.linkup360) * 0.15),
                0,
            ) * sc,
        merchants: rs.reduce((s, c) => s + (Number(c.merchants) || 0), 0),
        organizers: rs.reduce((s, c) => s + (Number(c.organizers) || 0), 0),
        countries: rs.length,
    };
});

const userRegionOf = (country: string) => countries.value.find((x) => x.country === country)?.region || 'International';
const userAgeBucket = (age: number) => {
    if (age < 25) return '18-24';
    if (age < 35) return '25-34';
    if (age < 45) return '35-44';
    if (age < 55) return '45-54';
    return '55+';
};
const userTimeAgo = (iso: string) => {
    if (!iso) return '—';
    const then = new Date(iso);
    const mins = Math.round((LINKUP_NOW.getTime() - then.getTime()) / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return mins + 'm ago';
    const hrs = Math.round(mins / 60);
    if (hrs < 24) return hrs + 'h ago';
    const days = Math.round(hrs / 24);
    if (days < 7) return days + 'd ago';
    const wks = Math.round(days / 7);
    if (wks < 5) return wks + 'w ago';
    return then.toLocaleDateString();
};

const filteredUserProfiles = computed(() => {
    const rf = filters.value.region;
    const cf = filters.value.country;
    return userProfiles.value.filter((u) => {
        const reg = userRegionOf(u.country);
        const okRegion = rf === 'All' || reg === rf;
        const okCountry = cf === 'All Countries' || u.country === cf;
        return okRegion && okCountry;
    });
});

const demographics = computed(() => {
    const u = filteredUserProfiles.value;
    const n = u.length;
    if (!n) return null;

    const ages = u.map((x) => x.age || 0).sort((a, b) => a - b);
    const avgAge = Math.round(u.reduce((a, x) => a + (x.age || 0), 0) / n);
    const active = u.filter((x) => x.status === 'Active').length;
    const cc: any = {};
    u.forEach((x) => (cc[x.country] = (cc[x.country] || 0) + 1));
    const top = Object.entries(cc).sort((a: any, b: any) => b[1] - a[1])[0];

    const ageOrder = ['18-24', '25-34', '35-44', '45-54', '55+'];
    const buckets: any = {};
    ageOrder.forEach((b) => (buckets[b] = 0));
    u.forEach((x) => buckets[userAgeBucket(x.age || 0)]++);

    const pm: any = {};
    u.forEach((x) => (pm[x.plan] = (pm[x.plan] || 0) + 1));
    const gender: any = {};
    u.forEach((x) => (gender[x.gender] = (gender[x.gender] || 0) + 1));
    const status: any = {};
    u.forEach((x) => (status[x.status] = (status[x.status] || 0) + 1));

    return {
        avgAge,
        ageRange: ages.length ? `Range ${ages[0]}–${ages[ages.length - 1]} yrs` : 'N/A',
        activeRate: Math.round((active / n) * 100) + '%',
        topCountry: top ? top[0] : 'N/A',
        topCountryShare: top ? Math.round((Number(top[1]) / n) * 100) + '% of users in view' : '0%',
        buckets,
        pm,
        gender,
        status,
    };
});

const growthAnalytics = computed(() => {
    const usersCount = totals.value.users || 0;
    const funnel = [
        ['Signups', usersCount, 1],
        ['KYC Verified', usersCount * 0.72, 0.72],
        ['First Transaction', usersCount * 0.58, 0.58],
        ['Subscriber', usersCount * 0.28, 0.28],
    ];

    const cohort = growthStats.value;

    const ltv = subPlans.value
        .filter((p) => p.price > 0)
        .map((p) => ({ name: p.name, value: p.price * (1 / 0.018) }))
        .sort((a, b) => b.value - a.value);

    return { funnel, cohort, ltv };
});

// --- State for Accordions ---
const signupSearch = ref('');
const groupMode = ref('region');
const openGroups = ref<Set<string>>(new Set());

// --- Edit User Modal ---
const showEditModal = ref(false);
const editingUser = ref<any>(null);
const avatarPreview = ref<string | null>(null);
const avatarInput = ref<HTMLInputElement | null>(null);

const editForm = useForm({
    name: '',
    country: '',
    age: 0,
    gender: 'Male',
    kyc_status: 'Pending',
    status: true,
    avatar: null as File | null,
    remove_avatar: false,
    _method: 'POST',
});

const openUserModal = (user: any) => {
    editingUser.value = user;
    editForm.reset();
    editForm.name = user.name;
    editForm.country = user.country;
    editForm.age = user.age;
    editForm.gender = user.gender;
    editForm.kyc_status = user.kyc === 'Verified' ? 'approved' : (user.kyc || 'Pending');
    editForm.status = user.status === 'Active';
    editForm.avatar = null;
    editForm.remove_avatar = false;
    avatarPreview.value = user.avatar || null;
    showEditModal.value = true;
};

const closeUserModal = () => {
    showEditModal.value = false;
    editingUser.value = null;
    avatarPreview.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const handleAvatarUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;
    editForm.avatar = file;
    editForm.remove_avatar = false;
    const reader = new FileReader();
    reader.onload = (event) => {
        avatarPreview.value = event.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const removeAvatar = () => {
    editForm.avatar = null;
    editForm.remove_avatar = true;
    avatarPreview.value = null;
    if (avatarInput.value) avatarInput.value.value = '';
};

const saveUser = () => {
    if (!editingUser.value) return;
    editForm.post(route('admin.users-dashboard.update', editingUser.value.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            const u = userProfiles.value.find((x) => x.id === editingUser.value.id);
            if (u) {
                u.name = editForm.name;
                u.country = editForm.country;
                u.age = editForm.age;
                u.gender = editForm.gender;
                u.kyc = editForm.kyc_status === 'approved' ? 'Verified' : editForm.kyc_status;
                u.status = editForm.status ? 'Active' : 'Inactive';
                if (editForm.remove_avatar) u.avatar = null;
            }
            toast.success('User updated successfully');
            closeUserModal();
        },
        onError: () => {
            toast.error('Failed to update user. Check the highlighted fields.');
        },
    });
};

// --- Delete User ---
const deletingUserId = ref<number | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

const deleteUser = (user: any) => {
    deletingUserId.value = user.id;
    showDeleteDialog.value = true;
};

const confirmDeleteUser = () => {
    if (!deletingUserId.value) return;
    const id = deletingUserId.value;
    deleteForm.delete(route('admin.users-dashboard.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            userProfiles.value = userProfiles.value.filter((u) => u.id !== id);
            toast.success('User deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete user');
        },
        onFinish: () => {
            showDeleteDialog.value = false;
            if (showEditModal.value) closeUserModal();
        },
    });
};

const deleteUserFromModal = () => {
    if (!editingUser.value) return;
    deleteUser(editingUser.value);
};

const toggleGroup = (key: string) => {
    if (openGroups.value.has(key)) openGroups.value.delete(key);
    else openGroups.value.add(key);
};

const expandAll = (open: boolean) => {
    if (open) {
        const all: string[] = [];
        filteredUserProfiles.value.forEach((u) => {
            all.push('R:' + userRegionOf(u.country));
            all.push('C:' + u.country);
            all.push('LD:R:' + userRegionOf(u.country));
            all.push('LD:C:' + u.country);
        });
        openGroups.value = new Set(all);
    } else {
        openGroups.value.clear();
    }
};

const recentSignupsFiltered = computed(() => {
    const q = signupSearch.value.toLowerCase();
    let list = filteredUserProfiles.value;

    if (q) {
        list = list.filter((x) => (x.name + ' ' + x.country + ' ' + x.plan).toLowerCase().includes(q));
    }

    return list.sort((a, b) => new Date(b.joined).getTime() - new Date(a.joined).getTime());
});

const groupedSignups = computed(() => {
    const list = recentSignupsFiltered.value;
    if (groupMode.value === 'flat') return { type: 'flat', data: list };

    const rm: any = {};
    list.forEach((u) => {
        const r = userRegionOf(u.country);
        const c = u.country;
        if (!rm[r]) rm[r] = { count: 0, countries: {} };
        if (!rm[r].countries[c]) rm[r].countries[c] = [];
        rm[r].countries[c].push(u);
        rm[r].count++;
    });

    if (groupMode.value === 'country') {
        const countriesMap: any = {};
        Object.values(rm).forEach((r: any) => Object.assign(countriesMap, r.countries));
        return { type: 'country', data: countriesMap };
    }

    return { type: 'region', data: rm };
});

const ledgerGrouped = computed(() => {
    const rm: any = {};
    filteredUserProfiles.value.forEach((u) => {
        const r = userRegionOf(u.country);
        const c = u.country;
        if (!rm[r]) rm[r] = { count: 0, countries: {} };
        if (!rm[r].countries[c]) rm[r].countries[c] = [];
        rm[r].countries[c].push(u);
        rm[r].count++;
    });
    return rm;
});

// --- Charts ---
const charts = ref<any>({});
const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const countryCtx = countryChartRef.value;
    if (countryCtx) {
        const rs = getFilteredCountries();
        const maxU = Math.max(...rs.map((c) => Number(c.users) || 0), 1);
        charts.value.country = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: rs.map((c) => c.country),
                datasets: [
                    {
                        label: 'Users',
                        data: rs.map((c) => (Number(c.users) || 0) * getScale()),
                        backgroundColor: rs.map((c) => (Number(c.users) === maxU ? '#facc15' : '#28A8FF')),
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } },
            },
        });
    }

    const genderCtx = genderChartRef.value;
    if (genderCtx) {
        const u = filteredUserProfiles.value;
        const g: any = { Female: 0, Male: 0 };
        u.forEach((x) => (g[x.gender] = (g[x.gender] || 0) + 1));

        charts.value.gender = new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Female', 'Male'],
                datasets: [{ data: [g['Female'] || 0, g['Male'] || 0], backgroundColor: ['#28A8FF', '#00C853'] }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }
};

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    renderAll();
};

const renderAll = async () => {
    await nextTick();
    const t = totals.value;
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

    renderCharts();
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});

const userAvatar = (name: string, size = 38) => {
    const parts = (name || '?').trim().split(/\s+/);
    const ini = ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '');
    let h = 0;
    for (let i = 0; i < (name || '').length; i++) {
        h = (h * 31 + name.charCodeAt(i)) >>> 0;
    }
    const hues = [h % 360, (h * 7) % 360];
    const bg = `linear-gradient(135deg,hsl(${hues[0]},70%,55%),hsl(${hues[1]},70%,45%))`;
    return `<div style="width:${size}px;height:${size}px;border-radius:9999px;background:${bg};display:grid;place-items:center;color:#fff;font-weight:900;font-size:${size * 0.38}px;box-shadow:0 2px 6px rgba(0,0,0,.15);flex-shrink:0">${ini.toUpperCase()}</div>`;
};
</script>

<template>
    <Head title="Users Dashboard" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="users" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Users Dashboard" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <!-- User View Stats Row 1 -->
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="card metric dark rounded-3xl p-5">
                        <p class="font-bold text-slate-300">Users</p>
                        <h3 class="mt-2 text-5xl font-black">{{ num(totals.users) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Verified</p>
                        <h3 class="text-4xl font-black">{{ num(totals.users * 0.72) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Premium</p>
                        <h3 class="text-4xl font-black">{{ num(totals.users * 0.28) }}</h3>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">RPU</p>
                        <h3 class="text-4xl font-black">{{ fmt(totals.users ? totals.platform / totals.users : 0) }}</h3>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl p-6 2xl:col-span-2">
                        <h3 class="mb-4 text-xl font-black">Users by Country</h3>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="countryChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Gender</h3>
                        <div class="relative h-[300px] w-full">
                            <canvas ref="genderChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- User Demographics Stats Row 2 -->
                <div v-if="demographics" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Average Age</p>
                        <h3 class="text-4xl font-black">{{ demographics.avgAge }}</h3>
                        <p class="text-xs text-slate-500">{{ demographics.ageRange }}</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Sample Profiles</p>
                        <h3 class="text-4xl font-black">{{ filteredUserProfiles.length }}</h3>
                        <p class="text-xs text-slate-500">Profiles in current view</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Active Rate</p>
                        <h3 class="text-4xl font-black">{{ demographics.activeRate }}</h3>
                        <p class="text-xs text-slate-500">Active vs total accounts</p>
                    </div>
                    <div class="card rounded-3xl p-5">
                        <p class="font-bold text-slate-500">Top Country</p>
                        <h3 class="text-2xl font-black">{{ demographics.topCountry }}</h3>
                        <p class="text-xs text-slate-500">{{ demographics.topCountryShare }}</p>
                    </div>
                </div>

                <!-- Demographics Distribution -->
                <div v-if="demographics" class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Age Range Distribution</h3>
                        <div class="space-y-3">
                            <div v-for="(val, key) in demographics.buckets" :key="key">
                                <div class="mb-1 flex justify-between text-sm font-bold">
                                    <span>{{ key }}</span>
                                    <span>{{ val }} ({{ Math.round((val / filteredUserProfiles.length) * 100) }}%)</span>
                                </div>
                                <div class="h-3 rounded-full bg-slate-100">
                                    <div
                                        class="h-3 rounded-full bg-purple-500"
                                        :style="{ width: (val / Math.max(...(Object.values(demographics.buckets) as number[]), 1)) * 100 + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Plan Mix</h3>
                        <div class="space-y-3">
                            <div v-for="(val, key) in demographics.pm" :key="key" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                <b>{{ key }}</b
                                ><span>{{ val }} • {{ Math.round((val / filteredUserProfiles.length) * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Gender & Status</h3>
                        <div class="space-y-3">
                            <div v-for="(val, key) in demographics.gender" :key="'g' + key" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                <b>{{ key }}</b
                                ><span>{{ val }} • {{ Math.round((val / filteredUserProfiles.length) * 100) }}%</span>
                            </div>
                            <div class="my-2 border-t border-slate-100"></div>
                            <div v-for="(val, key) in demographics.status" :key="'s' + key" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                <b>{{ key }}</b
                                ><span>{{ val }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Signups -->
                <div class="card rounded-3xl p-6">
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-xl font-black">Recent Signups</h3>
                            <p class="text-slate-500">Grouped by region & country — click a group to expand.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <input
                                v-model="signupSearch"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-2 md:w-64"
                                placeholder="Search name, country, plan..."
                            />
                            <select v-model="groupMode" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold">
                                <option value="region">Group: Region → Country</option>
                                <option value="country">Group: Country</option>
                                <option value="flat">Flat list</option>
                            </select>
                            <button @click="expandAll(true)" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-black">
                                Expand all
                            </button>
                            <button @click="expandAll(false)" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-black">
                                Collapse all
                            </button>
                        </div>
                    </div>

                    <div id="recentSignupsTable" class="space-y-2">
                        <!-- Flat List -->
                        <div v-if="groupedSignups.type === 'flat'">
                            <div
                                v-for="u in groupedSignups.data"
                                :key="u.id"
                                class="flex items-center gap-3 border-t border-slate-100 px-3 py-2.5 hover:bg-slate-50"
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <div v-html="userAvatar(u.name)"></div>
                                    <div class="min-w-0">
                                        <div class="truncate font-black">{{ u.name }}</div>
                                        <div class="text-xs text-slate-400">{{ u.country }} • {{ u.age }} • {{ u.gender }}</div>
                                    </div>
                                </div>
                                <span class="hidden w-20 text-sm font-bold text-slate-600 md:inline">{{ u.plan }}</span>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-black"
                                    :class="u.kyc === 'Verified' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
                                    >{{ u.kyc }}</span
                                >
                                <span class="hidden w-24 text-right text-xs text-slate-500 lg:block">{{ userTimeAgo(u.joined) }}</span>
                                <div class="flex gap-1">
                                    <button class="rounded-xl bg-slate-100 px-2.5 py-2" title="Edit" @click="openUserModal(u)">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button class="rounded-xl bg-slate-100 px-2.5 py-2 text-slate-500" title="Remove" @click="deleteUser(u)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Region Grouped -->
                        <div v-else-if="groupedSignups.type === 'region'">
                            <div
                                v-for="(regData, region) in groupedSignups.data"
                                :key="region"
                                class="overflow-hidden rounded-2xl border border-slate-100"
                            >
                                <button
                                    @click="toggleGroup('R:' + region)"
                                    class="flex w-full items-center justify-between bg-gradient-to-r from-slate-800 to-slate-600 px-4 py-3 font-black text-white"
                                >
                                    <span
                                        >{{ openGroups.has('R:' + region) ? '▾' : '▸' }} 🌎 {{ region }}
                                        <span class="font-bold opacity-60">({{ regData.count }})</span></span
                                    >
                                </button>
                                <div v-show="openGroups.has('R:' + region)" class="space-y-2 p-2">
                                    <div
                                        v-for="(cUsers, country) in regData.countries"
                                        :key="country"
                                        class="overflow-hidden rounded-2xl border border-slate-100"
                                    >
                                        <button
                                            @click="toggleGroup('C:' + country)"
                                            class="flex w-full items-center justify-between bg-slate-100 px-4 py-2.5 font-black text-slate-700"
                                        >
                                            <span
                                                >{{ openGroups.has('C:' + country) ? '▾' : '▸' }} {{ country }}
                                                <span class="opacity-50">({{ cUsers.length }})</span></span
                                            >
                                        </button>
                                        <div v-show="openGroups.has('C:' + country)">
                                            <div
                                                v-for="u in cUsers"
                                                :key="u.id"
                                                class="flex items-center gap-3 border-t border-slate-100 px-3 py-2.5 hover:bg-slate-50"
                                            >
                                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                                    <div v-html="userAvatar(u.name)"></div>
                                                    <div class="min-w-0">
                                                        <div class="truncate font-black">{{ u.name }}</div>
                                                        <div class="text-xs text-slate-400">{{ u.age }} • {{ u.gender }}</div>
                                                    </div>
                                                </div>
                                                <span
                                                    class="rounded-full px-3 py-1 text-xs font-black"
                                                    :class="u.kyc === 'Verified' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'"
                                                    >{{ u.kyc }}</span
                                                >
                                                <span class="w-24 text-right text-xs text-slate-500">{{ userTimeAgo(u.joined) }}</span>
                                                <div class="flex gap-1">
                                                    <button class="rounded-xl bg-slate-100 px-2.5 py-2" title="Edit" @click="openUserModal(u)">
                                                        <Pencil class="h-3.5 w-3.5" />
                                                    </button>
                                                    <button class="rounded-xl bg-slate-100 px-2.5 py-2 text-slate-500" title="Remove" @click="deleteUser(u)">
                                                        <Trash2 class="h-3.5 w-3.5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Growth Analytics -->
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-1 text-xl font-black">Growth Analytics</h3>
                    <p class="mb-4 text-slate-500">Conversion funnel, cohort retention, and lifetime value by plan.</p>
                    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                        <div>
                            <h4 class="mb-3 font-black">Conversion Funnel</h4>
                            <div class="space-y-3">
                                <div v-for="f in growthAnalytics.funnel" :key="f[0]">
                                    <div class="mb-1 flex justify-between text-sm font-bold">
                                        <span>{{ f[0] }}</span>
                                        <span>{{ num(f[1] as number) }} ({{ (Number(f[2]) * 100).toFixed(0) }}%)</span>
                                    </div>
                                    <div class="h-3 rounded-full bg-slate-100">
                                        <div
                                            class="h-3 rounded-full bg-sky-500"
                                            :style="{ width: (Number(f[1]) / (growthAnalytics.funnel[0][1] as number)) * 100 + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-3 font-black">
                                Cohort Retention <span class="text-xs font-normal text-slate-400">(by signup month)</span>
                            </h4>
                            <div class="space-y-2">
                                <div v-for="c in growthAnalytics.cohort" :key="c.month" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                    <b>{{ c.month }}</b>
                                    <span>{{ c.count }} joined · {{ c.active }}% active</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-3 font-black">Lifetime Value by Plan</h4>
                            <div class="space-y-2">
                                <div v-for="l in growthAnalytics.ltv" :key="l.name" class="flex justify-between rounded-2xl bg-slate-50 p-3">
                                    <b>{{ l.name }}</b>
                                    <span class="font-black">{{ fmt(l.value) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Ledger -->
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">User Ledger</h3>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3">User</th>
                                    <th>Country</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                    <th>KYC</th>
                                    <th>Plan</th>
                                    <th>RPU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(regData, region) in ledgerGrouped" :key="'ld' + region">
                                    <tr>
                                        <td
                                            colspan="8"
                                            @click="toggleGroup('LD:R:' + region)"
                                            class="cursor-pointer bg-gradient-to-r from-slate-800 to-slate-600 px-3 py-2 font-black text-white"
                                        >
                                            {{ openGroups.has('LD:R:' + region) ? '▾' : '▸' }} 🌎 {{ region }}
                                            <span class="opacity-60">({{ regData.count }})</span>
                                        </td>
                                    </tr>
                                    <template v-if="openGroups.has('LD:R:' + region)">
                                        <template v-for="(cUsers, country) in regData.countries" :key="'ld' + country">
                                            <tr>
                                                <td
                                                    colspan="8"
                                                    @click="toggleGroup('LD:C:' + country)"
                                                    class="cursor-pointer bg-slate-100 px-6 py-2 font-black text-slate-700"
                                                >
                                                    {{ openGroups.has('LD:C:' + country) ? '▾' : '▸' }} {{ country }}
                                                    <span class="opacity-50">({{ cUsers.length }})</span>
                                                </td>
                                            </tr>
                                            <template v-if="openGroups.has('LD:C:' + country)">
                                                <tr v-for="u in cUsers" :key="u.id" class="border-t">
                                                    <td class="py-3 font-black">
                                                        <div class="flex items-center gap-2">
                                                            <div v-html="userAvatar(u.name, 28)"></div>
                                                            <span>{{ u.name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ u.country }}</td>
                                                    <td>{{ u.age }}</td>
                                                    <td>{{ u.gender }}</td>
                                                    <td>{{ u.status }}</td>
                                                    <td>{{ u.kyc }}</td>
                                                    <td>{{ u.plan }}</td>
                                                    <td>
                                                        <div class="flex items-center gap-2">
                                                            <span>{{ fmt(u.rpu) }}</span>
                                                            <button class="rounded-lg bg-slate-100 px-2 py-1" title="Edit" @click="openUserModal(u)">
                                                                <Pencil class="h-3.5 w-3.5" />
                                                            </button>
                                                            <button
                                                                class="rounded-lg bg-slate-100 px-2 py-1 text-slate-500"
                                                                title="Remove"
                                                                @click="deleteUser(u)"
                                                            >
                                                                <Trash2 class="h-3.5 w-3.5" />
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
        </main>

        <!-- Edit User Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm">
            <div class="my-8 w-full max-w-md overflow-hidden rounded-[28px] bg-white shadow-2xl">
                <div class="flex items-start justify-between bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">Edit User</h3>
                        <p class="text-sm text-indigo-100">Update profile, photo &amp; status</p>
                    </div>
                    <button @click="closeUserModal" class="grid h-9 w-9 place-items-center rounded-xl bg-white/20 hover:bg-white/30 transition">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-5 p-6">
                    <div class="flex items-center gap-3">
                        <div v-if="avatarPreview" class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-full">
                            <img :src="avatarPreview" class="h-full w-full object-cover" alt="Avatar preview" />
                        </div>
                        <div v-else v-html="userAvatar(editForm.name, 64)"></div>
                        <button @click="avatarInput?.click()" type="button" class="flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-2.5 font-bold text-slate-700 hover:bg-slate-200 transition">
                            <Upload class="h-4 w-4" /> Upload Photo
                        </button>
                        <button v-if="avatarPreview" @click="removeAvatar" type="button" class="font-bold text-slate-400 hover:text-rose-500 transition">
                            Remove
                        </button>
                        <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="handleAvatarUpload" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700">Full Name</label>
                        <input v-model="editForm.name" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-medium outline-none focus:ring-4 focus:ring-purple-50 transition" />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs font-bold text-rose-500">{{ editForm.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Country</label>
                            <input v-model="editForm.country" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-medium outline-none focus:ring-4 focus:ring-purple-50 transition" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Age</label>
                            <input v-model.number="editForm.age" type="number" min="0" max="120" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-medium outline-none focus:ring-4 focus:ring-purple-50 transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Gender</label>
                            <select v-model="editForm.gender" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-medium bg-white outline-none focus:ring-4 focus:ring-purple-50 transition">
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Plan</label>
                            <select disabled :value="editingUser?.plan" title="Manage plan changes from Commerce → Subscriptions" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-medium text-slate-400 outline-none">
                                <option>{{ editingUser?.plan }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">KYC</label>
                            <select v-model="editForm.kyc_status" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-medium bg-white outline-none focus:ring-4 focus:ring-purple-50 transition">
                                <option value="Pending">Pending</option>
                                <option value="approved">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Status</label>
                            <select v-model="editForm.status" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-medium bg-white outline-none focus:ring-4 focus:ring-purple-50 transition">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 p-6">
                    <button @click="deleteUserFromModal" type="button" class="flex items-center gap-2 font-black text-rose-500 hover:text-rose-600 transition">
                        <Trash2 class="h-4 w-4" /> Delete User
                    </button>
                    <div class="flex gap-3">
                        <button @click="closeUserModal" type="button" class="rounded-2xl bg-slate-100 px-5 py-2.5 font-black text-slate-600 hover:bg-slate-200 transition">
                            Cancel
                        </button>
                        <button @click="saveUser" :disabled="editForm.processing" class="rounded-2xl bg-indigo-600 px-6 py-2.5 font-black text-white hover:bg-indigo-700 transition disabled:opacity-50">
                            {{ editForm.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDeleteDialog
            v-model="showDeleteDialog"
            :form="deleteForm"
            title="Delete User"
            description="Are you sure you want to remove this user? This action cannot be undone."
            @submit="confirmDeleteUser"
        />

        <Toaster rich-colors position="top-right" />
    </div>
</template>
