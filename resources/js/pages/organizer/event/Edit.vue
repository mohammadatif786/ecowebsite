<template>
    <AppLayout>

        <Head :title="`Update ${props.events?.title}`" />

        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <!-- Modal Content -->
            <div
                class="modal-scroll bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-transform">
                <div class="p-5">
                    <div
                        class="flex items-center justify-between mb-4 sticky top-0 bg-white/90 backdrop-blur-sm z-10 py-2 border-b border-slate-100 -mx-5 px-5 -mt-5">
                        <h3 class="text-xl font-black">Edit Event: <span class="text-blue-600">{{ props.events?.title
                        }}</span></h3>
                        <Link :href="route('organizer.event.index')"
                            class="text-slate-500 hover:text-slate-800 transition bg-slate-100 hover:bg-slate-200 p-2 rounded-full">
                            <X class="w-5 h-5" />
                        </Link>
                    </div>

                    <form @submit.prevent="handleSubmit">
                        <Media v-model="mediaFiles" :mediaFiles="[]" :events="props.events"
                            :errors="form.errors.mediaFiles" :appURL="appURL" />
                        <EventDetails :categories="props.categories" :eventDetails="eventDetails" v-model="eventDetails"
                            :allErrors="form.errors" @update:isCookout="isCookout = $event"
                            @update:isWellness="isWellness = $event" />
                        <DateTime v-model="dateTime" :dateTime="dateTime" :eventDetails="event_detail"
                            :errors="dateTimeErrors" />
                        <AdditionalOptions v-model="additionalOptions" :additionalOptions="additionalOptions"
                            :caribbeans="props.caribbeans" :allErrors="form.errors" :appURL="appURL"
                            :isCookout="isCookout" :isWellness="isWellness" />
                        <Location v-model="location" :events="props.events" :errors="locationErrors" :isCookout="isCookout"
                            :isWellness="isWellness" />
                        <Scanner :scanners="props.scanners" :eventDetail="props.event_detail" v-model="selectedScanners"
                            :errors="scannerErrors" />
                        <SocialMedia v-model="socialMedia" :socialMedia="socialMedia" :errors="socialMediaErrors"
                            v-show="!isCookout && !isWellness" />

                        <!-- Ticket notice matching reference -->
                        <div
                            class="rounded-2xl bg-slate-50 border border-slate-100 p-3 mb-4 text-[11px] text-slate-500 font-bold flex items-center gap-2">
                            <Ticket class="w-4 h-4 shrink-0" /> Ticket price and promoter commission are now set per
                            ticket — add a ticket from My Tickets after publishing.
                        </div>

                        <label class="flex items-center gap-2 mt-1 mb-3 font-bold text-sm">
                            <input type="checkbox" v-model="form.post_as_organization" /> Post as Organization / Group
                        </label>

                        <button type="submit" id="create-event-btn"
                            class="btn btn-primary bg-blue-500 rounded-lg text-white font-bold w-full py-3">
                            {{ form.processing ? 'Updating...' : 'Update Event' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Ticket, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import Media from './components/Media.vue';
import Coupons from './components/Coupons.vue';
import Sponsors from './components/Sponsors.vue';
import Location from './components/Location.vue';
import AdditionalOptions from './components/AdditionalOptions.vue';
import DateTime from './components/DateTime.vue';
import EventDetails from './EventDetails.vue';
import Scanner from './components/Scanner.vue';
import SocialMedia from './components/SocialMedia.vue';
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from "vue-sonner";

const props = defineProps<{
    categories: Array<{ id: number; name: string }>;
    caribbeans: any[];
    scanners: any[];
    events: any;
    event_detail: any;
    appURL: string;
    sponsor?: any[];
    coupon?: any[];
    allEvent?: any[];
}>()

const mediaFiles = ref<File[]>([]);
const location = ref({
    location_type: props.events?.location_type ?? 'venue',
    venue: props.events?.venue ?? '',
    latitude: props.events?.latitude ?? null,
    longtitude: props.events?.longtitude ?? null,
});
const eventDetails = ref({
    name: props.events?.title ?? "",
    description: props.events?.description ?? "",
    category_id: props.events?.category_id ?? "",
    audiences: props.event_detail?.audiences ?? "",
    attendees: props.event_detail?.attendees == 1 ? "show" : "hide",
    reviews: props.event_detail?.enable_views == 1 ? "enable" : "disable",
    seating_plan: props.event_detail?.seating_plan == 1 ? "yes" : "no",

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
    disclaimer: props.events?.disclaimer ?? "",
    gallery: props.event_detail?.image_gallery ?? "",
    artists: props.event_detail?.artists ?? [],
    zip: props.event_detail?.zip ?? "",
    tax_rate: props.event_detail?.tax_rate ?? "",
    tax_included: props.event_detail?.tax_included ?? "no",
    artist_image: (() => {
        const artists = props.event_detail?.artists ?? [];
        const existingArtistImages = props.event_detail?.artist_image ?? [];

        // If we have artists but no artist_image data, initialize with null values
        if (artists.length > 0 && existingArtistImages.length === 0) {
            return new Array(artists.length).fill(null);
        }

        // If we have both artists and artist_image data, ensure they have the same length
        if (artists.length > 0 && existingArtistImages.length > 0) {
            if (artists.length !== existingArtistImages.length) {
                // Pad or truncate to match artists length
                const synchronized = [...existingArtistImages];
                while (synchronized.length < artists.length) {
                    synchronized.push(null);
                }
                while (synchronized.length > artists.length) {
                    synchronized.pop();
                }
                return synchronized;
            }
        }

        return existingArtistImages;
    })()
});
const selectedScanners = ref<any[]>([]);
const socialMedia = ref({
    twitter: props.event_detail?.twitter ?? "",
    instagram: props.event_detail?.instagram ?? "",
    facebook: props.event_detail?.facebook ?? "",
    tiktok: props.event_detail?.tiktok ?? "",
    linkedin: props.event_detail?.linkedin ?? ""
});

const isCookout = ref(false);
const isWellness = ref(false);

watch(() => eventDetails.value.category_id, (newId) => {
    if (!newId || !props.categories) {
        isCookout.value = false;
        isWellness.value = false;
        return;
    }
    const category = props.categories.find((c: any) => String(c.id) === String(newId));
    const name = category?.name?.trim().toLowerCase() || '';

    isCookout.value = name.includes('cookout');
    isWellness.value = name.includes('wellness');

    console.log('Category Selection Watch (Edit):', { id: newId, name, isCookout: isCookout.value, isWellness: isWellness.value });
}, { immediate: true });

const form = useForm({
    mediaFiles: null,
    eventDetails: {},
    dateTime: {},
    additionalOptions: {},
    scanners: [],
    socialMedia: {},
    location_type: "",
    venue: "",
    latitude: null,
    longtitude: null,
    post_as_organization: props.events?.post_as_organization == 1 ? true : false
});

const handleSubmit = () => {

    form.mediaFiles = mediaFiles.value;
    form.eventDetails = eventDetails.value;
    form.dateTime = dateTime.value;
    form.additionalOptions = additionalOptions.value;
    form.scanners = selectedScanners.value;
    form.socialMedia = socialMedia.value;
    form.location_type = location.value.location_type;
    form.venue = location.value.venue;
    form.latitude = location.value.latitude;
    form.longtitude = location.value.longtitude;

    form.post(route('organizer.event.update', { event: props.events.id }), {
        forceFormData: true,
        onSuccess: () => {
            toast.success("Event Update Successfully");
        },
        onError: (errors) => {
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
            // Optional: remove prefix from message
            return (value as string).replace("The social media.", "The ");
        });
});

const locationErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key === "location_type" || key === "venue")
        .map(([, value]) => value as string);
});

</script>

<style scoped>
.modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.modal-scroll::-webkit-scrollbar {
    width: 6px;
}
.modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.modal-scroll::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
