<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { BadgePercent, Calendar, Check, CircleCheck, CircleX, FileText, Plus, Ticket, TicketPercent, X, Pencil, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialCoupons: any[];
    events: any[];
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
const coupons = ref([...props.initialCoupons]);
const couponSearch = ref('');
const couponStatusFilter = ref('All Statuses');

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

// Coupon Specific Logic
const filteredCoupons = computed(() => {
    const q = couponSearch.value.toLowerCase().trim();
    const st = couponStatusFilter.value;
    return coupons.value.filter(c => {
        const hay = [c.name, c.code, c.event, c.discount, c.discountType, c.status, c.description].join(' ').toLowerCase();
        return (!q || hay.includes(q)) && (st === 'All Statuses' || c.status === st);
    });
});

const stats = computed(() => {
    const all = coupons.value;
    return {
        total: all.length,
        active: all.filter(c => c.status === 'Active').length,
        inactive: all.filter(c => c.status !== 'Active').length,
        linkedEvents: new Set(all.map(c => c.link_up_event_id).filter(Boolean)).size,
        percentCount: all.filter(c => c.discount_type === 'percentage').length,
        fixedCount: all.filter(c => c.discount_type === 'amount' || c.discount_type === 'free').length
    };
});

// Modal State
const showModal = ref(false);
const editingCoupon = ref<any>(null);

const form = useForm({
    id: 0,
    title: '',
    code: '',
    link_up_event_id: null as number | null,
    discount_type: 'percentage',
    discount: '',
    expiry_date: '',
    usage_limit: 100,
    uses: 0,
    status: 1,
    description: '',
    _method: 'POST'
});

const openModal = (id: number | null = null) => {
    if (id) {
        const c = coupons.value.find(x => x.id === id);
        if (c) {
            editingCoupon.value = c;
            form.id = c.id;
            form.title = c.title;
            form.code = c.code;
            form.link_up_event_id = c.link_up_event_id;
            form.discount_type = c.discount_type;
            form.discount = c.discount;
            form.expiry_date = c.expiry_date || '';
            form.usage_limit = c.usage_limit;
            form.uses = c.uses;
            form.status = c.raw_status;
            form.description = c.description;
            form._method = 'PUT';
        }
    } else {
        editingCoupon.value = null;
        form.reset();
        form.id = 0;
        form.status = 1;
        form.discount_type = 'percentage';
        form._method = 'POST';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCoupon.value = null;
    form.reset();
    form.clearErrors();
};

const saveCoupon = () => {
    const isUpdate = form.id > 0;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success(isUpdate ? 'Coupon updated successfully' : 'Coupon created successfully');
        },
        onError: () => {
            toast.error(isUpdate ? 'Failed to update coupon' : 'Failed to create coupon');
        },
    };

    if (isUpdate) {
        form.post(route('admin.coupons.update', form.id), options);
    } else {
        form.post(route('admin.coupons.store'), options);
    }
};

const toggleStatus = (c: any) => {
    const newStatus = c.raw_status === 1 ? 0 : 1;

    // We must pass ALL required fields from CouponRequest to pass validation
    // Fallbacks are provided for required fields to prevent silent failures
    const payload = {
        title: c.title || c.name || 'Untitled Coupon',
        code: c.code || 'NOCODE',
        link_up_event_id: c.link_up_event_id || 0,
        discount_type: (c.discount_type || 'percentage').toLowerCase(),
        discount: c.discount || 0,
        expiry_date: c.expiry_date || new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().split('T')[0],
        usage_limit: c.usage_limit || c.limit || 100,
        description: c.description || '—',
        status: newStatus,
        _method: 'PUT'
    };

    router.post(route('admin.coupons.update', c.id), payload, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Coupon status updated successfully');
        },
        onError: (errors) => {
            console.error('Failed to update status:', errors);
            toast.error('Could not update status. Please check if all required fields are set for this coupon.');
        }
    });
};

const deletingCouponId = ref<number | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

const deleteCoupon = (id: number) => {
    deletingCouponId.value = id;
    showDeleteDialog.value = true;
};

function confirmDeleteCoupon() {
    if (!deletingCouponId.value) return;
    const url = route('admin.coupons.destroy', deletingCouponId.value);
    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Coupon deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete coupon');
        },
        onFinish: () => (showDeleteDialog.value = false),
    });
}

