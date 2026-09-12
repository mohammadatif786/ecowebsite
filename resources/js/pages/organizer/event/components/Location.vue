<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Location
        </p>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 mb-3">
            <button type="button" @click="activeTab = 'venue'"
                class="rounded-xl border-2 px-4 py-2.5 text-sm font-bold flex items-center gap-2 transition"
                :class="activeTab === 'venue' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200 text-slate-600'">
                📍 Venue
            </button>
            <button type="button" @click="activeTab = 'online'"
                class="rounded-xl border-2 px-4 py-2.5 text-sm font-bold flex items-center gap-2 transition"
                :class="activeTab === 'online' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200 text-slate-600'">
                🖥️ Online event
            </button>
            <button type="button" @click="activeTab = 'tba'"
                class="rounded-xl border-2 px-4 py-2.5 text-sm font-bold flex items-center gap-2 transition"
                :class="activeTab === 'tba' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200 text-slate-600'">
                📅 To be announced
            </button>
        </div>

        <!-- Venue -->
        <div v-if="activeTab === 'venue'">
            <label class="text-xs font-black text-slate-500">Venue Location *</label>
            <input type="text" v-model.trim="form.venue" placeholder="Enter venue name or address"
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3" />

            <!-- Address preview — no Google Maps JavaScript API or API key is required. -->
            <div class="rounded-2xl border border-blue-100 overflow-hidden mb-3">
                <div v-if="!form.venue?.trim()"
                    class="flex h-36 items-center justify-center bg-slate-50 px-4 text-center text-sm font-medium text-slate-400">
                    Enter an address to preview the map
                </div>
                <template v-else>
                    <iframe
                        :src="mapEmbedUrl"
                        :title="`Map preview for ${form.venue}`"
                        class="block h-40 w-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                    <a :href="mapSearchUrl" target="_blank" rel="noopener"
                        class="flex items-center justify-center gap-1 border-t border-blue-100 bg-white px-4 py-2 text-xs font-bold text-blue-600 hover:bg-blue-50">
                        Open in Google Maps ↗
                    </a>
                </template>
            </div>

        </div>

        <!-- Online -->
        <div v-if="activeTab === 'online'">
            <label class="flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-100 p-3 cursor-pointer mb-4">
                <input type="checkbox" v-model="hostOnLinkUpLive" class="mt-0.5 accent-blue-600" />
                <span>
                    <span class="font-black text-sm block text-slate-800">📡 Host on LinkUp Live</span>
                    <span class="text-[11px] text-slate-500">Tag this event directly to LinkUp Live so attendees watch inside the app — no external Zoom link needed.</span>
                </span>
            </label>

            <template v-if="hostOnLinkUpLive">
                <label class="text-xs font-black text-slate-500">Live Category *</label>
                <select v-model="liveCategory"
                    class="w-full border border-blue-500 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-100 mt-1 mb-3 bg-white font-medium text-slate-800">
                    <option v-for="category in liveCategories" :key="category" :value="category">{{ category }}</option>
                </select>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-2.5 text-[11px] text-emerald-700 font-bold mb-3">
                    ✅ Attendees will get a "Watch Live" button that opens your broadcast in LinkUp Live.
                </div>
            </template>
        </div>

        <!-- TBA -->
        <div v-if="activeTab === 'tba'">
            <p class="text-sm font-bold text-slate-600 my-4">Location to be announced later.</p>
        </div>

        <ReservedSeating />

        <!-- Errors -->
        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in props.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from "vue";
import ReservedSeating from './ReservedSeating.vue';
const props = defineProps<{
    errors?: string[],
    events?: { location_type?: string, venue?: string, latitude?: string, longtitude?: string },
    isCookout?: boolean,
    isWellness?: boolean
}>();

const modelValue = defineModel<{
    location_type: string;
    venue: string;
    latitude: string | null;
    longtitude: string | null;
}>({
    default: {
        location_type: "venue",
        venue: "",
        latitude: null,
        longtitude: null,
    }
});
// Local reactive state bound to form inputs
const form = reactive(modelValue.value);

// Keep modelValue in sync with local form
watch(form, (val) => {
    modelValue.value = val;
}, { deep: true });

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.events });
const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};

// Tab state
const activeTab = ref<'venue' | 'online' | 'tba'>('venue');
const hostOnLinkUpLive = ref(true);
const liveCategory = ref('Just Chatting');
const liveCategories = [
    'Private',
    'Adult / Mature',
    'Afrobeats & Amapiano',
    'Carnival Mixers',
    'News · Sports',
    'Just Chatting',
    'Comedy & Skits',
    'Education & Growth',
    'Business & Money',
];

// Initialize active tab if parent provides a value
if (props.events?.location_type) {
    activeTab.value = props.events.location_type as 'venue' | 'online' | 'tba';
    form.location_type = props.events.location_type;
}

if (props.events?.venue) {
    form.venue = props.events.venue;
}

// Watch tab changes and update form
watch(activeTab, (val) => {
    form.location_type = val;
    // The venue field is also used for the online-event URL.
    if (val !== 'venue') {
        form.venue = '';
        form.latitude = null;
        form.longtitude = null;
    }
});

const encodedVenue = computed(() => encodeURIComponent(form.venue?.trim() ?? ''));
const mapEmbedUrl = computed(() => `https://www.google.com/maps?q=${encodedVenue.value}&output=embed`);
const mapSearchUrl = computed(() => `https://www.google.com/maps/search/?api=1&query=${encodedVenue.value}`);
</script>
