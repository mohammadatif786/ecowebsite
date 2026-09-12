<template>
    <div v-if="asModal">
        <!-- Modal Backdrop -->
        <div v-if="showModal"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] flex items-center justify-center p-4 overflow-hidden"
            @click="handleBackdropClick">
            <!-- Modal Content -->
            <div
                class="bg-white rounded-[20px] w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col" @click.stop>
                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <div
                        class="flex items-center justify-between mb-4 sticky top-0 bg-white/90 backdrop-blur-sm z-10 py-2 border-b border-slate-100 -mx-5 px-5 -mt-5">
                        <h3 class="text-xl font-black">Create a New Event</h3>
                        <button @click="close"
                            class="text-slate-500 hover:text-slate-800 transition bg-slate-100 hover:bg-slate-200 p-2 rounded-full">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="handleSubmit">
                        <Media v-model="mediaFiles" :errors="form.errors.mediaFiles" :appURL="props.appURL" />
                        <EventDetails :categories="props.categories" v-model="eventDetails" :allErrors="form.errors"
                            :eventDetails="[]" @update:isCookout="isCookout = $event"
                            @update:isWellness="isWellness = $event" />
                        <DateTime v-model="dateTime" :errors="dateTimeErrors" />
                        <AdditionalOptions v-model="additionalOptions" :caribbeans="props.caribbeans"
                            :allErrors="form.errors" :isCookout="isCookout" :isWellness="isWellness"
                            :appURL="props.appURL || ''" :additionalOptions="[]" :events="props.events" />
                        <Location v-model="location" :errors="locationErrors" :isCookout="isCookout"
                            :isWellness="isWellness" :events="props.events" />
                        <Scanner :scanners="props.scanners" v-model="selectedScanners" :errors="scannerErrors" />
                        <SocialMedia v-model="socialMedia" :errors="socialMediaErrors"
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
                            {{ form.processing ? 'Creating...' : 'Publish Event' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <AppLayout v-else>
        <Head :title="'Create a New Event'" />

        <!-- Modal Backdrop (Full Page version) -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-hidden"
            @click="handleBackdropClick">
            <!-- Modal Content -->
            <div
                class="bg-white rounded-[20px] w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col" @click.stop>
                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <div
                        class="flex items-center justify-between mb-4 sticky top-0 bg-white/90 backdrop-blur-sm z-10 py-2 border-b border-slate-100 -mx-5 px-5 -mt-5">
                        <h3 class="text-xl font-black">Create a New Event</h3>
                        <Link :href="route('organizer.event.index')"
                            class="text-slate-500 hover:text-slate-800 transition bg-slate-100 hover:bg-slate-200 p-2 rounded-full">
                            <X class="w-5 h-5" />
                        </Link>
                    </div>

                    <form @submit.prevent="handleSubmit">
                        <Media v-model="mediaFiles" :errors="form.errors.mediaFiles" :appURL="props.appURL" />
                        <EventDetails :categories="props.categories" v-model="eventDetails" :allErrors="form.errors"
                            :eventDetails="[]" @update:isCookout="isCookout = $event"
                            @update:isWellness="isWellness = $event" />
                        <DateTime v-model="dateTime" :errors="dateTimeErrors" />
                        <AdditionalOptions v-model="additionalOptions" :caribbeans="props.caribbeans"
                            :allErrors="form.errors" :isCookout="isCookout" :isWellness="isWellness"
                            :appURL="props.appURL || ''" :additionalOptions="[]" :events="props.events" />
                        <Location v-model="location" :errors="locationErrors" :isCookout="isCookout"
                            :isWellness="isWellness" :events="props.events" />
                        <Scanner :scanners="props.scanners" v-model="selectedScanners" :errors="scannerErrors" />
                        <SocialMedia v-model="socialMedia" :errors="socialMediaErrors"
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
                            {{ form.processing ? 'Creating...' : 'Publish Event' }}
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
    events?: any;
    allEvents?: any[];
    appURL?: string;
    asModal?: boolean;
}>()

const showModal = ref(false);

const open = () => {
    showModal.value = true;
};

const close = () => {
    showModal.value = false;
};

defineExpose({ open, close });

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
    zip: "",
    tax_rate: "",
    tax_included: "no",
    disclaimer: "",
    gallery: [],
    artists: [],
    artist_image: []
});
const location = ref({
    location_type: "venue",
    venue: "",
    latitude: null,
    longtitude: null,
});
const selectedScanners = ref<any[]>([]);
const socialMedia = ref({
    twitter: "",
    instagram: "",
    facebook: "",
    tiktok: "",
    linkedin: ""
});

const form = useForm({
    mediaFiles: [] as File[],
    eventDetails: {},
    dateTime: {},
    additionalOptions: {},
    location_type: "",
    venue: "",
    latitude: null,
    longtitude: null,
    scanners: [],
    socialMedia: {},
    post_as_organization: false
});

const isCookout = ref(false);
const isWellness = ref(false);

watch(() => eventDetails.value.category_id, (newId: number | null | string) => {
    if (!newId || !props.categories) {
        isCookout.value = false;
        isWellness.value = false;
        return;
    }
    const category = props.categories.find((c: any) => String(c.id) === String(newId));
    const name = category?.name?.trim().toLowerCase() || '';

    isCookout.value = name.includes('cookout');
    isWellness.value = name.includes('wellness');

    console.log('Category Selection Watch:', { id: newId, name, isCookout: isCookout.value, isWellness: isWellness.value });
}, { immediate: true });

const handleSubmit = () => {

    form.mediaFiles = mediaFiles.value;
    form.eventDetails = eventDetails.value;
    form.dateTime = dateTime.value;
    form.additionalOptions = additionalOptions.value;
    form.location_type = location.value.location_type;
    form.venue = location.value.venue;
    form.latitude = location.value.latitude;
    form.longtitude = location.value.longtitude;
    form.scanners = selectedScanners.value;
    form.socialMedia = socialMedia.value;


    form.post(route('organizer.event.store'), {
        onSuccess: () => {
            toast.success("Event Created Successfully");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
        },
    });
};

const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        if (props.asModal) {
            close();
        } else {
            // Navigate back to events page
            window.location.href = route('organizer.event.index');
        }
    }
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
const locationErrors = computed(() => {
    return Object.entries(form.errors)
        .filter(([key]) => key === "location_type" || key === "venue")
        .map(([, value]) => value as string);
});

</script>

<style>
@layer utilities {
    .modal-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .modal-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-scroll::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
        border: 2px solid white;
        background-clip: padding-box;
    }

    .modal-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }
}
</style>
