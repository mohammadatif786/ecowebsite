<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import DeleteModal from '@/components/admin/DeleteModal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { BadgeCheck, CalendarPlus, Clock, Globe2, MapPin, Pencil, Plus, Ticket, Trash2, Eye, X, IdCard, Phone, Mail, Globe, CreditCard, FileText, Tag, Settings as SettingsIcon, Image as ImageIcon, FileCheck, UserRound, Youtube, Upload, FileDigit } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialOrganizers: any[];
    initialStats: any;
    appURL: string;
    categories: any[];
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;
const organizerRecords = ref([...props.initialOrganizers]);
const organizerSearch = ref('');
const organizerCountryFilter = ref('All Countries');
const organizerStatusFilter = ref('All Statuses');

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

const filteredOrganizers = computed(() => {
    const q = organizerSearch.value.toLowerCase().trim();
    const c = organizerCountryFilter.value;
    const s = organizerStatusFilter.value;

    return organizerRecords.value.filter(o => {
        const hay = [o.name, o.email, o.country, o.city, o.category, o.kyc, o.status].join(' ').toLowerCase();
        const okSearch = !q || hay.includes(q);
        const okCountry = c === 'All Countries' || o.country === c;
        const okStatus = s === 'All Statuses' || o.status === s;
        return okSearch && okCountry && okStatus;
    });
});

const stats = computed(() => {
    const all = organizerRecords.value;
    return {
        total: all.length,
        active: all.filter(o => o.status === 'Active').length,
        countries: new Set(all.map(o => o.country).filter(Boolean)).size,
        pendingKyc: all.filter(o => o.kyc === 'Pending Review').length,
        events: all.reduce((s, o) => s + (o.events || 0), 0)
    };
});

const countrySummary = computed(() => {
    const map: any = {};
    organizerRecords.value.forEach(o => {
        const c = o.country || 'Unknown';
        if (!map[c]) map[c] = { count: 0, events: 0, revenue: 0 };
        map[c].count++;
        map[c].events += (o.events || 0);
        map[c].revenue += (o.revenue || 0);
    });
    return Object.entries(map).sort((a: any, b: any) => b[1].count - a[1].count);
});

// Sync ref when props update from backend
watch(() => props.initialOrganizers, (newVal) => {
    organizerRecords.value = [...newVal];
}, { deep: true });

// Actions
const showViewModal = ref(false);
const viewingOrganizer = ref<any>(null);

const handleViewOrganizer = (o: any) => {
    viewingOrganizer.value = o;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    viewingOrganizer.value = null;
};

const viewThenEdit = () => {
    const o = viewingOrganizer.value;
    closeViewModal();
    if (o) openModal(o);
};

const maskSSN = (ssn: string | null | undefined) => {
    if (!ssn || ssn === '—') return '—';
    return '•••-••-••••';
};

const handleEdit = (o: any) => {
    // Open modal for editing if preferred, but user said "add organizer" opens model.
    // We'll restore the modal for both for consistency in the "New Admin" style.
    openModal(o);
};

const deleteRecord = ref(false);
const deleteId = ref(0);

const deleteData = (id: number) => {
    deleteId.value = id;
    deleteRecord.value = true;
};

// Modal Logic
const showModal = ref(false);
const editingOrganizer = ref<any>(null);

const form = useForm({
    accountPassword: '',
    profileDetail: {
        organizer_profile_type: 'profileDetail',
        organizer_name: '',
        date_of_birth: '',
        place_of_birth: '',
        nationality: '',
        address: '',
        telephone: '',
        about_the_organizer: '',
        ssn: '',
        categories: [] as string[],
    },
    additionalDetails: {
        organizer_profile_type: 'AdditionalDetails',
        logo: null as File | null,
        cover_photo: null as File | null,
        profile_photo: null as File | null,
    },
    organizerMedia: {
        organizer_profile_type: 'OrganizerMedia',
        facebook: '',
        twitter: '',
        instagram: '',
        linkedin: '',
        youtube: '',
        country: 'Bahamas',
        state: '',
        city: '',
        website: '',
        email: '',
        phone: '',
    },
    profileVisibility: {
        organizer_profile_type: 'profileVisibility',
        show_venues_map: 1,
        show_followers: 1,
        show_reviews: 1,
    },
    bankingInformation: {
        organizer_profile_type: 'BankingInformation',
        banks: [
            { bank_name: '', account_number: '', routing_number: '' }
        ],
        paypal_id: '',
    },
    organizerKYC: {
        organizer_profile_type: 'organizerKYC',
        passport_front: null as File | null,
        passport_back: null as File | null,
        proof_of_address: null as File | null,
    },
});

