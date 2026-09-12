<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import ConfirmDeleteDialog from '@/components/admin/ConfirmDeleteDialog.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { BadgeDollarSign, Calendar, CircleCheck, CircleX, Image as ImageIcon, Plus, X, Pencil, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    initialSponsors: any[];
    events: any[];
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
const sponsors = ref([...props.initialSponsors]);
const sponsorSearch = ref('');
const sponsorStatusFilter = ref('All Statuses');

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

// Sponsor Specific Logic
const filteredSponsors = computed(() => {
    const q = sponsorSearch.value.toLowerCase().trim();
    const st = sponsorStatusFilter.value;
    return sponsors.value.filter(s => {
        const hay = [s.sponsor, s.event, s.country, s.package, s.status, s.submittedBy, s.notes, s.description].join(' ').toLowerCase();
        return (!q || hay.includes(q)) && (st === 'All Statuses' || s.status === st);
    });
});

const stats = computed(() => {
    const all = sponsors.value;
    return {
        total: all.length,
        active: all.filter(s => s.status === 'Active').length,
        inactive: all.filter(s => s.status === 'Inactive').length,
        linkedEvents: new Set(all.map(s => s.link_up_event_id).filter(Boolean)).size,
        mediaCount: all.filter(s => s.image_object).length
    };
});

// Modal State
const showModal = ref(false);
const editingSponsor = ref<any>(null);

const form = useForm({
    id: 0,
    name: '',
    description: '',
    status: 1,
    link_up_event_id: null as number | null,
    sponsor_image_file: null as File | null,
    _method: 'POST'
});

const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const openModal = (id: number | null = null) => {
    if (id) {
        const s = sponsors.value.find(x => x.id === id);
        if (s) {
            editingSponsor.value = s;
            form.id = s.id;
            form.name = s.name;
            form.description = s.description;
            form.status = s.status === 'Active' ? 1 : 0;
            form.link_up_event_id = s.link_up_event_id;
            form._method = 'PUT';
            imagePreview.value = getMediaUrl(s.image_object);
        }
    } else {
        editingSponsor.value = null;
        form.reset();
        form.id = 0;
        form.status = 1;
        form._method = 'POST';
        imagePreview.value = null;
    }
    showModal.value = true;
};

const handleImageUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.sponsor_image_file = file;

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
    editingSponsor.value = null;
    imagePreview.value = null;
    form.reset();
    form.clearErrors();
};

const saveSponsor = () => {
    const isUpdate = form.id > 0;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success(isUpdate ? 'Sponsor updated successfully' : 'Sponsor created successfully');
        },
        onError: () => {
            toast.error(isUpdate ? 'Failed to update sponsor' : 'Failed to create sponsor');
        },
    };

    if (isUpdate) {
        form.post(route('admin.sponsors.update', form.id), options);
    } else {
        form.post(route('admin.sponsors.store'), options);
    }
};

const deletingSponsorId = ref<number | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

const deleteSponsor = (id: number) => {
    deletingSponsorId.value = id;
    showDeleteDialog.value = true;
};

function confirmDeleteSponsor() {
    if (!deletingSponsorId.value) return;
    const url = route('admin.sponsors.destroy', deletingSponsorId.value);
    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Sponsor deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete sponsor');
        },
        onFinish: () => (showDeleteDialog.value = false),
    });
}

// Sync ref when props update from backend
watch(() => props.initialSponsors, (newVal) => {
    sponsors.value = [...newVal];
}, { deep: true });

