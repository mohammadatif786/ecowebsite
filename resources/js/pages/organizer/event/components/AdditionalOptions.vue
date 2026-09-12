<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Additional Options
        </p>

        <!-- Event Type -->
        <label class="text-xs font-black text-slate-500">Event Type *</label>
        <div class="flex gap-2 mt-1 mb-3">
            <button type="button" @click="form.type = 'public'"
                :class="form.type === 'public' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-500'"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                Public Event
            </button>
            <button type="button" @click="form.type = 'private'"
                :class="form.type === 'private' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-500'"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                Private Event
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-sm font-bold text-slate-700">Email</label>
                <input type="email" v-model="form.email" placeholder="Email"
                    class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                    :class="props.allErrors?.['additionalOptions.email'] ? 'border-rose-500' : 'border-slate-200'" />
                <p v-if="props.allErrors?.['additionalOptions.email']" class="text-[10px] text-rose-500 font-bold ml-1">
                    {{ props.allErrors['additionalOptions.email'] }}
                </p>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Phone</label>
                <input type="tel" v-model="form.phone" placeholder="Phone"
                    class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                    :class="props.allErrors?.['additionalOptions.phone'] ? 'border-rose-500' : 'border-slate-200'" />
                <p v-if="props.allErrors?.['additionalOptions.phone']" class="text-[10px] text-rose-500 font-bold ml-1">
                    {{ props.allErrors['additionalOptions.phone'] }}
                </p>
            </div>
        </div>

        <div v-show="!isCookout && !isWellness" class="mb-3">
            <label class="text-sm font-bold text-slate-700">Website</label>
            <input type="text" v-model="form.website" placeholder="Website"
                class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                :class="props.allErrors?.['additionalOptions.website'] ? 'border-rose-500' : 'border-slate-200'" />
            <p v-if="props.allErrors?.['additionalOptions.website']" class="text-[10px] text-rose-500 font-bold ml-1">
                {{ props.allErrors['additionalOptions.website'] }}
            </p>
        </div>

        <!-- Location Details -->
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-sm font-bold text-slate-700">Country</label>
                <select v-model="form.country" @change="handleCountryChange"
                    class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                    :class="props.allErrors?.['additionalOptions.country'] ? 'border-rose-500' : 'border-slate-200'">
                    <option value="">Select Country</option>
                    <option v-for="country in countries" :key="country.value" :value="country.value">{{ country.label }}</option>
                </select>
                <p v-if="props.allErrors?.['additionalOptions.country']" class="text-[10px] text-rose-500 font-bold ml-1">
                    {{ props.allErrors['additionalOptions.country'] }}
                </p>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">State <span v-if="isLoadingState" class="text-blue-500 ml-1">...</span></label>
                <select v-model="form.state" @change="handleStateChange" :disabled="!states.length && !isLoadingState"
                    class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                    :class="props.allErrors?.['additionalOptions.state'] ? 'border-rose-500' : 'border-slate-200'">
                    <option value="">Select State</option>
                    <option v-for="state in states" :key="state.value" :value="state.value">{{ state.label }}</option>
                </select>
                <p v-if="props.allErrors?.['additionalOptions.state']" class="text-[10px] text-rose-500 font-bold ml-1">
                    {{ props.allErrors['additionalOptions.state'] }}
                </p>
                <p v-else-if="!isLoadingState && states.length === 0 && form.country" class="text-[10px] text-red-500 mt-1">No states found</p>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">City <span v-if="isLoadingCity" class="text-blue-500 ml-1">...</span></label>
                <select v-model="form.city" :disabled="!cities.length && !isLoadingCity"
                    class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                    :class="props.allErrors?.['additionalOptions.city'] ? 'border-rose-500' : 'border-slate-200'">
                    <option value="">Select City</option>
                    <option v-for="city in cities" :key="city.value" :value="city.value">{{ city.label }}</option>
                </select>
                <p v-if="props.allErrors?.['additionalOptions.city']" class="text-[10px] text-rose-500 font-bold ml-1">
                    {{ props.allErrors['additionalOptions.city'] }}
                </p>
                <p v-else-if="!isLoadingCity && cities.length === 0 && form.state" class="text-[10px] text-red-500 mt-1">No cities found</p>
            </div>
            <div v-if="isApiSupported">
                <label class="text-sm font-bold text-slate-700">ZIP Code</label>
                <input type="text" v-model="form.zip" placeholder="ZIP Code" @blur="fetchTaxRate"
                    class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                    :class="props.allErrors?.['additionalOptions.zip'] ? 'border-rose-500' : 'border-slate-200'" />
                <p v-if="props.allErrors?.['additionalOptions.zip']" class="text-[10px] text-rose-500 font-bold ml-1">
                    {{ props.allErrors['additionalOptions.zip'] }}
                </p>
            </div>
        </div>

        <!-- Taxes -->
        <label class="text-xs font-black text-slate-500">Does this event include taxes? *</label>
        <div class="flex gap-2 mt-1 mb-3">
            <button type="button" @click="form.tax_included = 'yes'"
                :class="form.tax_included === 'yes' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-500'"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                Yes, included
            </button>
            <button type="button" @click="form.tax_included = 'no'"
                :class="form.tax_included === 'no' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-500'"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                No, not included
            </button>
        </div>

        <div class="mb-3">
            <label class="text-xs font-black text-slate-500">Tax Rate <span v-if="isFetchingTaxRate" class="text-blue-500 ml-1">...</span></label>
            <input type="text" v-model="form.tax_rate" placeholder="Tax Rate" readonly
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 bg-slate-50" />
            <p v-if="taxRateError" class="text-[11px] text-red-500 mt-1">{{ taxRateError }}</p>
            <p v-else-if="isCountryUnsupported" class="text-[11px] text-orange-500 mt-1">⚠️ Contact admin or leave blank for 0 tax</p>
            <p v-else-if="!form.tax_rate && (form.country || form.zip)" class="text-[11px] text-orange-500 mt-1">💡 Configure tax API settings to check rates</p>
        </div>

        <!-- Disclaimer -->
        <div class="mb-4">
            <label class="text-xs font-black text-slate-500">Disclaimer</label>
            <div class="border border-slate-200 rounded-2xl overflow-hidden mt-1 focus-within:border-blue-400">
                <div class="bg-slate-50 border-b border-slate-200 p-2 flex gap-2">
                    <button type="button" @click.prevent="exec('bold')" class="px-2 py-1 rounded hover:bg-slate-200 font-bold">B</button>
                    <button type="button" @click.prevent="exec('italic')" class="px-2 py-1 rounded hover:bg-slate-200 italic">I</button>
                    <button type="button" @click.prevent="exec('underline')" class="px-2 py-1 rounded hover:bg-slate-200 underline">U</button>
                    <button type="button" @click.prevent="insertLink" class="px-2 py-1 rounded hover:bg-slate-200">🔗</button>
                </div>
                <div class="p-4 min-h-[100px] outline-none" ref="editor" contenteditable="true" @input="updateDisclaimer">
                    Enter disclaimer here ...
                </div>
            </div>
        </div>

        <!-- Gallery -->
        <div class="mb-4">
            <label class="text-xs font-black text-slate-500">Images gallery</label>
            <p class="text-[11px] text-slate-400 mb-2">Add Multiple images and videos for your event</p>

            <button type="button" @click="triggerGallery"
                class="rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/40 text-blue-600 px-4 py-3 text-sm font-bold w-full hover:bg-blue-50 transition">
                + Add Media
            </button>
            <input type="file" ref="galleryInput" accept="image/*,video/*" multiple class="hidden" @change="handleGallery" />

            <div v-if="galleryPreviews.length" class="flex flex-wrap gap-2 mt-3">
                <div v-for="(item, i) in galleryPreviews" :key="i" class="relative group">
                    <img v-if="item.type === 'image'" :src="item.url" class="h-24 w-24 rounded-xl object-cover" />
                    <video v-else-if="item.type === 'video'" :src="item.url" class="h-24 w-24 rounded-xl object-cover" controls muted></video>
                    <button type="button" @click="removeGalleryImage(i)"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">✕</button>
                </div>
            </div>
        </div>

        <!-- Artists -->
        <div v-show="!isCookout && !isWellness" class="mb-2">
            <label class="text-xs font-black text-slate-500">Artists</label>
            <p class="text-[11px] text-slate-400 mb-2">Enter the list of artists that will perform in your event (press Enter after each entry)</p>

            <input type="text" v-model="artistInput" @keydown.enter.prevent="addArtist" placeholder="Type artist name and press Enter"
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3" />

            <div class="flex flex-wrap gap-3">
                <div v-for="(artist, index) in form.artists" :key="index"
                    class="rounded-2xl border border-slate-200 p-3 flex flex-col items-center gap-2 relative group w-32">
                    <button type="button" @click="removeArtist(index)"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">✕</button>

                    <div class="w-16 h-16 rounded-full bg-slate-100 overflow-hidden relative border border-slate-200">
                        <img v-if="form.artist_image[index]" :src="getArtistImagePreview(index)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400">👤</div>
                    </div>

                    <span class="text-sm font-bold text-center truncate w-full">{{ artist }}</span>

                    <button type="button" @click="triggerArtistImageUpload(index)"
                        class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                        {{ form.artist_image[index] ? 'Change' : '+ Image' }}
                    </button>
                    <input type="file" :ref="el => artistImageInputs[index] = el" accept="image/*" class="hidden"
                        @change="handleArtistImageUpload(index, $event)" />
                </div>
            </div>
        </div>

        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in props.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted, nextTick, computed, ComponentPublicInstance } from "vue";
