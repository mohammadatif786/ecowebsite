<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ScanLine, UserCheck, WifiOff, CalendarCheck, TicketCheck, ShieldAlert, Plus, Eye, Pencil, X, User, Phone, Mail, MapPin, Building2, Camera, Smartphone, BarChart3, Info, Trash2, Upload, Check } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialScanners: any[];
    organizers: any[];
    initialStats: any;
    appURL: string;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;
const scanners = ref([...props.initialScanners]);
const scannerSearch = ref('');
const scannerStatusFilter = ref('All Statuses');

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

// Filtering Logic
const filteredScanners = computed(() => {
    const q = scannerSearch.value.toLowerCase().trim();
    const st = scannerStatusFilter.value;
    return scanners.value.filter(s => {
        const hay = [s.name, s.email, s.phone, s.organizer, s.country, s.role, s.status].join(' ').toLowerCase();
        return (!q || hay.includes(q)) && (st === 'All Statuses' || s.status === st);
    });
});

const stats = computed(() => {
    const all = scanners.value;
    return {
        total: all.length,
        active: all.filter(s => s.status === 'Active').length,
        offline: all.filter(s => s.status === 'Offline').length,
        events: props.initialStats.events || 0,
        todayScans: all.reduce((s, x) => s + x.scansToday, 0),
        fraudAlerts: all.reduce((s, x) => s + x.fraudAlerts, 0)
    };
});

// Sync ref when props update from backend
watch(() => props.initialScanners, (newVal) => {
    scanners.value = [...newVal];
}, { deep: true });

// Modal Logic
const showModal = ref(false);
const editingScanner = ref<any>(null);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    id: 0,
    first_name: '',
    last_name: '',
    email: '',
    telephone: '',
    address: '',
    user_id: '' as string | number, // Assigned organizer's user ID
    status: 'Active',
    scanner_image_object: null as File | null,
    _method: 'POST'
});

const openModal = (s: any = null) => {
    if (s) {
        editingScanner.value = s;
        form.id = s.id;
        form.first_name = s.first_name;
        form.last_name = s.last_name;
        form.email = s.email;
        form.telephone = s.phone;
        form.address = s.address;
        form.user_id = s.org_id || '';
        form.status = s.status;
        form._method = 'PUT';
        imagePreview.value = s.image;
    } else {
        editingScanner.value = null;
        form.reset();
        form.id = 0;
        form.status = 'Active';
        form._method = 'POST';
        imagePreview.value = null;
    }
    showModal.value = true;
};

const handleImageUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.scanner_image_object = file;

        const reader = new FileReader();
        reader.onload = (event) => {
            imagePreview.value = event.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const closeModal = () => {
    showModal.value = false;
    editingScanner.value = null;
    imagePreview.value = null;
    form.reset();
    form.clearErrors();
};

const saveScanner = () => {
    const isUpdate = form.id > 0;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success(isUpdate ? 'Scanner updated successfully' : 'Scanner created successfully');
        },
        onError: () => {
            toast.error(isUpdate ? 'Failed to update scanner. Check the highlighted fields.' : 'Failed to create scanner. Check the highlighted fields.');
        },
    };

    if (isUpdate) {
        form.post(route('admin.new_admin.scanners.update', form.id), options);
    } else {
        form.post(route('admin.new_admin.scanners.store'), options);
    }
};

const deletingScannerId = ref<number | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

const deleteScanner = (id: number) => {
    deletingScannerId.value = id;
    showDeleteDialog.value = true;
};