// Get media URL helper
const getMediaUrl = (path: string | null | undefined): string | null => {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return `/${path}`;
    return `/storage/${path.replace(/^\//, '')}`;
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
    <Head title="Event Sponsors" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eventSponsorsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Event Sponsors" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="space-y-6 p-5 lg:p-8">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black text-purple-600">Event Sponsors</h3>
                        <p class="text-slate-500">Manage sponsor ads submitted by event organizers. Admins can add sponsors and make sponsor ads active or inactive.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <input v-model="sponsorSearch" class="rounded-2xl border border-slate-200 px-4 py-2 w-full xl:w-96" placeholder="Search sponsors, events, descriptions, status...">
                        <select v-model="sponsorStatusFilter" class="rounded-2xl border border-slate-200 px-4 py-2 font-bold bg-white">
                            <option>All Statuses</option>
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                        <button @click="openModal()" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-5 py-2 font-black shadow-lg shadow-purple-200 flex items-center gap-2 transition hover:scale-105 active:scale-95">
                            <Plus class="h-5 w-5" /> Add Sponsor
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4 xl:col-span-2">
                        <div class="h-14 w-14 rounded-2xl bg-purple-100 text-purple-600 grid place-items-center"><BadgeDollarSign class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Total Sponsors</p><h3 class="text-4xl font-black">{{ num(stats.total) }}</h3><p class="text-xs text-slate-500">Sponsor ads in the system</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-green-100 text-green-600 grid place-items-center"><CircleCheck class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Active Ads</p><h3 class="text-4xl font-black">{{ num(stats.active) }}</h3></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-rose-100 text-rose-600 grid place-items-center"><CircleX class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Inactive Ads</p><h3 class="text-4xl font-black">{{ num(stats.inactive) }}</h3></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="card rounded-3xl p-5 flex items-center gap-4 xl:col-span-2">
                        <div class="h-14 w-14 rounded-2xl bg-sky-100 text-sky-600 grid place-items-center"><Calendar class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Linked Events</p><h3 class="text-4xl font-black">{{ num(stats.linkedEvents) }}</h3><p class="text-xs text-slate-500">Events using sponsor ads</p></div>
                    </div>
                    <div class="card rounded-3xl p-5 flex items-center gap-4 xl:col-span-2">
                        <div class="h-14 w-14 rounded-2xl bg-sky-100 text-sky-600 grid place-items-center"><ImageIcon class="h-8 w-8" /></div>
                        <div><p class="text-slate-500 font-bold">Media Uploaded</p><h3 class="text-4xl font-black">{{ num(stats.mediaCount) }}</h3><p class="text-xs text-slate-500">Images / videos</p></div>
                    </div>
                </div>

                <div class="card rounded-3xl p-6 shadow-sm">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-8">
                        <div>
                            <h3 class="text-2xl font-black">Sponsor Ads</h3>
                            <p class="text-slate-500">These are sponsor ads connected to events. Use Active / Inactive to control whether the ad shows on the front end.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                                <tr>
                                    <th class="py-4 px-6">Sponsor</th>
                                    <th>Description</th>
                                    <th>Linked Event</th>
                                    <th>Media</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in filteredSponsors" :key="s.id" class="border-t border-slate-100 align-top hover:bg-slate-50 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-14 w-14 rounded-2xl bg-slate-100 overflow-hidden flex-shrink-0 grid place-items-center text-3xl shadow-sm border border-slate-200">
                                                <img v-if="getMediaUrl(s.image_object || s.sponsor_image_object)" :src="getMediaUrl(s.image_object || s.sponsor_image_object)!" class="w-full h-full object-cover" alt="Sponsor logo">
                                                <span v-else>🏷️</span>
                                            </div>
                                            <div>
                                                <b class="text-slate-800 block text-lg leading-tight">{{ s.name }}</b>
                                                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">{{ s.country || 'Bahamas' }} • {{ s.id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 max-w-md"><p class="text-sm text-slate-600 line-clamp-2 leading-relaxed">{{ s.description }}</p></td>
                                    <td class="py-4">
                                        <span v-if="s.event_name && s.event_name !== '—'" class="rounded-full bg-purple-50 text-purple-700 px-3 py-1 text-xs font-black border border-purple-100">
                                            {{ s.event_name }}
                                        </span>
                                        <span v-else-if="s.event && s.event !== '—' && s.event !== s.name" class="rounded-full bg-purple-50 text-purple-700 px-3 py-1 text-xs font-black border border-purple-100">
                                            {{ s.event }}
                                        </span>
                                        <span v-else class="text-slate-400 text-xs font-bold uppercase">No Event</span>
                                    </td>
                                    <td class="py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black border" :class="(s.image_object || s.sponsor_image_object) ? 'bg-sky-50 text-sky-700 border-sky-100' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                            {{ (s.image_object || s.sponsor_image_object) ? 'Image' : 'Logo' }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black border transition-colors" :class="s.status === 'Active' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-100 text-slate-600 border-slate-200'">
                                            {{ s.status }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <button @click="openModal(s.id)" class="rounded-xl bg-slate-100 text-slate-700 px-3 py-2 text-xs font-black hover:bg-slate-200 transition">Edit</button>
                                            <button @click="deleteSponsor(s.id)" class="rounded-xl bg-rose-50 text-rose-700 border border-rose-100 px-3 py-2 text-xs font-black hover:bg-rose-100 transition">Delete</button>
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
                        <h3 class="text-4xl font-black">{{ editingSponsor ? 'Edit Sponsor' : 'Create Sponsor' }}</h3>
                        <p class="text-purple-100 text-xl mt-3">Fill in the details to configure the sponsor ad</p>
                    </div>
                    <button @click="closeModal" class="text-white/80 hover:text-white transition hover:scale-110">
                        <X class="w-10 h-10" />
                    </button>
                </div>

                <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto scrollbar">
                    <!-- 1. Sponsor Media Input -->
                    <div>
                        <p class="text-slate-700 text-lg font-bold mb-4">Sponsor Media <span class="text-slate-400 font-medium">• Max 20MB</span></p>
                        <div
                            @click="triggerFileInput"
                            class="h-72 w-full rounded-[2rem] border-2 border-dashed border-slate-300 bg-slate-50 grid place-items-center text-center cursor-pointer overflow-hidden relative group hover:border-purple-400 hover:bg-purple-50 transition-all"
                        >
                            <div v-if="!imagePreview" id="sponsorMediaPreview" class="p-4">
                                <div class="h-24 w-24 rounded-full bg-purple-100 text-purple-600 grid place-items-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                                    <ImageIcon class="w-12 h-12" />
                                </div>
                                <p class="text-3xl text-slate-500 font-black">Click to upload image or video</p>
                                <p class="text-slate-400 mt-2 text-lg font-bold">JPG, PNG, WebP, MP4, WebM</p>
                            </div>
                            <div v-else class="w-full h-full">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <p class="text-white font-black text-2xl">Change Media</p>
                                </div>
                            </div>
                        </div>
                        <input type="file" ref="fileInput" class="hidden" accept="image/*,video/*" @change="handleImageUpload">
                        <p v-if="form.errors.sponsor_image_file" class="text-rose-500 text-sm mt-2 font-bold">{{ form.errors.sponsor_image_file }}</p>
                    </div>

                    <!-- 2. Event Link -->
                    <div>
                        <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4"><Calendar class="text-slate-400 w-6 h-6" /> Link to Event</label>
                        <select v-model="form.link_up_event_id" class="w-full rounded-2xl border border-purple-100 bg-white px-6 py-5 text-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-purple-100 appearance-none transition-all">
                            <option :value="null">Select an event</option>
                            <option v-for="e in events" :key="e.value" :value="e.value">{{ e.label }}</option>
                        </select>
                        <p v-if="form.errors.link_up_event_id" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.link_up_event_id }}</p>
                    </div>

                    <!-- 3. Sponsor Name -->
                    <div>
                        <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4">Sponsor Name</label>
                        <input
                            v-model="form.name"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-6 py-5 text-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-purple-50 focus:border-purple-200 transition-all shadow-sm"
                            placeholder="e.g., Kalik Beer"
                        >
                        <p v-if="form.errors.name" class="text-rose-500 text-sm mt-1 font-bold">{{ form.errors.name }}</p>
                    </div>

                    <!-- 4. Description -->
                    <div>
                        <label class="flex items-center gap-3 text-slate-700 text-lg font-bold mb-4">Description / Note</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5 text-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-purple-50 transition-all"
                            placeholder="Describe the sponsorship or add notes..."
                        ></textarea>
                    </div>

                    <!-- 5. Status Selection -->
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
                    <button @click="saveSponsor" :disabled="form.processing" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-10 py-5 text-xl font-black shadow-xl shadow-purple-200 hover:opacity-90 transition disabled:opacity-50 flex items-center gap-3">
                        <span v-if="form.processing" class="animate-spin h-6 w-6 border-4 border-white border-t-transparent rounded-full"></span>
                        {{ form.processing ? 'Saving...' : (editingSponsor ? 'Update Sponsor' : 'Create Sponsor') }}
                    </button>
                </div>
            </div>
        </div>

        <ConfirmDeleteDialog
            v-model="showDeleteDialog"
            :form="deleteForm"
            title="Delete Sponsor"
            description="Are you sure you want to delete this sponsor? This action cannot be undone."
            @submit="confirmDeleteSponsor"
        />

        <Toaster rich-colors position="top-right" />
    </div>
</template>