import { useCountryStateCity } from "@/composables/useCountryStateCity";
import axios from "axios";
import { route } from "ziggy-js";
const props = defineProps<{
    errors?: string[];
    allErrors?: Record<string, string>;
    appURL: string;
    additionalOptions: Array<Record<string, null>>;
    isCookout?: boolean;
    isWellness?: boolean;
}>();

// modelValue from parent
const modelValue = defineModel<{
    type: string;
    email: string;
    phone: string;
    website: string;
    country: string;
    state: string;
    city: string;
    zip: string;
    tax_rate: string;
    tax_included: string;
    disclaimer: string;
    gallery: (string | File)[];
    artists: string[];
    artist_image: (string | File | null)[];
}>({
    default: {
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
    }
});

const form = reactive(modelValue.value);
watch(form, (val) => {
    modelValue.value = val;
}, { deep: true });

watch(() => [props.isCookout, props.isWellness], (vals) => {
    console.log('AdditionalOptions Category Status - Cookout:', vals[0], 'Wellness:', vals[1]);
}, { immediate: true });

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.additionalOptions });
const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};

// disclaimer editor
const editor = ref<HTMLDivElement | null>(null);
const exec = (command: string, value?: string) => {
    if (!editor.value) return;
    editor.value.focus();
    document.execCommand(command, false, value);
};
const insertLink = () => {
    const url = prompt("Enter URL:");
    if (url !== null) exec("createLink", url);
};
const updateDisclaimer = () => {
    form.disclaimer = editor.value?.innerHTML || "";
};