function confirmDeleteScanner() {
    if (!deletingScannerId.value) return;
    const url = route('admin.new_admin.scanners.destroy', deletingScannerId.value);
    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Scanner deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete scanner');
        },
        onFinish: () => (showDeleteDialog.value = false),
    });
};

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
    <Head title="Scanners Management" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="organizerScannersCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Scanners Management" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-purple-600">Scanners Management</h3>
                        <p class="text-slate-500">Manage event ticket scanners, assignments, check-ins, device activity, and fraud alerts.</p>
                    </div>
                    <button @click="openModal()" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-5 py-3 font-black shadow-lg shadow-purple-200 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                        <Plus class="h-5 w-5" /> Add Scanner
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><ScanLine class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Total Scanners</p><h3 class="text-3xl font-black">{{ num(stats.total) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-green-100 text-green-600 grid place-items-center"><UserCheck class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Active</p><h3 class="text-3xl font-black">{{ num(stats.active) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-600 grid place-items-center"><WifiOff class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Offline</p><h3 class="text-3xl font-black">{{ num(stats.offline) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-sky-100 text-sky-600 grid place-items-center"><CalendarCheck class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Assigned Events</p><h3 class="text-3xl font-black">{{ num(stats.events) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center"><TicketCheck class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Check-ins Today</p><h3 class="text-3xl font-black">{{ num(stats.todayScans) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-rose-100 text-rose-600 grid place-items-center"><ShieldAlert class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Fraud Alerts</p><h3 class="text-3xl font-black">{{ num(stats.fraudAlerts) }}</h3></div></div>
                </div>

                <div class="card rounded-3xl p-6 shadow-sm">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-5">
                        <div><h3 class="text-xl font-black text-slate-800">Scanner Directory</h3><p class="text-slate-500 text-sm font-medium">View scanner assignments and event check-in performance.</p></div>
                        <div class="flex flex-wrap gap-2">
                            <input v-model="scannerSearch" class="rounded-2xl border border-slate-200 px-4 py-2 w-full xl:w-64 outline-none focus:ring-4 focus:ring-purple-50 transition" placeholder="Search scanner, organizer, email...">
                            <select v-model="scannerStatusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold bg-white focus:ring-4 focus:ring-purple-50 outline-none transition">
                                <option>All Statuses</option>
                                <option>Active</option>
                                <option>Offline</option>
                                <option>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-50">
                                <tr>
                                    <th class="py-3 px-2">Scanner</th>
                                    <th>Email / Phone</th>
                                    <th>Organizer</th>
                                    <th>Country</th>
                                    <th class="text-center">Scans Today</th>
                                    <th class="text-center">Total Scans</th>
                                    <th class="text-center">Fraud Alerts</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="s in filteredScanners" :key="s.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition align-middle">
                                    <td class="py-4 px-2">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 rounded-full bg-purple-600 text-white grid place-items-center font-black text-xs shadow-sm overflow-hidden">
                                                <img v-if="s.image" :src="s.image" class="w-full h-full object-cover">
                                                <span v-else>{{ s.name.split(' ').map(x=>x[0]).join('') }}</span>
                                            </div>
                                            <div>
                                                <b class="text-[13px] text-slate-900 block leading-tight">{{ s.name }}</b>
                                                <p class="text-[11px] text-slate-400 font-bold mt-1">ID: #{{ s.id }} • {{ s.role }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-[13px] font-bold text-slate-700">{{ s.email }}</div>
                                        <div class="text-[11px] text-slate-400 font-medium">{{ s.phone }}</div>
                                    </td>
                                    <td class="text-[13px] font-bold text-slate-600">{{ s.organizer }}</td>
                                    <td class="text-[13px] font-bold text-slate-800">{{ s.country }}</td>
                                    <td class="text-center font-black text-slate-900">{{ num(s.scansToday) }}</td>
                                    <td class="text-center font-medium text-slate-500">{{ num(s.totalScans) }}</td>
                                    <td class="text-center">
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-black text-white" :class="s.fraudAlerts > 0 ? 'bg-rose-500' : 'bg-green-500'">
                                            {{ s.fraudAlerts }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="s.status === 'Active' ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-slate-400'"></span>
                                            <span class="text-[13px] font-black" :class="s.status === 'Active' ? 'text-green-600' : 'text-slate-500'">{{ s.status }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button @click="openModal(s)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition" title="Edit"><Pencil class="h-4 w-4" /></button>
                                            <button @click="deleteScanner(s.id)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition" title="Delete"><Trash2 class="h-4 w-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredScanners.length === 0">
                                    <td colspan="9" class="py-12 text-center text-slate-400 font-bold">No scanners found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        <!-- Comprehensive Add/Edit Modal (Aligned with HTML Reference) -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-[100] flex items-start justify-center p-3 overflow-y-auto backdrop-blur-md">
            <div class="bg-white rounded-[40px] max-w-4xl w-full shadow-2xl my-4 overflow-hidden animate-in zoom-in duration-200">
                <!-- Header -->
                <div class="p-6 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-white/20 grid place-items-center"><ScanLine class="w-7 h-7" /></div>
                    <div class="flex-1">
                        <h3 class="text-3xl font-black">{{ editingScanner ? 'Edit Scanner' : 'Add Scanner' }}</h3>
                        <p class="text-violet-100">{{ editingScanner ? 'Update scanner information' : 'Create a new ticket scanner' }}</p>
                    </div>
                    <button @click="closeModal" class="text-white/90 hover:text-white transition hover:scale-110">
                        <X class="w-10 h-10" />
                    </button>
                </div>

                <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-5 max-h-[78vh] overflow-y-auto scrollbar">
                    <div class="lg:col-span-2 space-y-5">
                        <!-- Personal Information -->
                        <div class="card rounded-3xl p-6 border-slate-200/60 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="h-9 w-9 rounded-xl bg-violet-100 text-violet-600 grid place-items-center"><User class="w-5 h-5" /></span>
                                <h4 class="text-xl font-black text-slate-800">Personal Information</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><label class="text-sm font-bold text-slate-600">First Name</label><input v-model="form.first_name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:ring-4 focus:ring-purple-50 transition outline-none font-bold" placeholder="First name"></div>
                                <div><label class="text-sm font-bold text-slate-600">Last Name</label><input v-model="form.last_name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:ring-4 focus:ring-purple-50 transition outline-none font-bold" placeholder="Last name"></div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card rounded-3xl p-6 border-slate-200/60 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="h-9 w-9 rounded-xl bg-sky-100 text-sky-600 grid place-items-center"><Phone class="w-5 h-5" /></span>
                                <h4 class="text-xl font-black text-slate-800">Contact Information</h4>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-bold text-slate-600">Email Address</label>
                                    <div class="mt-1 flex items-center rounded-2xl border border-slate-200 px-3 bg-white focus-within:ring-4 focus-within:ring-sky-50 transition-all">
                                        <Mail class="w-4 h-4 text-slate-400" />
                                        <input v-model="form.email" type="email" class="w-full px-2 py-3 outline-none bg-transparent font-bold" placeholder="email@example.com">
                                    </div>
                                    <p v-if="form.errors.email" class="text-rose-500 text-xs mt-1 font-bold">{{ form.errors.email }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-bold text-slate-600">Phone Number</label>
                                    <div class="mt-1 flex items-center rounded-2xl border border-slate-200 px-3 bg-white">
                                        <Phone class="w-4 h-4 text-slate-400" />
                                        <input v-model="form.telephone" class="w-full px-2 py-3 outline-none bg-transparent font-bold" placeholder="+1 (555) 000-0000">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-bold text-slate-600">Address</label>
                                    <div class="mt-1 flex items-center rounded-2xl border border-slate-200 px-3 bg-white">
                                        <MapPin class="w-4 h-4 text-slate-400" />
                                        <input v-model="form.address" class="w-full px-2 py-3 outline-none bg-transparent font-bold" placeholder="Enter full address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Organizer Assignment -->
                        <div class="card rounded-3xl p-6 border-slate-200/60 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="h-9 w-9 rounded-xl bg-green-100 text-green-600 grid place-items-center"><Building2 class="w-5 h-5" /></span>
                                <h4 class="text-xl font-black text-slate-800">Organizer Assignment</h4>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-600">Assign to Organizer</label>
                                <select v-model="form.user_id" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-black bg-white focus:ring-4 focus:ring-green-50 outline-none">
                                    <option :value="null">— Unassigned —</option>
                                    <option v-for="org in organizers" :key="org.id" :value="org.id">{{ org.name }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div><label class="text-sm font-bold text-slate-600">Role</label><select class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-black bg-white"><option>Lead Scanner</option><option>Gate Scanner</option><option>Entry Scanner</option><option>VIP Scanner</option><option>Check-in Team</option><option>Scanner Admin</option></select></div>
                                <div><label class="text-sm font-bold text-slate-600">Status</label><select v-model="form.status" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-black bg-white"><option>Active</option><option>Offline</option><option>Inactive</option></select></div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Info/Photo -->
                    <div class="space-y-5">
                        <div class="card rounded-3xl p-6 border-slate-200/60 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="h-9 w-9 rounded-xl bg-amber-100 text-amber-600 grid place-items-center"><Camera class="w-5 h-5" /></span>
                                <h4 class="text-xl font-black text-slate-800">Profile Photo</h4>
                            </div>
                            <div
                                @click="triggerFileInput"
                                class="h-48 w-full rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all overflow-hidden relative group"
                            >
                                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                                <div v-else class="text-center p-4">
                                    <Upload class="w-8 h-8 mx-auto text-slate-400" />
                                    <p class="mt-2 font-bold text-slate-500">Click to upload</p>
                                    <p class="text-xs text-slate-400">PNG, JPG up to 5MB</p>
                                </div>
                                <div v-if="imagePreview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <p class="text-white font-black text-xl">Change Photo</p>
                                </div>
                            </div>
                            <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleImageUpload">
                        </div>

                        <div class="rounded-3xl bg-violet-50 p-6 border border-violet-100">
                            <div class="flex items-center gap-2 mb-3 text-violet-700">
                                <Info class="w-5 h-5" />
                                <h4 class="text-lg font-black">Scanner Info</h4>
                            </div>
                            <ul class="space-y-2 text-violet-700 text-sm font-bold">
                                <li class="flex gap-2"><span>•</span>Scanners can verify tickets at events</li>
                                <li class="flex gap-2"><span>•</span>Assign to an organizer for access</li>
                                <li class="flex gap-2"><span>•</span>Contact info is used for notifications</li>
                            </ul>
                        </div>

                        <button @click="saveScanner" :disabled="form.processing" class="w-full rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white px-6 py-4 font-black flex items-center justify-center gap-2 shadow-lg shadow-violet-200 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50">
                            <span v-if="form.processing" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                            <Check v-else class="w-5 h-5" />
                            {{ editingScanner ? 'Update Scanner' : 'Create Scanner' }}
                        </button>
                        <button @click="closeModal" class="w-full rounded-2xl bg-slate-100 px-6 py-3 font-black text-slate-500 hover:bg-slate-200 transition">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDeleteDialog
            v-model="showDeleteDialog"
            :form="deleteForm"
            title="Delete Scanner"
            description="Are you sure you want to delete this scanner? This action cannot be undone."
            @submit="confirmDeleteScanner"
        />

        <Toaster rich-colors position="top-right" />
    </div>
</template>
