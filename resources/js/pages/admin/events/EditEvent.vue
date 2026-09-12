<template>
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eventMgmtCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Edit Event"
                :countries="initialCountries"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <section class="p-5 lg:p-8">
                <div class="card rounded-3xl p-6 lg:p-10">
                    <h1 class="text-3xl font-black text-purple-600 mb-4 text-center">
                        Update " <label class="text-black">{{ props.events?.title }}</label> "
                    </h1>

                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <OrganizerDropDown :organizer_details="props.organizer" :events="props.events" v-model="organizerData.organizer_id" />

                        <Media v-model="mediaFiles" :mediaFiles="mediaFiles" :events="events" :errors="form.errors.mediaFiles"
                            :appURL="appURL" />
                        <EventDetails :categories="props.categories" :eventDetails="eventDetails" v-model="eventDetails"
                            :errors="eventDetailErrors" />
                        <DateTime v-model="dateTime" :dateTime="dateTime" :eventDetails="event_detail"
                            :errors="dateTimeErrors" />
                        <AdditionalOptions v-model="additionalOptions" :additionalOptions="additionalOptions"
                            :caribbeans="props.caribbeans" :errors="additionalOptionsErrors" :appURL="appURL" />
                        <Location v-model="location" :events="props.events" />
                        <Scanner :scanners="props.scanners" :eventDetail="props.event_detail" v-model="selectedScanners"
                            :errors="scannerErrors" />
                        <SocialMedia v-model="socialMedia" :socialMedia="socialMedia" :errors="socialMediaErrors" />

                        <div class="pt-6">
                            <button type="submit" class="submit-btn w-full md:w-auto mx-auto block" id="create-event-btn">
                                Update Event
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
    categories: Array<{ id: number; name: string }>;
    caribbeans: Array<Record<string, any>>;
    scanners: Array<Record<string, any>>;
    events: {
        id: number;
        title: string;
        description: string;
        category_id: number;
        type: string;
        email: string;
        phone: string;
        website: string;
        country: string;
        state: string;
        city: string;
        disclaimer: string;
        location_type: string;
        venue: string;
        latitude: string;
        longtitude: string;
        organizer_id: number;
        organizer_name: string;
    };
    event_detail: {
        audiences: string[];
        attendees: string;
        enable_views: string;
        seating_plan: string;
        venue: string;
        event_type: string;
        single_event_date: string;
        single_start_time: string;
        single_end_time: string;
        recurr_pattern: string;
        recurr_start_date: string;
        recurr_end_date: string;
        zip: string;
        tax_rate: string;
        tax_included: string;
        image_gallery: string[];
        artists: string[];
        artist_image: string[];
        twitter: string;
        instagram: string;
        facebook: string;
        tiktok: string;
        linkedin: string;
    };
    appURL: string;
    sponsor: Array<Record<string, any>>;
    coupon: Array<Record<string, any>>;
    allEvent: Array<Record<string, null>>;
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
    name: props.events?.title ?? "",
    description: props.events?.description ?? "",
    category_id: props.events?.category_id ?? "",
    audiences: props.event_detail?.audiences ?? "",
    attendees: props.event_detail?.attendees == "show" || props.event_detail?.attendees == "1" || props.event_detail?.attendees === true ? "show" : "hide",
    reviews: props.event_detail?.enable_views == "enable" || props.event_detail?.enable_views == "1" || props.event_detail?.enable_views === true ? "enable" : "disable",
    seating_plan: props.event_detail?.seating_plan == "yes" || props.event_detail?.seating_plan == "1" || props.event_detail?.seating_plan === true ? "yes" : "no",
    venue: props.events?.venue ?? ""
});
const dateTime = ref({
    eventType: props.event_detail?.event_type ?? '',
    singleDate: props.event_detail?.single_event_date ?? '',
    singleStartTime: props.event_detail?.single_start_time ?? '',
    singleEndTime: props.event_detail?.single_end_time ?? '',
    recurrPattern: props.event_detail?.recurr_pattern ?? '',
    recurrStartDate: props.event_detail?.recurr_start_date ?? '',
    recurrEndDate: props.event_detail?.recurr_end_date ?? '',
});
const additionalOptions = ref({
    type: props.events?.type ?? "",
    email: props.events?.email ?? "",
    phone: props.events?.phone ?? "",
    website: props.events?.website ?? "",
    country: props.events?.country ?? "",
    state: props.events?.state ?? "",
    city: props.events?.city ?? "",
    zip: props.event_detail?.zip ?? '',
    tax_rate: props.event_detail?.tax_rate ?? '',
    tax_included: props.event_detail?.tax_included ?? '',
    disclaimer: props.events?.disclaimer ?? "",
    gallery: Array.isArray(props.event_detail?.image_gallery) ? props.event_detail.image_gallery : [],
    artists: Array.isArray(props.event_detail?.artists) ? props.event_detail.artists : [],
    artist_image: Array.isArray(props.event_detail?.artist_image) ? props.event_detail.artist_image : []
});
const selectedScanners = ref<any[]>([]);
const socialMedia = ref({
    twitter: props.event_detail?.twitter ?? "",
    instagram: props.event_detail?.instagram ?? "",
    facebook: props.event_detail?.facebook ?? "",
    tiktok: props.event_detail?.tiktok ?? "",
    linkedin: props.event_detail?.linkedin ?? ""
});
const location = ref({
    location_type: props.events?.location_type ?? 'venue',
    venue: props.events?.venue ?? '',
    latitude: props.events?.latitude ?? null,
    longtitude: props.events?.longtitude ?? null,
});
const organizerData = ref({
    organizer_id: props?.events?.organizer_id ?? "",
    organizer_name: props?.organizer?.find(o => o.id === props?.events?.organizer_id)?.organizer_name ?? ""
});
const form = useForm({
    mediaFiles: null as File[] | null,
    eventDetails: {},
    dateTime: {},
    additionalOptions: {},
    scanners: [],
    socialMedia: {},
    organizer_id: null as any,
    location_type: "",
    venue: "",
    latitude: null as any,
    longtitude: null as any,
});

const handleSubmit = () => {
    form.mediaFiles = mediaFiles.value;
    form.eventDetails = eventDetails.value;
    form.dateTime = dateTime.value;
    form.additionalOptions = additionalOptions.value;
    form.scanners = selectedScanners.value;
    form.socialMedia = socialMedia.value;
    form.organizer_id = organizerData.value.organizer_id;
    form.location_type = location.value.location_type;
    form.venue = location.value.venue;
    form.latitude = location.value.latitude ?? null;
    form.longtitude = location.value.longtitude ?? null;

    form.post(route('admin.event.updated', { event: props.events.id }), {
        forceFormData: true,
        onSuccess: () => {
            toast.success("Event Updated Successfully");
        },
        onError: (errors) => {
            toast.error("Failed to update event");
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