// Country/State/City
const {
    countries,
    states,
    cities,
    fetchCountries,
    fetchStates,
    fetchCities,
    resetStatesAndCities,
    resetCities,
    isLoadingState,
    isLoadingCity
} = useCountryStateCity();

// Preload data from existing event props
const syncFromEvent = () => {
    if (!props.events) return;

    if (props.events.event_details?.image_gallery) {
        form.gallery = [...props.events.event_details.image_gallery];
        preloadGallery();
    }

    if (props.events.event_details?.artists) {
        form.artists = [...props.events.event_details.artists];
        form.artist_image = props.events.event_details.artist_image
            ? [...props.events.event_details.artist_image]
            : new Array(form.artists.length).fill(null);
    }
};

onMounted(async () => {
    await fetchCountries();
    await fetchLocalTaxCountries(); // Fetch countries with local tax rates
    await fetchApiSupportedCountries(); // Fetch countries supported by API

    // Normalize country to option value if persisted as label
    if (form.country && !countries.value.some(c => c.value === form.country)) {
        const match = countries.value.find(c => c.label === form.country);
        if (match) form.country = match.value as any;
    }

    syncFromEvent();

    // preload state/city if existing
    if (form.country) {
        await fetchStates(form.country);

        if (form.state && !states.value.some(s => s.value === form.state)) {
            const sMatch = states.value.find(s => s.label === form.state);
            if (sMatch) form.state = sMatch.value as any;
        }

        await fetchCities(form.country, form.state);

        if (form.city && !cities.value.some(ci => ci.value === form.city)) {
            const cMatch = cities.value.find(ci => ci.label === form.city);
            if (cMatch) form.city = cMatch.value as any;
        }
    }

    // preload disclaimer in editor
    if (editor.value && form.disclaimer) {
        editor.value.innerHTML = form.disclaimer;
    }

    // ensure DOM updates reflect normalized values
    await nextTick();
});

watch(() => props.events, (newVal) => {
    if (newVal) {
        syncFromEvent();
    }
}, { deep: true });

const countryNormalized = ref(false);
const stateNormalized = ref(false);
const cityNormalized = ref(false);