// Sync ref when props update from backend
watch(() => props.initialCoupons, (newVal) => {
    coupons.value = [...newVal];
}, { deep: true });

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
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Discount Coupons" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eventCouponsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Discount Coupons" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-purple-600">Discount Coupons</h3>
                        <p class="text-slate-500">Add, edit, and manage coupon codes connected to events.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <input v-model="couponSearch" class="rounded-2xl border border-slate-200 px-4 py-2 w-full xl:w-96" placeholder="Search coupons, code...">
                        <select v-model="couponStatusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold bg-white">
                            <option>All Statuses</option>
                            <option>Active</option>
                            <option>Inactive</option>
                            <option>Expired</option>
                        </select>
                        <button @click="openModal()" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-5 py-2 font-black shadow-lg shadow-purple-200 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                            <Plus class="h-5 w-5" /> Add Coupon
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><TicketPercent class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Total Coupons</p><h3 class="text-4xl font-black">{{ num(stats.total) }}</h3><p class="text-xs text-slate-500">Coupons in the system</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-green-100 text-green-600 grid place-items-center"><CircleCheck class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Active Coupons</p><h3 class="text-4xl font-black">{{ num(stats.active) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-slate-100 text-slate-600 grid place-items-center"><CircleX class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Inactive / Expired</p><h3 class="text-4xl font-black">{{ num(stats.inactive) }}</h3></div>
                    </div>

                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-sky-100 text-sky-600 grid place-items-center"><Calendar class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Linked Events</p><h3 class="text-4xl font-black">{{ num(stats.linkedEvents) }}</h3><p class="text-xs text-slate-500">Events using coupons</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center"><BadgePercent class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Percentage Based</p><h3 class="text-4xl font-black">{{ num(stats.percentCount) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-blue-100 text-blue-600 grid place-items-center"><Ticket class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Fixed / Free</p><h3 class="text-4xl font-black">{{ num(stats.fixedCount) }}</h3></div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6 shadow-sm">
                    <div class="mb-8">
                        <h3 class="text-2xl font-black">Coupon List</h3>
                        <p class="text-slate-500">Create and edit event coupons. No sponsor/coupon value tracking is used here.</p>
                    </div>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                                <tr>
                                    <th class="py-4 px-6">Coupon</th>
                                    <th>Code</th>
                                    <th>Discount</th>
                                    <th>Expiry</th>
                                    <th>Event</th>
                                    <th>Uses</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in filteredCoupons" :key="c.id" class="border-t border-slate-100 align-top hover:bg-slate-50 transition">
                                    <td class="py-4 px-6">
                                        <div>
                                            <b class="text-slate-800 block text-lg leading-tight">{{ c.title }}</b>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-1 max-w-[200px]">{{ c.description }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4 font-black text-slate-900 tracking-wider">{{ c.code }}</td>
                                    <td class="py-4">
                                        <span class="text-slate-500 text-sm capitalize">{{ c.discount_type }} • </span>
                                        <b class="text-lg text-slate-800">{{ c.discount }}</b>
                                    </td>
                                    <td class="py-4 text-sm font-medium text-slate-600">{{ c.expiry }}</td>
                                    <td class="py-4">
                                        <span v-if="c.event && c.event !== '—'" class="font-black text-purple-600 text-sm">
                                            {{ c.event }}
                                        </span>
                                        <span v-else class="text-slate-400 text-xs font-bold uppercase">No Event</span>
                                    </td>
                                    <td class="py-4 font-bold text-slate-700">
                                        {{ num(c.uses) }} / {{ num(c.limit) }}
                                    </td>
                                    <td class="py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black border transition-colors" :class="c.status === 'Active' ? 'bg-green-50 text-green-700 border-green-200' : (c.status === 'Expired' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200')">
                                            {{ c.status }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex gap-2">
                                            <button @click="openModal(c.id)" class="rounded-xl bg-slate-100 text-slate-700 px-4 py-2 text-xs font-black hover:bg-slate-200 transition">Edit</button>
                                            <button
                                                @click="toggleStatus(c)"
                                                class="rounded-xl border px-3 py-2 text-xs font-black"
                                                :class="c.status === 'Active' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-green-50 text-green-700 border-green-100'"
                                            >
                                                {{ c.status === 'Active' ? 'Make Inactive' : 'Make Active' }}
                                            </button>
                                            <button @click="deleteCoupon(c.id)" class="rounded-xl bg-slate-100 text-slate-600 px-4 py-2 text-xs font-black hover:bg-rose-50 hover:text-rose-600 transition">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-5 backdrop-blur-md">
            <div class="bg-white rounded-[2.5rem] max-w-4xl w-full shadow-2xl overflow-hidden max-h-[94vh] overflow-y-auto animate-in zoom-in duration-200">
                <!-- Header -->
                <div class="p-8 bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white flex justify-between items-start">
                    <div>
                        <h3 class="text-4xl font-black">{{ editingCoupon ? 'Edit Coupon' : 'Create Coupon' }}</h3>
                        <p class="text-purple-100 text-xl mt-3">Fill in the details to configure the discount code</p>
                    </div>
                    <button @click="closeModal" class="text-white/80 hover:text-white transition hover:scale-110">
                        <X class="w-10 h-10" />
                    </button>
                </div>

                <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto scrollbar">
                    <!-- 1. Event Link -->
                    <div>
                        <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4"><Calendar class="text-slate-400 w-6 h-6" /> Link to Event</label>
                        <select v-model="form.link_up_event_id" class="w-full rounded-2xl border border-purple-100 bg-white px-6 py-5 text-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-purple-100 appearance-none transition-all">
                            <option :value="null">Select an event</option>
                            <option v-for="e in events" :key="e.value" :value="e.value">{{ e.label }}</option>
                        </select>
                        <p v-if="form.errors.link_up_event_id" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.link_up_event_id }}</p>
                    </div>

                    <!-- 2. Name & Code -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4">Coupon Name</label>
                            <input v-model="form.title" class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-5 text-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-purple-50" placeholder="e.g., Early Bird Promo">
                            <p v-if="form.errors.title" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4">Coupon Code</label>
                            <input v-model="form.code" class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-5 text-2xl font-semibold uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-purple-50" placeholder="EARLY25">
                            <p v-if="form.errors.code" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <!-- 3. Type, Discount, Expiry -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="text-slate-700 text-lg font-bold mb-4 block">Discount Type</label>
                            <select v-model="form.discount_type" class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-5 text-xl font-semibold h-[74px]">
                                <option value="percentage">Percentage</option>
                                <option value="amount">Fixed Amount</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-slate-700 text-lg font-bold mb-4 block">Discount Value</label>
                            <input v-model="form.discount" class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-5 text-xl font-semibold h-[74px]" placeholder="25% or 10">
                            <p v-if="form.errors.discount" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.discount }}</p>
                        </div>
                        <div>
                            <label class="text-slate-700 text-lg font-bold mb-4 block">Expiry Date</label>
                            <input v-model="form.expiry_date" type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-5 text-xl font-semibold h-[74px]">
                            <p v-if="form.errors.expiry_date" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.expiry_date }}</p>
                        </div>
                    </div>

                    <!-- 4. Limits -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-slate-700 text-lg font-bold mb-4 block">Usage Limit</label>
                            <input v-model="form.usage_limit" type="number" class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-5 text-2xl font-semibold" placeholder="100">
                            <p v-if="form.errors.usage_limit" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.usage_limit }}</p>
                        </div>
                        <div>
                            <label class="text-slate-700 text-lg font-bold mb-4 block">Initial Uses</label>
                            <input v-model="form.uses" type="number" class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-5 text-2xl font-semibold" placeholder="0">
                        </div>
                    </div>

                    <!-- 5. Description -->
                    <div>
                        <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4"><FileText class="text-slate-400 h-6 w-6" /> Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5 text-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-purple-50" placeholder="What is this discount for?"></textarea>
                        <p v-if="form.errors.description" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.description }}</p>
                    </div>

                    <!-- 6. Status Selection -->
                    <div>
                        <p class="text-slate-700 text-lg font-bold mb-4">Status</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <button
                                @click="form.status = 1"
                                type="button"
                                class="rounded-[2rem] border-2 px-8 py-6 text-left flex items-center gap-6 transition-all"
                                :class="form.status === 1 ? 'border-purple-500 bg-purple-50' : 'border-slate-100 bg-white hover:border-slate-200'"
                            >
                                <span class="h-16 w-16 rounded-full flex items-center justify-center transition-colors" :class="form.status === 1 ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-400'">
                                    <CircleCheck class="h-10 w-10" />
                                </span>
                                <span class="text-3xl font-black" :class="form.status === 1 ? 'text-purple-900' : 'text-slate-400'">Active</span>
                            </button>
                            <button
                                @click="form.status = 0"
                                type="button"
                                class="rounded-[2rem] border-2 px-8 py-6 text-left flex items-center gap-6 transition-all"
                                :class="form.status === 0 ? 'border-purple-500 bg-purple-50' : 'border-slate-100 bg-white hover:border-slate-200'"
                            >
                                <span class="h-16 w-16 rounded-full flex items-center justify-center transition-colors" :class="form.status === 0 ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-400'">
                                    <CircleX class="h-10 w-10" />
                                </span>
                                <span class="text-3xl font-black" :class="form.status === 0 ? 'text-purple-900' : 'text-slate-400'">Inactive</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-8 border-t border-slate-200 flex justify-end gap-4 bg-slate-50">
                    <button @click="closeModal" class="rounded-2xl bg-white border border-slate-200 text-slate-800 px-10 py-5 text-xl font-black hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button @click="saveCoupon" :disabled="form.processing" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-10 py-5 text-xl font-black shadow-xl shadow-purple-200 hover:opacity-90 transition disabled:opacity-50 flex items-center gap-3">
                        <span v-if="form.processing" class="animate-spin h-6 w-6 border-4 border-white border-t-transparent rounded-full"></span>
                        {{ form.processing ? 'Saving...' : (editingCoupon ? 'Update Coupon' : 'Create Coupon') }}
                    </button>
                </div>
            </div>
        </div>

        <ConfirmDeleteDialog
            v-model="showDeleteDialog"
            :form="deleteForm"
            title="Delete Coupon"
            description="Are you sure you want to delete this coupon? This action cannot be undone."
            @submit="confirmDeleteCoupon"
        />

        <Toaster rich-colors position="top-right" />
    </div>
</template>