// Placeholder display values ('—', 'Unknown') should render as empty in form inputs
const clean = (v: any): string => (v === undefined || v === null || v === '—' || v === 'Unknown' ? '' : v);

const openModal = (o: any = null) => {
    if (o) {
        editingOrganizer.value = o;
        form.reset();
        form.accountPassword = '';

        form.profileDetail.organizer_name = clean(o.name);
        form.profileDetail.date_of_birth = clean(o.dob);
        form.profileDetail.place_of_birth = clean(o.placeBirth);
        form.profileDetail.nationality = clean(o.nationality);
        form.profileDetail.address = clean(o.address);
        form.profileDetail.telephone = clean(o.telephone);
        form.profileDetail.about_the_organizer = clean(o.about);
        form.profileDetail.ssn = clean(o.ssn);
        form.profileDetail.categories = o.categories?.length ? [...o.categories] : [];

        form.organizerMedia.facebook = clean(o.social?.facebook);
        form.organizerMedia.twitter = clean(o.social?.twitter);
        form.organizerMedia.instagram = clean(o.social?.instagram);
        form.organizerMedia.linkedin = clean(o.social?.linkedin);
        form.organizerMedia.youtube = clean(o.social?.youtube);
        form.organizerMedia.country = clean(o.country) || 'Bahamas';
        form.organizerMedia.state = clean(o.state);
        form.organizerMedia.city = clean(o.city);
        form.organizerMedia.website = clean(o.website);
        form.organizerMedia.email = clean(o.email);
        form.organizerMedia.phone = clean(o.phone);

        form.profileVisibility.show_venues_map = o.settings?.show_venues_map ? 1 : 0;
        form.profileVisibility.show_followers = o.settings?.show_followers ? 1 : 0;
        form.profileVisibility.show_reviews = o.settings?.show_reviews ? 1 : 0;

        form.bankingInformation.banks = o.bankAccounts?.length
            ? o.bankAccounts.map((b: any) => ({
                  bank_name: clean(b.bank_name),
                  account_number: clean(b.account_number),
                  routing_number: clean(b.routing_number),
              }))
            : [{ bank_name: '', account_number: '', routing_number: '' }];

        // Existing KYC/logo/cover/profile files are preserved server-side unless new files are chosen here
    } else {
        editingOrganizer.value = null;
        form.reset();
    }
    showModal.value = true;
};