watch([countries, () => form.country], async ([countryOptions, currentCountry]) => {
    if (countryNormalized.value) return;
    if (!countryOptions?.length || !currentCountry) return;
    // If stored as label, convert to value
    if (!countryOptions.some((c: any) => c.value === currentCountry)) {
        const match = countryOptions.find((c: any) => c.label === currentCountry);
        if (match) form.country = match.value as any;
    }
    countryNormalized.value = true;
    // Load states for selected country
    await fetchStates(form.country);
}, { immediate: false });

watch([states, () => form.state, () => form.country], async ([stateOptions, currentState, currentCountry]) => {
    if (!currentCountry || !stateOptions) return;
    if (stateOptions.length === 0) return;
    if (!stateNormalized.value && currentState) {
        if (!stateOptions.some((s: any) => s.value === currentState)) {
            const sMatch = stateOptions.find((s: any) => s.label === currentState);
            if (sMatch) form.state = sMatch.value as any;
        }
        stateNormalized.value = true;
        await fetchCities(form.country, form.state);
    }
}, { immediate: false });

watch([cities, () => form.city, () => form.state, () => form.country], ([cityOptions, currentCity, currentState, currentCountry]) => {
    if (!currentCountry || !currentState || !cityOptions) return;
    if (cityOptions.length === 0 || cityNormalized.value || !currentCity) return;
    if (!cityOptions.some((ci: any) => ci.value === currentCity)) {
        const cMatch = cityOptions.find((ci: any) => ci.label === currentCity);
        if (cMatch) form.city = cMatch.value as any;
    }
    cityNormalized.value = true;
}, { immediate: false });

// Display label for current country selection
const countryLabel = computed(() => {
    const list = countries.value || [];
    const match = list.find((c: any) => c.value === form.country);
    return match ? match.label : (typeof form.country === 'string' && form.country.length && !list.length ? form.country : '');
});

const handleCountryChange = () => {
    resetStatesAndCities();
    fetchStates(form.country);
};

const handleStateChange = () => {
    resetCities();
    fetchCities(form.country, form.state);
};

// Tax Rate Fetching
const isFetchingTaxRate = ref(false);
const taxRateError = ref("");

// Check if current country has local tax rate (not supported by API)
const hasLocalTaxRate = computed(() => {
    // For now, use hardcoded list - in future this could be fetched from API
    return localTaxCountries.value.includes(form.country);
});


// List of countries that have local tax rates (fetched from database)
const localTaxCountries = ref<string[]>([]);

// List of countries supported by TaxJar API (fetched from backend)
const apiSupportedCountries = ref<string[]>([]);

// Fetch countries that have local tax rates
const fetchLocalTaxCountries = async () => {
    try {
        const response = await axios.get(route('organizer.event.get-local-tax-countries'));
        localTaxCountries.value = response.data.countries || [];
    } catch (error) {
        console.error('Failed to fetch local tax countries:', error);
        localTaxCountries.value = [];
    }
};

// Fetch countries supported by TaxJar API
const fetchApiSupportedCountries = async () => {
    try {
        const response = await axios.get(route('organizer.event.get-api-supported-countries'));
        apiSupportedCountries.value = response.data.countries || [];
    } catch (error) {
        console.error('Failed to fetch API supported countries:', error);
        apiSupportedCountries.value = [];
    }
};

// Check if current country is supported by API
const isApiSupported = computed(() => {
    return apiSupportedCountries.value.includes(form.country);
});

// Check if current country is unsupported (neither in API nor local database)
const isCountryUnsupported = computed(() => {
    return form.country && !hasLocalTaxRate.value && !isApiSupported.value;
});

const fetchTaxRate = async () => {
    if (!form.country || (!form.zip && !hasLocalTaxRate.value)) return;

    isFetchingTaxRate.value = true;
    taxRateError.value = '';

    try {
        const payload: Record<string, string> = { country: form.country };
        if (form.state) {
            const selectedState = states.value.find((state: any) =>
                state.value === form.state || state.label === form.state || state.state_code === form.state
            );
            payload.state = selectedState?.state_code || selectedState?.value || form.state;
        }
        if (form.city) payload.city = form.city;
        if (form.zip) payload.zip = form.zip;

        const response = await axios.post(route('organizer.event.get-tax-rate'), payload);

        if (response.data.success) {
            const rate = Number(response.data.rate ?? 0);
            form.tax_rate = hasLocalTaxRate.value ? `${rate}` : `${(rate * 100).toFixed(2)}%`;
        } else {
            taxRateError.value = response.data.message || 'Failed to fetch tax rate';
        }

    } catch (error: any) {
        taxRateError.value = error.response?.data?.message || 'Error fetching tax rate';
    } finally {
        isFetchingTaxRate.value = false;
    }
};


