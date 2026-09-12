<template>
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eventMgmtCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Create Event"
                :countries="initialCountries"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <section class="p-5 lg:p-8">
                <div class="card rounded-3xl p-6 lg:p-10">
                    <h1 class="text-3xl font-black text-purple-600 mb-4 text-center">Create a New Event</h1>

                    <p class="text-slate-500 mb-8 text-center max-w-3xl mx-auto">
                        To create your event, start by uploading photos and videos to make it visually appealing.
                        Click the plus sign in the media section to select files. For each section below,
                        click the plus icon to reveal the fields and enter the required information.
                    </p>

                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <OrganizerDropDown :organizer_details="props.organizer" v-model="form.organizer_id" />
                        <Media v-model="mediaFiles" :errors="form.errors.mediaFiles" />
                        <EventDetails :categories="props.categories" v-model="eventDetails" :errors="eventDetailErrors" />
                        <DateTime v-model="dateTime" :errors="dateTimeErrors" />
                        <AdditionalOptions v-model="additionalOptions" :caribbeans="props.caribbeans"
                            :errors="additionalOptionsErrors" />
                        <Location v-model="location" :errors="locationErrors" />
                        <Scanner :scanners="props.scanners" v-model="selectedScanners" :errors="scannerErrors" />
                        <SocialMedia v-model="socialMedia" :errors="socialMediaErrors" />

                        <div class="pt-6">
                            <button type="submit" class="submit-btn w-full md:w-auto mx-auto block" id="create-event-btn">
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <Toaster rich-colors position="top-right" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { Toaster, toast } from "vue-sonner";
import 'vue-sonner/style.css';
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';

// Shared event form components preserve the existing event create/edit logic
import Media from './components/Media.vue';
import EventDetails from './components/EventDetails.vue';
import DateTime from './components/DateTime.vue';
import AdditionalOptions from './components/AdditionalOptions.vue';
import Location from './components/Location.vue';
import Scanner from './components/Scanner.vue';
import SocialMedia from './components/SocialMedia.vue';
import OrganizerDropDown from './components/OrganizerDropDown.vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    categories: Array<Record<string, any>>;
    caribbeans: Array<Record<string, any>>;
    scanners: Array<Record<string, any>>;
    events: Array<Record<string, any>>;
    allEvents: Array<Record<string, any>>;
    organizer: Array<{ id: number; organizer_name: string }>;
    initialUnits: any[];
    initialCountries: any[];
}>()

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// GTV Logic
const SCOTIA_SHARE = 0.4;
const units = props.initialUnits;
const countries = props.initialCountries;

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
    return (countries || []).filter(
        (c) =>
            (filters.value.region === 'All' || c.region === filters.value.region) &&
            (filters.value.country === 'All Countries' || c.country === filters.value.country),
    );
};

const countryFin = (c: any) => {
    let gross = 0, platform = 0, bank = 0, cost = 0;
    (units || []).forEach((u) => {
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
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});

const mediaFiles = ref<File[]>([]);
const eventDetails = ref({
    name: "",
    description: "",
    category_id: null,
    audiences: [],
    attendees: "show",
    reviews: "enable",
    seating_plan: "no",
    venue: ""
});
const dateTime = ref({
    eventType: "single",
    singleDate: null,
    singleStartTime: null,
    singleEndTime: null,
    recurrPattern: null,
    recurrStartDate: null,
    recurrEndDate: null,
});
const additionalOptions = ref({
    type: "public",
    email: "",
    phone: "",
    website: "",
    country: "",
    state: "",
    city: "",
    zip:"",
    tax_rate:"",
    tax_included:"",
    disclaimer: "",
    gallery: [],
    artists: [],
    artist_image: []
});
const selectedScanners = ref<any[]>([]);
const socialMedia = ref({
    twitter: "",
    instagram: "",
    facebook: "",
    tiktok: "",
    linkedin: ""
});
const organizerData = ref({
    organizer_id: "",
    organizer_name: ""
});
const location = ref({
    location_type: "venue",
    venue: "",
    latitude: null,
    longtitude: null,
});
const form = useForm({
    mediaFiles: null as any,
    eventDetails: {},
    dateTime: {},
    additionalOptions: {},
    location_type: "",
    venue: "",
    latitude: null,
    longtitude: null,
    scanners: [],
    socialMedia: {},
    organizer_id: null,
    organizerData: {} // Added to match handleSubmit logic
});

const handleSubmit = () => {
    form.mediaFiles = mediaFiles.value;
    form.eventDetails = eventDetails.value;
    form.dateTime = dateTime.value;
    form.additionalOptions = additionalOptions.value;
    form.scanners = selectedScanners.value;
    form.socialMedia = socialMedia.value;
    form.organizerData = organizerData.value
    form.location_type = location.value.location_type;
    form.venue = location.value.venue;
    form.latitude = location.value.latitude;
    form.longtitude = location.value.longtitude;

    form.post(route('admin.event.store'), {
        onSuccess: () => {
            toast.success("Event Created Successfully");
        },
        onError: (errors) => {
            toast.error("Failed to create event");
            console.error("Form submission errors:", errors);
        },
    });
};

const eventDetailErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key.startsWith("eventDetails."))
        .map(([, value]) => value as string);
});
const dateTimeErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key.startsWith("dateTime."))
        .map(([, value]) => {
            return (value as string).replace("The date time.", "The ");
        });
});
const additionalOptionsErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key.startsWith("additionalOptions."))
        .map(([, value]) => {
            return (value as string).replace("The additional options.", "The ");
        });
});
const scannerErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key === "scanners")
        .map(([, value]) => value as string);
});
const socialMediaErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key.startsWith("socialMedia."))
        .map(([, value]) => {
            return (value as string).replace("The social media.", "The ");
        });
});
</script>

<style scoped>
.submit-btn {
    background: linear-gradient(90deg, #00AEEF 0%, #FFEB3B 100%);
    color: #fff;
    padding: 15px 40px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.3em;
    font-weight: 600;
    transition: transform 0.3s ease;
}

.submit-btn:hover {
    transform: scale(1.05);
}
</style>