const saveOrganizer = () => {
    if (editingOrganizer.value) {
        form.post(route('admin.organizer.profile.update', { organizer_id: editingOrganizer.value.id, source: 'new_admin' }), {
            onSuccess: () => {
                closeModal();
                toast.success('Organizer updated successfully');
            },
            onError: () => {
                toast.error('Failed to update organizer. Check the highlighted fields.');
            },
        });
    } else {
        form.post(route('admin.organizer.profile.store', { source: 'new_admin' }), {
            onSuccess: () => {
                closeModal();
                toast.success('Organizer created successfully');
            },
            onError: () => {
                toast.error('Failed to create organizer. Check the highlighted fields.');
            },
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    editingOrganizer.value = null;
    form.reset();
    form.clearErrors();
};

const addBank = () => {
    form.bankingInformation.banks.push({ bank_name: '', account_number: '', routing_number: '' });
};

const removeBank = (index: number) => {
    if (form.bankingInformation.banks.length > 1) {
        form.bankingInformation.banks.splice(index, 1);
    }
};

// Helper function to properly construct image URLs
function getImageUrl(path: string | null | undefined): string {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return `/${path}`;
    return `/storage/${path.replace(/^\//, '')}`;
}

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
    <Head title="Organizer Directory" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="organizerDirectoryCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Organizer Directory" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-purple-600">Organizer Directory</h3>
                        <p class="text-slate-500">Country-by-country list of event organizers with contact details, KYC status, linked events, tickets, and revenue activity.</p>
                    </div>
                    <button @click="openModal()" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-5 py-3 font-black shadow-lg shadow-purple-200 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                        <Plus class="h-5 w-5" /> Add Organizer
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4 xl:col-span-2"><div class="h-12 w-12 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><CalendarPlus class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Total Organizers</p><h3 class="text-3xl font-black">{{ num(stats.total) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-green-100 text-green-600 grid place-items-center"><BadgeCheck class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Active</p><h3 class="text-3xl font-black">{{ num(stats.active) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-sky-100 text-sky-600 grid place-items-center"><Globe2 class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Countries</p><h3 class="text-3xl font-black">{{ num(stats.countries) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center"><Clock class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Pending KYC</p><h3 class="text-3xl font-black">{{ num(stats.pendingKyc) }}</h3></div></div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4"><div class="h-12 w-12 rounded-2xl bg-lime-100 text-lime-700 grid place-items-center"><Ticket class="h-6 w-6" /></div><div><p class="text-slate-500 font-bold">Linked Events</p><h3 class="text-3xl font-black">{{ num(stats.events) }}</h3></div></div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
                    <div class="2xl:col-span-2 card rounded-3xl p-6">
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-5">
                            <div><h3 class="text-xl font-black text-slate-800">Organizer Directory by Country</h3><p class="text-slate-500 text-sm font-medium">Search, filter, view, edit, activate, or deactivate organizers.</p></div>
                            <div class="flex flex-wrap gap-2">
                                <input v-model="organizerSearch" class="rounded-2xl border border-slate-200 px-4 py-2 w-full xl:w-64 outline-none focus:ring-4 focus:ring-purple-50 transition" placeholder="Search organizer, email, country, city...">
                                <select v-model="organizerCountryFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold bg-white focus:ring-4 focus:ring-purple-50 outline-none transition">
                                    <option>All Countries</option>
                                    <option v-for="c in countries" :key="c.country" :value="c.country">{{ c.country }}</option>
                                </select>
                                <select v-model="organizerStatusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold bg-white text-sm outline-none focus:ring-4 focus:ring-purple-50 transition">
                                    <option>All Statuses</option>
                                    <option>Active</option>
                                    <option>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar">
                            <table class="w-full text-left">
                                <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest border-b border-slate-50">
                                    <tr>
                                        <th class="py-3 px-2">Organizer</th>
                                        <th>Contact</th>
                                        <th>Country / City</th>
                                        <th>Events</th>
                                        <th>Revenue</th>
                                        <th>KYC</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <tr v-for="o in filteredOrganizers" :key="o.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition align-middle">
                                        <td class="py-4 px-2">
                                            <div class="flex items-center gap-4">
                                                <div class="h-14 w-14 rounded-full bg-purple-600 text-white grid place-items-center font-black text-lg shadow-sm overflow-hidden">
                                                    <img v-if="o.media?.profile_photo" :src="getImageUrl(o.media.profile_photo)" class="w-full h-full object-cover">
                                                    <span v-else>{{ o.name?.split(' ').map(x=>x[0]).join('') }}</span>
                                                </div>
                                                <div>
                                                    <b class="text-base text-slate-900 block leading-tight">{{ o.name }}</b>
                                                    <p class="text-[11px] text-slate-400 font-bold mt-1">{{ o.category }} • ID: #{{ o.id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-[13px] font-bold text-slate-700">{{ o.email }}</div>
                                            <div class="text-[11px] text-slate-400 font-medium">{{ o.phone }}</div>
                                        </td>
                                        <td>
                                            <div class="text-[13px] font-black text-slate-800">{{ o.country }}</div>
                                            <div class="text-[11px] text-slate-400 font-medium">{{ o.city }}</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1 text-[13px] font-black text-slate-900">
                                                {{ o.events }}
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">linked events</span>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-bold mt-0.5">{{ num(o.tickets) }} tickets</div>
                                        </td>
                                        <td>
                                            <div class="text-base font-black text-slate-900">{{ fmt(o.revenue) }}</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ num(o.tickets) }} tickets</div>
                                        </td>
                                        <td>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border-2" :class="o.kyc === 'Verified' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-600 border-amber-100'">
                                                {{ o.kyc }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-1.5 w-1.5 rounded-full" :class="o.status === 'Active' ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-slate-400'"></span>
                                                <span class="text-[13px] font-black" :class="o.status === 'Active' ? 'text-green-600' : 'text-slate-500'">{{ o.status }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button @click="handleViewOrganizer(o)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition" title="View"><Eye class="h-4 w-4" /></button>
                                                <button @click="handleEdit(o)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-500 hover:bg-slate-200 transition" title="Edit"><Pencil class="h-4 w-4" /></button>
                                                <button @click="deleteData(o.id)" class="h-9 w-9 rounded-xl bg-slate-100 grid place-items-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition" title="Delete"><Trash2 class="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredOrganizers.length === 0">
                                        <td colspan="8" class="py-12 text-center text-slate-400 font-bold">No organizers found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="text-xl font-black text-slate-800 mb-4">Country Summary</h3>
                        <div class="space-y-3">
                            <div v-for="[c, v] in (countrySummary as any)" :key="c" class="rounded-2xl bg-slate-50 p-4 flex justify-between gap-3 group hover:bg-white hover:shadow-md transition">
                                <div><b>{{ c }}</b><p class="text-xs text-slate-500 font-bold">{{ v.count }} organizer(s) • {{ v.events }} event(s)</p></div>
                                <b class="text-emerald-600 text-lg">{{ fmt(v.revenue) }}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Delete Modal -->
        <DeleteModal
            v-if="deleteRecord"
            :id="deleteId"
            route="admin.organizer.profile.destroy"
            :extra-params="{ organizer_id: deleteId, source: 'new_admin' }"
            :open="deleteRecord"
            @update:open="deleteRecord = $event"
        />

        <!-- View Organizer Modal -->
        <div v-if="showViewModal && viewingOrganizer" class="fixed inset-0 bg-black/50 z-[100] flex items-start justify-center p-3 overflow-y-auto backdrop-blur-md">
            <div class="bg-white rounded-[32px] max-w-4xl w-full shadow-2xl my-4 overflow-hidden animate-in zoom-in duration-200">
                <!-- Cover -->
                <div class="relative h-40 bg-gradient-to-br from-purple-600 via-fuchsia-500 to-amber-400">
                    <img v-if="getImageUrl(viewingOrganizer.media?.cover_photo)" :src="getImageUrl(viewingOrganizer.media?.cover_photo)" class="w-full h-full object-cover">
                    <button @click="closeViewModal" class="absolute top-4 right-4 h-9 w-9 rounded-full bg-white grid place-items-center hover:bg-slate-100 transition shadow">
                        <X class="h-5 w-5 text-slate-700" />
                    </button>
                    <div class="absolute -bottom-10 left-8 h-20 w-20 rounded-2xl bg-slate-900 text-white grid place-items-center font-black text-3xl shadow-lg border-4 border-white overflow-hidden">
                        <img v-if="getImageUrl(viewingOrganizer.media?.profile_photo)" :src="getImageUrl(viewingOrganizer.media?.profile_photo)" class="w-full h-full object-cover">
                        <span v-else>{{ viewingOrganizer.name?.charAt(0)?.toUpperCase() || '?' }}</span>
                    </div>
                </div>

                <div class="pt-14 px-8 pb-8 max-h-[80vh] overflow-y-auto scrollbar">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">{{ viewingOrganizer.name }}</h3>
                            <p class="text-slate-500 flex items-center gap-1 mt-1 text-sm"><MapPin class="h-4 w-4" /> {{ viewingOrganizer.city && viewingOrganizer.city !== '—' ? viewingOrganizer.city + ', ' : '' }}{{ viewingOrganizer.country }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-4 py-1.5 text-xs font-black border" :class="viewingOrganizer.status === 'Active' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                ● {{ viewingOrganizer.status }}
                            </span>
                            <button @click="viewThenEdit" class="rounded-full border border-purple-200 text-purple-600 px-4 py-1.5 text-xs font-black hover:bg-purple-50 transition flex items-center gap-1">
                                <Pencil class="h-3.5 w-3.5" /> Edit Profile
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                        <div class="xl:col-span-2 space-y-5">
                            <!-- About -->
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h4 class="font-black text-slate-800 flex items-center gap-2 mb-3"><UserRound class="h-5 w-5 text-purple-500" /> About</h4>
                                <p class="text-slate-600 text-sm">{{ viewingOrganizer.about }}</p>
                            </div>

                            <!-- Contact Information -->
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h4 class="font-black text-slate-800 flex items-center gap-2 mb-4"><Phone class="h-5 w-5 text-sky-500" /> Contact Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div class="space-y-3">
                                        <p class="flex items-center gap-2 text-slate-600"><Mail class="h-4 w-4 text-slate-400" /> {{ viewingOrganizer.email }}</p>
                                        <p class="flex items-center gap-2 text-slate-600"><Phone class="h-4 w-4 text-slate-400" /> {{ viewingOrganizer.phone }}</p>
                                        <p class="flex items-center gap-2 text-slate-600"><MapPin class="h-4 w-4 text-slate-400" /> {{ viewingOrganizer.city && viewingOrganizer.city !== '—' ? viewingOrganizer.city + ', ' : '' }}{{ viewingOrganizer.country }}</p>
                                        <a v-if="viewingOrganizer.website && viewingOrganizer.website !== '—'" :href="viewingOrganizer.website" target="_blank" rel="noopener" class="flex items-center gap-2 text-purple-600 font-bold hover:underline break-all"><Globe class="h-4 w-4 flex-shrink-0" /> {{ viewingOrganizer.website }}</a>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase text-slate-400 mb-2">Social Links</p>
                                        <div class="flex flex-wrap gap-2">
                                            <a v-if="viewingOrganizer.social?.facebook" :href="viewingOrganizer.social.facebook" target="_blank" rel="noopener" class="rounded-full bg-blue-50 text-blue-700 px-3 py-1 text-xs font-black hover:bg-blue-100 transition">Facebook</a>
                                            <a v-if="viewingOrganizer.social?.instagram" :href="viewingOrganizer.social.instagram" target="_blank" rel="noopener" class="rounded-full bg-pink-50 text-pink-600 px-3 py-1 text-xs font-black hover:bg-pink-100 transition">Instagram</a>
                                            <a v-if="viewingOrganizer.social?.twitter" :href="viewingOrganizer.social.twitter" target="_blank" rel="noopener" class="rounded-full bg-sky-50 text-sky-600 px-3 py-1 text-xs font-black hover:bg-sky-100 transition">Twitter</a>
                                            <a v-if="viewingOrganizer.social?.linkedin" :href="viewingOrganizer.social.linkedin" target="_blank" rel="noopener" class="rounded-full bg-blue-50 text-blue-800 px-3 py-1 text-xs font-black hover:bg-blue-100 transition">LinkedIn</a>
                                            <a v-if="viewingOrganizer.social?.youtube" :href="viewingOrganizer.social.youtube" target="_blank" rel="noopener" class="rounded-full bg-rose-50 text-rose-600 px-3 py-1 text-xs font-black hover:bg-rose-100 transition">YouTube</a>
                                            <span v-if="!viewingOrganizer.social?.facebook && !viewingOrganizer.social?.instagram && !viewingOrganizer.social?.twitter && !viewingOrganizer.social?.linkedin && !viewingOrganizer.social?.youtube" class="text-slate-400 text-xs font-bold">No social links added</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Accounts -->
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <h4 class="font-black text-slate-800 flex items-center gap-2 mb-4"><CreditCard class="h-5 w-5 text-emerald-500" /> Bank Accounts</h4>
                                <div class="overflow-x-auto scrollbar">
                                    <table class="w-full text-left text-sm">
                                        <thead class="text-[10px] uppercase text-slate-400 font-black tracking-widest">
                                            <tr><th class="py-2">Bank Name</th><th>Account Number</th><th>Routing Number</th></tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="viewingOrganizer.bankAccounts?.length">
                                                <tr v-for="(b, i) in viewingOrganizer.bankAccounts" :key="i" class="border-t border-slate-50">
                                                    <td class="py-3 font-bold text-slate-700">{{ b.bank_name || '—' }}</td>
                                                    <td class="py-3 font-mono text-slate-500">{{ b.account_number ? '••••' + String(b.account_number).slice(-4) : '—' }}</td>
                                                    <td class="py-3 text-slate-500">{{ b.routing_number || '—' }}</td>
                                                </tr>
                                            </template>
                                            <tr v-else class="border-t border-slate-50">
                                                <td class="py-3 text-slate-400 font-bold" colspan="3">No bank accounts on file.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- KYC Documents -->
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-black text-slate-800 flex items-center gap-2"><FileText class="h-5 w-5 text-amber-500" /> KYC Documents</h4>
                                    <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase border-2" :class="viewingOrganizer.kyc === 'Verified' ? 'bg-green-50 text-green-700 border-green-100' : (viewingOrganizer.kyc === 'Not Submitted' ? 'bg-slate-100 text-slate-500 border-slate-200' : 'bg-amber-50 text-amber-600 border-amber-100')">
                                        {{ viewingOrganizer.kyc }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <a
                                        v-for="doc in [
                                            { key: 'passport_front', label: 'Passport / ID Front', icon: IdCard },
                                            { key: 'passport_back', label: 'Passport / ID Back', icon: FileCheck },
                                            { key: 'proof_of_address', label: 'Proof of Address', icon: ImageIcon },
                                        ]"
                                        :key="doc.key"
                                        :href="getImageUrl(viewingOrganizer.kycDocuments?.[doc.key]) || undefined"
                                        target="_blank"
                                        rel="noopener"
                                        class="h-24 rounded-2xl border overflow-hidden grid place-items-center transition"
                                        :class="viewingOrganizer.kycDocuments?.[doc.key] ? 'border-slate-200 hover:border-purple-300 cursor-pointer' : 'bg-slate-50 border-slate-100 text-slate-300 pointer-events-none'"
                                        :title="doc.label"
                                    >
                                        <img
                                            v-if="viewingOrganizer.kycDocuments?.[doc.key]"
                                            :src="getImageUrl(viewingOrganizer.kycDocuments[doc.key])"
                                            class="w-full h-full object-cover"
                                            :alt="doc.label"
                                        />
                                        <component :is="doc.icon" v-else class="h-8 w-8" />
                                    </a>
                                </div>
                                <p v-if="!viewingOrganizer.kycDocuments?.passport_front && !viewingOrganizer.kycDocuments?.passport_back && !viewingOrganizer.kycDocuments?.proof_of_address" class="text-slate-400 text-xs font-bold mt-3">
                                    No KYC documents uploaded yet.
                                </p>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-5">
                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Quick Info</p>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between"><span class="text-slate-500">Date of Birth</span><b class="text-slate-800">{{ viewingOrganizer.dob }}</b></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Place of Birth</span><b class="text-slate-800">{{ viewingOrganizer.placeBirth }}</b></div>
                                    <div class="flex justify-between"><span class="text-slate-500">SSN / Tax ID</span><b class="text-slate-800 font-mono">{{ maskSSN(viewingOrganizer.ssn) }}</b></div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4 flex items-center gap-1"><Tag class="h-3.5 w-3.5" /> Categories</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="c in (viewingOrganizer.categories?.length ? viewingOrganizer.categories : [viewingOrganizer.category])" :key="c" class="rounded-full bg-purple-50 text-purple-700 px-3 py-1 text-xs font-black">{{ c }}</span>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4 flex items-center gap-1"><SettingsIcon class="h-3.5 w-3.5" /> Settings</p>
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center justify-between"><span class="text-slate-600 font-bold">Show Followers</span><span class="h-5 w-5 rounded-md grid place-items-center text-white text-xs" :class="viewingOrganizer.settings?.show_followers ? 'bg-green-500' : 'bg-slate-300'">✓</span></div>
                                    <div class="flex items-center justify-between"><span class="text-slate-600 font-bold">Show Reviews</span><span class="h-5 w-5 rounded-md grid place-items-center text-white text-xs" :class="viewingOrganizer.settings?.show_reviews ? 'bg-green-500' : 'bg-slate-300'">✓</span></div>
                                    <div class="flex items-center justify-between"><span class="text-slate-600 font-bold">Show Venues Map</span><span class="h-5 w-5 rounded-md grid place-items-center text-white text-xs" :class="viewingOrganizer.settings?.show_venues_map ? 'bg-green-500' : 'bg-slate-300'">✓</span></div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-100 p-6 bg-white shadow-sm">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Performance</p>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between"><span class="text-slate-500">Linked Events</span><b class="text-slate-800">{{ num(viewingOrganizer.events) }}</b></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Tickets Sold</span><b class="text-slate-800">{{ num(viewingOrganizer.tickets) }}</b></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Revenue</span><b class="text-emerald-600">{{ fmt(viewingOrganizer.revenue) }}</b></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comprehensive Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-[100] flex items-start justify-center p-3 overflow-y-auto backdrop-blur-md">
            <div class="bg-white rounded-[40px] max-w-3xl w-full shadow-2xl my-4 overflow-hidden animate-in zoom-in duration-200">
                <div class="p-8 bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white flex justify-between items-center sticky top-0 z-[110]">
                    <div><h3 class="text-3xl font-black">{{ editingOrganizer ? 'Edit' : 'Add' }} Event Organizer</h3><p class="text-purple-100 font-bold mt-1 text-sm uppercase tracking-widest">Complete organizer profile, banking & KYC</p></div>
                    <button @click="closeModal" class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center hover:bg-white/30 transition">✕</button>
                </div>
                <div class="p-8 space-y-6 max-h-[75vh] overflow-y-auto scrollbar bg-slate-50/30">
                    <!-- Validation Errors -->
                    <div v-if="Object.keys(form.errors).length" class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 p-5 text-sm font-bold space-y-1">
                        <p v-for="(msg, key) in form.errors" :key="key">{{ msg }}</p>
                    </div>

                    <!-- Account Credentials -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Account Credentials</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="text-xs font-black uppercase text-slate-400 mb-2 block">
                                    Login Password
                                    <span v-if="!editingOrganizer" class="text-rose-500">*</span>
                                    <span v-else class="text-slate-400 normal-case font-medium">(leave blank to keep current password)</span>
                                </label>
                                <input v-model="form.accountPassword" type="password" autocomplete="new-password" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold text-slate-700" placeholder="Set an initial login password">
                                <p class="text-xs text-slate-400 mt-2">The organizer will log in using the email set below under Location & Contact.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Profile Details</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Organiser Name <span class="text-rose-500">*</span></label><input v-model="form.profileDetail.organizer_name" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold text-slate-700"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Date of Birth <span class="text-rose-500">*</span></label><input v-model="form.profileDetail.date_of_birth" type="date" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Place of Birth <span class="text-rose-500">*</span></label><input v-model="form.profileDetail.place_of_birth" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold" placeholder="Enter place of birth"></div>
                            <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Address <span class="text-rose-500">*</span></label><textarea v-model="form.profileDetail.address" rows="2" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold"></textarea></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Social Security / National ID <span class="text-rose-500">*</span></label><input v-model="form.profileDetail.ssn" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Telephone <span class="text-rose-500">*</span></label><input v-model="form.profileDetail.telephone" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Nationality <span class="text-rose-500">*</span></label><input v-model="form.profileDetail.nationality" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold"></div>
                            <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">About the Organiser <span class="text-rose-500">*</span></label><textarea v-model="form.profileDetail.about_the_organizer" rows="3" class="w-full rounded-2xl border border-slate-200 px-5 py-4 focus:ring-4 focus:ring-sky-100 transition outline-none font-bold" placeholder="Tell attendees about this organizer..."></textarea></div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Additional Details</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Organizer Logo</label><input type="file" @input="form.additionalDetails.logo = ($event.target as HTMLInputElement).files?.[0] || null" class="w-full text-[10px] text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Cover Photo</label><input type="file" @input="form.additionalDetails.cover_photo = ($event.target as HTMLInputElement).files?.[0] || null" class="w-full text-[10px] text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Organizer Photo</label><input type="file" @input="form.additionalDetails.profile_photo = ($event.target as HTMLInputElement).files?.[0] || null" class="w-full text-[10px] text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition"></div>
                        </div>
                    </div>

                    <!-- Social Handles -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Social Media Handles</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Facebook</label><input v-model="form.organizerMedia.facebook" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="https://"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Twitter / X</label><input v-model="form.organizerMedia.twitter" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="https://"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Instagram</label><input v-model="form.organizerMedia.instagram" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="https://"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">LinkedIn</label><input v-model="form.organizerMedia.linkedin" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="https://"></div>
                            <div class="md:col-span-2"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">YouTube Video URL</label><input v-model="form.organizerMedia.youtube" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="https://www.youtube.com/watch?v=..."></div>
                        </div>
                    </div>

                    <!-- Location & Contact -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Location & Contact</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Country <span class="text-rose-500">*</span></label><input v-model="form.organizerMedia.country" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="Bahamas"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">State / Province <span class="text-rose-500">*</span></label><input v-model="form.organizerMedia.state" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="New Providence"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">City <span class="text-rose-500">*</span></label><input v-model="form.organizerMedia.city" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="Nassau"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Website <span class="text-rose-500">*</span></label><input v-model="form.organizerMedia.website" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="https://"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Email <span class="text-rose-500">*</span></label><input v-model="form.organizerMedia.email" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="email@example.com"></div>
                            <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Phone <span class="text-rose-500">*</span></label><input v-model="form.organizerMedia.phone" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                        </div>
                    </div>

                    <!-- Visibility -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Profile Visibility Settings</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center justify-between rounded-2xl bg-slate-50 p-5 cursor-pointer hover:bg-slate-100 transition"><span class="font-bold text-slate-600">Show Venues Map</span><input v-model="form.profileVisibility.show_venues_map" :true-value="1" :false-value="0" type="checkbox" class="h-6 w-6 accent-green-600"></label>
                            <label class="flex items-center justify-between rounded-2xl bg-slate-50 p-5 cursor-pointer hover:bg-slate-100 transition"><span class="font-bold text-slate-600">Show Followers</span><input v-model="form.profileVisibility.show_followers" :true-value="1" :false-value="0" type="checkbox" class="h-6 w-6 accent-green-600"></label>
                            <label class="flex items-center justify-between rounded-2xl bg-slate-50 p-5 cursor-pointer hover:bg-slate-100 transition"><span class="font-bold text-slate-600">Show Reviews</span><input v-model="form.profileVisibility.show_reviews" :true-value="1" :false-value="0" type="checkbox" class="h-6 w-6 accent-green-600"></label>
                        </div>
                    </div>

                    <!-- Banking -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Banking Information</h4><button @click="addBank" type="button" class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">+</button></div>
                        <div v-for="(bank, index) in form.bankingInformation.banks" :key="index" class="space-y-4 mb-6 pb-6 border-b border-slate-100 last:border-0 last:mb-0 last:pb-0">
                            <div class="flex justify-between items-center"><span class="text-xs font-black text-slate-400 uppercase">Bank #{{ index + 1 }}</span><button v-if="form.bankingInformation.banks.length > 1" @click="removeBank(index)" type="button" class="text-rose-500 text-xs font-black">Remove</button></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Bank Name <span class="text-rose-500">*</span></label><input v-model="bank.bank_name" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="Scotiabank"></div>
                                <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Account Number <span class="text-rose-500">*</span></label><input v-model="bank.account_number" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-mono font-bold"></div>
                                <div><label class="text-xs font-black uppercase text-slate-400 mb-2 block">Routing Number <span class="text-rose-500">*</span></label><input v-model="bank.routing_number" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold"></div>
                            </div>
                        </div>
                        <div class="mt-4"><label class="text-xs font-black uppercase text-slate-400 mb-2 block">PayPal ID (Email)</label><input v-model="form.bankingInformation.paypal_id" class="w-full rounded-2xl border border-slate-200 px-5 py-4 font-bold" placeholder="Enter PayPal email"></div>
                    </div>

                    <!-- KYC -->
                    <div class="card rounded-[32px] p-8 border-slate-200/60 shadow-sm">
                        <div class="flex items-center justify-between border-b-2 border-sky-500 pb-4 mb-6"><h4 class="text-2xl font-black text-slate-800">Organiser KYC</h4><div class="h-8 w-8 rounded-full bg-sky-500 text-white grid place-items-center font-black">−</div></div>
                        <div class="space-y-6">
                            <div>
                                <p class="text-sm font-bold text-slate-600 mb-1">1. Passport Verification</p>
                                <p class="text-xs text-slate-500 mb-3">Upload clear photos of the front and back of your valid passport.</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-[10px] uppercase font-black text-slate-400 block mb-1">Front</label><input type="file" @input="form.organizerKYC.passport_front = ($event.target as HTMLInputElement).files?.[0] || null" class="w-full text-xs"></div>
                                    <div><label class="text-[10px] uppercase font-black text-slate-400 block mb-1">Back</label><input type="file" @input="form.organizerKYC.passport_back = ($event.target as HTMLInputElement).files?.[0] || null" class="w-full text-xs"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-600 mb-1">2. Proof of Address</p>
                                <p class="text-xs text-slate-500 mb-3">Upload a document confirming your current residential address.</p>
                                <input type="file" @input="form.organizerKYC.proof_of_address = ($event.target as HTMLInputElement).files?.[0] || null" class="w-full text-xs">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-200 flex justify-end gap-4 sticky bottom-0 bg-white z-[110]">
                    <button @click="closeModal" class="rounded-2xl bg-slate-100 px-10 py-4 font-black text-slate-500 hover:bg-slate-200 transition">Cancel</button>
                    <button @click="saveOrganizer" :disabled="form.processing" class="rounded-2xl bg-purple-600 text-white px-12 py-4 font-black shadow-xl shadow-purple-100 hover:bg-purple-700 transition active:scale-95 uppercase tracking-wider">
                        <span v-if="form.processing" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2 inline-block"></span>
                        {{ editingOrganizer ? 'Update Profile' : 'Add Organizer' }}
                    </button>
                </div>
            </div>
        </div>

        <Toaster rich-colors position="top-right" />
    </div>
</template>