// Watch for changes in location fields to auto-fetch tax rate
watch([() => form.country, () => form.state, () => form.city, () => form.zip], () => {

    if (hasLocalTaxRate.value && form.country) {
        // For local tax countries, fetch immediately when country changes
        fetchTaxRate();
    } else if (form.country && form.zip) {
        // For API countries, require ZIP
        fetchTaxRate();
    }
});

// Also watch specifically for country changes to clear state/city/zip for local tax countries
watch(() => form.country, (newCountry, oldCountry) => {

    if (newCountry !== oldCountry) {
        // Always clear tax rate when country changes
        form.tax_rate = '';
        taxRateError.value = '';

        if (hasLocalTaxRate.value) {
            // Clear fields that aren't needed for local tax countries
            form.state = '';
            form.city = '';
            form.zip = '';
        } else if (isCountryUnsupported.value) {
            // Clear tax rate for unsupported countries
            form.state = '';
            form.city = '';
            form.zip = '';
        }
    }
});

// Gallery
const galleryInput = ref<HTMLInputElement | null>(null);
const galleryPreviews = ref<{ url: string, type: 'image' | 'video' }[]>([]);
const isVideoFile = (file: File | string): boolean => {
    if (typeof file === 'string') {
        // Check file extension for existing DB files
        const videoExtensions = ['.mp4', '.webm', '.ogg', '.avi', '.mov', '.wmv', '.flv', '.mkv'];
        return videoExtensions.some(ext => file.toLowerCase().includes(ext));
    } else {
        // Check MIME type for File objects
        return file.type.startsWith('video/');
    }
};

const preloadGallery = () => {
    galleryPreviews.value = form.gallery.map((item) => {
        let url = "";
        let type: 'image' | 'video' = 'image';

        if (typeof item === "string") {
            url = `${props.appURL}${item}`; // existing DB image/video
            type = isVideoFile(item) ? 'video' : 'image';
        } else if (item instanceof File) {
            url = URL.createObjectURL(item); // newly selected file
            type = isVideoFile(item) ? 'video' : 'image';
        }

        return { url, type };
    });
};
const triggerGallery = () => {
    galleryInput.value?.click();
};
const handleGallery = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;
    if (!files) return;

    Array.from(files).forEach(file => {
        form.gallery.push(file);
        const url = URL.createObjectURL(file);
        const type = isVideoFile(file) ? 'video' : 'image';
        galleryPreviews.value.push({ url, type });
    });

    if (galleryInput.value) galleryInput.value.value = "";
};


// Artists
const artistInput = ref("");
const artistImageInputs = ref<(Element | ComponentPublicInstance | null)[]>([]);
const addArtist = () => {
    if (artistInput.value.trim()) {
        form.artists.push(artistInput.value.trim());
        // Ensure artist_image array has same length as artists array
        if (!form.artist_image) {
            form.artist_image = [];
        }
        // Add null placeholder for this artist's image
        form.artist_image.push(null);
        artistInput.value = "";
    }
};
const removeArtist = (index: number) => {
    form.artists.splice(index, 1);
    if (form.artist_image) {
        form.artist_image.splice(index, 1);
    }
};

const triggerArtistImageUpload = (index: number) => {
    if (artistImageInputs.value[index]) {
        (artistImageInputs.value[index] as HTMLInputElement).click();
    }
};

const handleArtistImageUpload = (index: number, event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    if (!files || files.length === 0) return;

    const file = files[0];
    if (form.artist_image) {
        form.artist_image[index] = file;
    }

    // Clear the input value to allow selecting the same file again
    if (artistImageInputs.value[index]) {
        (artistImageInputs.value[index] as HTMLInputElement).value = "";
    }
};

const removeArtistImage = (index: number) => {
    if (form.artist_image) {
        form.artist_image[index] = null;
    }
};

const getArtistImagePreview = (index: number) => {
    if (form.artist_image && form.artist_image[index]) {
        const image = form.artist_image[index];
        if (typeof image === 'string') {
            return `${props.appURL}${image}`;
        } else if (image instanceof File) {
            return URL.createObjectURL(image);
        }
    }
    return '';
};

const removeGalleryImage = (index: number) => {
    form.gallery.splice(index, 1);
    galleryPreviews.value.splice(index, 1);
};
</script>
