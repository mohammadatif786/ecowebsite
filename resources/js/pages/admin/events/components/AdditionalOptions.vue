<template>
    <div class="form-section">
        <h2>Additional Options</h2>
        <button type="button" class="add-section-btn" @click.stop="toggleSection(0)" v-show="!showFields[0]">+</button>
        <button type="button" class="add-section-btn" @click.stop="toggleSection(0)" v-show="showFields[0]">-</button>

        <p class="section-overview" v-if="!showFields[0]">
            Provide additional details like event type, contact information, and disclaimer.
        </p>

        <div class="form-fields" v-if="showFields[0]">
            <!-- Event Type -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Event Type
                </label>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            value="public"
                            v-model="form.type"
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    />
                        <span class="text-gray-800">Public Event</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        value="private"
                        v-model="form.type"
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    />
                        <span class="text-gray-800">Private Event</span>
                    </label>
                </div>
            </div>

            <!-- Contact -->
            <div class="form-group">
                <label>Email</label>
                <input type="email" v-model="form.email" placeholder="Email" />
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="tel" v-model="form.phone" placeholder="Phone" />
            </div>

            <div class="form-group">
                <label>Website</label>
                <input type="text" v-model="form.website" placeholder="Website" />
            </div>

            <!-- Country / State / City -->
            <div class="form-group">
                <label>
                    Country
                </label>
                <select v-model="form.country" @change="handleCountryChange">
                    <option value="">Select Country</option>
                    <option v-for="country in countries" :key="country.value" :value="country.value">
                        {{ country.label }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>State <span v-if="isLoadingState" class="text-sm text-blue-500 ml-2">Loading...</span></label>
                <select v-model="form.state" @change="handleStateChange" :disabled="!states.length && !isLoadingState">
                    <option value="">Select State</option>
                    <option v-for="state in states" :key="state.value" :value="state.value">
                        {{ state.label }}
                    </option>
                </select>
                <p v-if="!isLoadingState && states.length === 0 && form.country" class="text-sm text-red-500 mt-1">No
                    states found</p>
            </div>

            <div class="form-group">
                <label>City <span v-if="isLoadingCity" class="text-sm text-blue-500 ml-2">Loading...</span></label>
                <select v-model="form.city" :disabled="!cities.length && !isLoadingCity">
                    <option value="">Select City</option>
                    <option v-for="city in cities" :key="city.value" :value="city.value">
                        {{ city.label }}
                    </option>
                </select>
                <p v-if="!isLoadingCity && cities.length === 0 && form.state" class="text-sm text-red-500 mt-1">No
                    cities found</p>
            </div>

            <div class="form-group" v-if="isApiSupported">
                <label>ZIP Code</label>
                <input type="text" v-model="form.zip" placeholder="ZIP Code" @blur="fetchTaxRate" />
            </div>

             <!-- Tax Included Option -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Does this event include taxes?
                </label>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            value="yes"
                            v-model="form.tax_included"
                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                        />
                        <span class="text-gray-800">Yes, taxes are included</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            value="no"
                            v-model="form.tax_included"
                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                        />
                        <span class="text-gray-800">No, taxes are not included</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Tax Rate <span v-if="isFetchingTaxRate"
                        class="text-sm text-blue-500 ml-2">Loading...</span></label>
                <input type="text" v-model="form.tax_rate" placeholder="Tax Rate" readonly />
                <p v-if="taxRateError" class="text-sm text-red-500 mt-1">{{ taxRateError }}</p>
                <p v-else-if="isCountryUnsupported" class="text-sm text-orange-500 mt-1">
                    ?? Please contact admin or leave blank for 0 tax
                </p>
                <p v-else-if="!form.tax_rate && (form.country || form.zip)" class="text-sm text-orange-500 mt-1">
                    ?? To check tax rates, please first configure tax API settings
                </p>
            </div>

            <!-- Disclaimer -->
            <div class="form-group">
                <label>Disclaimer</label>
                <div class="editor-toolbar">
                    <button type="button" @click.prevent="exec('bold')" title="Bold">B</button>
                    <button type="button" @click.prevent="exec('italic')" title="Italic">I</button>
                    <button type="button" @click.prevent="exec('underline')" title="Underline">U</button>
                    <button type="button" @click.prevent="insertLink" title="Link">??</button>
                </div>
                <div class="editor-content" ref="editor" contenteditable="true" @input="updateDisclaimer">
                    Enter disclaimer here ...
                </div>
            </div>

            <!-- Gallery -->
            <div class="form-group">
                <label>Images gallery</label>
                <p class="info"><span class="info-icon">?</span> Add Multiple images and videos for your event</p>
                <button type="button" class="add-button" @click="triggerGallery">
                    <span style="margin-right: 5px;">+</span>ADD
                </button>
                <input type="file" ref="galleryInput" accept="image/*,video/*" multiple style="display: none"
                    @change="handleGallery" />

                <div class="preview-container">
                    <div v-for="(item, i) in galleryPreviews" :key="i" class="preview-wrapper">
                        <img v-if="item.type === 'image'" :src="item.url" class="preview-item" />
                        <video v-else-if="item.type === 'video'" :src="item.url" class="preview-item" controls
                            muted></video>
                        <button type="button" class="remove-btn" @click="removeGalleryImage(i)">-</button>
                    </div>
                </div>

            </div>

            <!-- Artists -->
            <div class="form-group">
                <label>Artists</label>
                <p class="info">
                    <span class="info-icon">?</span>
                    Enter the list of artists that will perform in your event (press Enter after each entry)
                </p>
                <input type="text" v-model="artistInput" @keydown.enter.prevent="addArtist"
                    placeholder="Type artist name and press Enter" />
                <div class="tags-container">
                    <div v-for="(artist, index) in form.artists" :key="index" class="artist-item">
                        <div class="artist-info">
                            <span class="artist-name">{{ artist }}</span>
                            <div class="artist-image-upload">
                                <button type="button" class="image-upload-btn" @click="triggerArtistImageUpload(index)">
                                    <span v-if="!form.artist_image || !form.artist_image[index]">+ Add Image</span>
                                    <span v-else>Change Image</span>
                                </button>
                               <input type="file" :ref="el => artistImageInputs[index] = el" accept="image/*" style="display: none"
                                    @change="handleArtistImageUpload(index, $event)" />
                                <button type="button" class="remove-image-btn" v-if="form.artist_image && form.artist_image[index]"
                                    @click="removeArtistImage(index)">×</button>
                            </div>
                        </div>
                        <div class="artist-image-preview" v-if="form.artist_image && form.artist_image[index]">
                            <img :src="getArtistImagePreview(index)" alt="Artist image" />
                        </div>
                        <button type="button" class="remove-artist-btn" @click="removeArtist(index)">×</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul>
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
    appURL: string;
    additionalOptions: any;
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
        tax_included: "",
        disclaimer: "",
        gallery: [],
        artists: [],
        artist_image: []
    }
});

const form = reactive(modelValue.value);

// Ensure artist_image array is synchronized with artists array
onMounted(() => {
    console.log('AdditionalOptions mounted with props:', props.additionalOptions);
    console.log('Initial form data:', form);

    if (form.artists && form.artists.length > 0) {
        if (!form.artist_image) {
            form.artist_image = [];
        }
        // Ensure artist_image array has the same length as artists array
        while (form.artist_image.length < form.artists.length) {
            form.artist_image.push(null);
        }
        // Initialize artistImageInputs array
        artistImageInputs.value = new Array(form.artists.length).fill(null);
        console.log('Arrays synchronized after mount:', {
            artists: form.artists,
            artist_image: form.artist_image,
            artistImageInputs: artistImageInputs.value
        });
    }
});

watch(
    form,
    (val) => {
        modelValue.value = val;
    },
    { deep: true }
);

// Watch artists array to synchronize artist_image and artistImageInputs arrays
watch(() => form.artists, (newArtists) => {
    if (!form.artist_image) {
        form.artist_image = [];
    }

    // If artists array is longer, add null placeholders to artist_image
    while (form.artist_image.length < newArtists.length) {
        form.artist_image.push(null);
    }

    // If artists array is shorter, remove extra items from artist_image
    if (form.artist_image.length > newArtists.length) {
        form.artist_image.splice(newArtists.length);
    }

    // Update artistImageInputs array
    artistImageInputs.value = new Array(newArtists.length).fill(null);
}, { deep: true });

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

onMounted(async () => {
    await fetchCountries();
    await fetchLocalTaxCountries(); // Fetch countries with local tax rates
    await fetchApiSupportedCountries(); // Fetch countries supported by API

    // Normalize country to option value if persisted as label
    if (form.country && !countries.value.some(c => c.value === form.country)) {
        const match = countries.value.find(c => c.label === form.country);
        if (match) form.country = match.value as any;
    }

    // preload gallery if form.gallery has DB images
    if (form.gallery && form.gallery.length) {
        preloadGallery();
    }

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
        const response = await axios.get(route('admin.tax-setting.get-local-tax-countries'));
        localTaxCountries.value = response.data.countries || [];
    } catch (error) {
        console.error('Failed to fetch local tax countries:', error);
        localTaxCountries.value = [];
    }
};

// Fetch countries supported by TaxJar API
const fetchApiSupportedCountries = async () => {
    try {
        const response = await axios.get(route('admin.tax-setting.get-api-supported-countries'));
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

        const response = await axios.post(route('admin.tax-setting.get-tax-rate'), payload);

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
    if (!Array.isArray(form.gallery)) {
        galleryPreviews.value = [];
        return;
    }
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
    console.log('addArtist called with artistInput:', artistInput.value);
    if (artistInput.value.trim()) {
        form.artists.push(artistInput.value.trim());
        // Ensure artist_image array has same length as artists array
        if (!form.artist_image) {
            form.artist_image = [];
        }
        // Add null placeholder for this artist's image
        form.artist_image.push(null);
        artistInput.value = "";
        console.log('Artist added. Arrays now:', {
            artists: form.artists,
            artist_image: form.artist_image
        });
    }
};
const removeArtist = (index: number) => {
    form.artists.splice(index, 1);
    // Always remove the corresponding image to maintain array synchronization
    if (form.artist_image) {
        form.artist_image.splice(index, 1);
    }
    // Also remove the corresponding input ref
    if (artistImageInputs.value) {
        artistImageInputs.value.splice(index, 1);
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

<style scoped>
.preview-wrapper {
    display: inline-block;
    position: relative;
    margin: 8px;
}

.preview-item {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 2px solid rgba(0, 174, 239, 0.2);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.preview-item[controls] {
    /* Additional styles for video elements */
    background: #000;
}

.preview-item:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 174, 239, 0.2);
}

.remove-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    transition: all 0.3s ease;
}

.remove-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
}

.form-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 32px;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0, 174, 239, 0.08);
    margin-bottom: 24px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    border: 1px solid rgba(0, 174, 239, 0.1);
}

.form-section:hover {
    box-shadow: 0 8px 32px rgba(0, 174, 239, 0.12);
    transform: translateY(-2px);
}

.form-section h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: #1a202c;
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    padding-bottom: 12px;
    border-bottom: 3px solid transparent;
    border-image: linear-gradient(90deg, #00AEEF 0%, #FFB300 100%);
    border-image-slice: 1;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #334155;
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #ffffff;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #00AEEF;
    outline: none;
    box-shadow: 0 0 0 4px rgba(0, 174, 239, 0.1);
    background: #f8fafc;
}

.submit-btn {
    background: linear-gradient(90deg, #00AEEF 0%, #FFEB3B 100%);
    color: #fff;
    padding: 15px 40px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.3em;
    font-weight: 600;
    display: block;
    margin: 40px auto 0;
    transition: transform 0.3s ease;
}

.submit-btn:hover {
    transform: scale(1.05);
}

/* Stylish Upload Section */
.upload-area {
    position: relative;
    height: 300px;
    background-image: url('https://images.pexels.com/photos/3775593/pexels-photo-3775593.jpeg');
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    z-index: 1;
}

.upload-card {
    position: relative;
    z-index: 2;
    background-color: #fff;
    padding: 20px 40px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.upload-icon {
    font-size: 2em;
    color: #007BFF;
    margin-bottom: 10px;
}

.upload-text {
    font-size: 1.2em;
    color: #007BFF;
    font-weight: 500;
}

.add-media-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 1.5em;
    cursor: pointer;
    z-index: 2;
    transition: transform 0.3s ease;
}

.add-media-btn:hover {
    transform: scale(1.1);
}

.upload-input {
    display: none;
}

/* Overview Text */
.overview-text {
    font-size: 1.1em;
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

/* Section Overview */
.section-overview {
    font-size: 0.95rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 8px;
    border-left: 4px solid #00AEEF;
}

/* Add Section Button */
.add-section-btn {
    position: absolute;
    top: 24px;
    right: 24px;
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    font-size: 1.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.add-section-btn:hover {
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 6px 20px rgba(0, 174, 239, 0.4);
}

.add-section-btn:active {
    transform: scale(0.95);
}

/* Location Specific Styles */
.location-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.location-tab {
    padding: 10px 20px;
    border-radius: 20px;
    background-color: #f0f0f0;
    color: #333;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.location-tab.active {
    background-color: #007BFF;
    color: #fff;
}

.location-search {
    position: relative;
}

.location-search input {
    width: 100%;
    padding: 12px 40px 12px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
}

.location-search::before {
    content: '??';
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
}

.error-message {
    color: #ff0000;
    font-size: 0.9em;
    margin-top: 5px;
    display: none;
    /* Hide by default */
}

.add-details-link {
    color: #007BFF;
    text-decoration: none;
    font-size: 0.9em;
    display: block;
    margin-bottom: 20px;
}

.map-container {
    height: 200px;
    background-color: #e0f7fa;
    border-radius: 10px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.map-placeholder {
    width: 100%;
    height: 100%;
    background: url('https://maps.googleapis.com/maps/api/staticmap?center=San+Francisco&zoom=13&size=600x300&maptype=roadmap&key=YOUR_API_KEY') no-repeat center center;
    background-size: cover;
}

.reserved-seating {
    background-color: #f0f0f0;
    padding: 10px;
    border-radius: 8px;
}

.reserved-seating label {
    display: flex;
    align-items: center;
    font-weight: 500;
}

.reserved-seating input[type="checkbox"] {
    margin-right: 10px;
}

.reserved-description {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}

/* Date and Time Styles */
.event-type-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.event-type-tab {
    padding: 10px 20px;
    border-radius: 20px;
    background-color: #f0f0f0;
    color: #333;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    border: 1px solid #007BFF;
}

.event-type-tab.active {
    background-color: #007BFF;
    color: #fff;
    border: none;
}

.event-type-tab .new-badge {
    background-color: #007BFF;
    color: #fff;
    border-radius: 10px;
    padding: 2px 8px;
    margin-left: 10px;
    font-size: 0.8em;
}

.event-type-tab.active .new-badge {
    background-color: #fff;
    color: #007BFF;
}

.event-type-tab .radio {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #aaa;
    margin-left: auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.event-type-tab.active .radio {
    border-color: #fff;
}

.event-type-tab .radio-inner {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #007BFF;
}

.event-type-tab.active .radio-inner {
    background-color: #fff;
}

.date-time-layout {
    display: flex;
    gap: 20px;
    align-items: flex-end;
}

.date-input {
    flex: 2;
}

.time-input {
    flex: 1;
}

.more-options {
    color: #007BFF;
    text-decoration: none;
    font-size: 0.9em;
    display: block;
    margin-top: 10px;
}

/* Organizer Information Styles */
.organizer-image-placeholder {
    width: 200px;
    height: 150px;
    background-color: #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.organizer-image-placeholder .icon {
    font-size: 50px;
    color: #fff;
}

/* Icon placeholders with modern touch */
.icon-home::before {
    content: '?? ';
    font-size: 1.2em;
}

.icon-dashboard::before {
    content: '? ';
    font-size: 1.2em;
}

.icon-profile::before {
    content: '??? ';
    font-size: 1.2em;
}

.icon-events::before {
    content: '?? ';
    font-size: 1.2em;
}

.icon-venues::before {
    content: '?? ';
    font-size: 1.2em;
}

.icon-scanner::before {
    content: '?? ';
    font-size: 1.2em;
}

.icon-pos::before {
    content: '??? ';
    font-size: 1.2em;
}

.icon-reviews::before {
    content: '? ';
    font-size: 1.2em;
}

.icon-payouts::before {
    content: '?? ';
    font-size: 1.2em;
}

.icon-reports::before {
    content: '?? ';
    font-size: 1.2em;
}

.icon-account::before {
    content: '?? ';
    font-size: 1.2em;
}

footer {
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 20px;
    font-size: 0.9em;
    margin-top: auto;
}

footer a {
    color: #fff;
    text-decoration: none;
    margin: 0 10px;
    transition: color 0.3s ease;
}

footer a:hover {
    color: #FFD700;
}

/* Additional Options Styles */
.radio-group {
    display: flex;
    align-items: center;
}

.radio-group label {
    display: flex;
    align-items: center;
    margin-right: 20px;
    font-size: 14px;
}

.radio-group input[type="radio"] {
    margin-right: 5px;
}

.editor-toolbar {
    display: flex;
    align-items: center;
    background-color: #f9f9f9;
    padding: 5px;
    border: 1px solid #ddd;
    border-bottom: none;
    border-radius: 4px 4px 0 0;
}

.editor-toolbar button {
    background: none;
    border: none;
    margin-right: 5px;
    cursor: pointer;
    font-size: 14px;
}

.editor-content {
    border: 1px solid #ddd;
    padding: 10px;
    min-height: 100px;
    border-radius: 0 0 4px 4px;
    font-size: 14px;
    color: #aaa;
}

/* Enhanced styles */
.preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.preview-item {
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

input[type="checkbox"]:checked {
    background-color: #007BFF !important;
}

.audience-checkboxes label input[type="checkbox"] {
    appearance: none;
    width: 20px;
    height: 20px;
    background-color: #eee;
    border-radius: 4px;
    margin-right: 5px;
    position: relative;
}

.audience-checkboxes label input[type="checkbox"]:checked::before {
    content: '\2713';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-size: 14px;
}

.audience-checkboxes label input[type="checkbox"]:checked {
    background-color: #007BFF;
}

/* Event type tab functionality */
.recurring-options {
    display: none;
}

/* Simple validation */
.invalid {
    border-color: #ff0000 !important;
}

/* New toggle styles for attendees and reviews */
.toggle-group {
    display: flex;
    gap: 15px;
    align-items: center;
}

.toggle-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #666;
    font-size: 1em;
}

.toggle-group input[type="radio"] {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #ccc;
    margin-right: 8px;
    position: relative;
}

.toggle-group input[type="radio"]:checked {
    border-color: #00AEEF;
    background-color: #00AEEF;
}

.toggle-group input[type="radio"]:not(:checked) {
    background-color: #f0f0f0;
}

.info {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 10px;
}

.info-icon {
    color: #00AEEF;
    margin-right: 5px;
    font-size: 1em;
}

.form-group>label {
    color: #333;
    font-weight: 600;
}

.form-group>label::after {
    content: '*';
    color: #FFEB3B;
    margin-left: 2px;
}

/* New styles for Images gallery and Artists */
.add-button {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    color: #334155;
    border: 2px solid #00AEEF;
    padding: 10px 24px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 174, 239, 0.1);
}

.add-button:hover {
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
}

.artists-input {
    background-color: #f0f7ff;
    border: none;
    padding: 12px;
    border-radius: 8px;
}

.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.tag {
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
    color: #0369a1;
    padding: 8px 14px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    border: 1px solid rgba(3, 105, 161, 0.2);
    transition: all 0.3s ease;
}

.tag:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(3, 105, 161, 0.2);
}

.tag button {
    background: none;
    border: none;
    color: #0369a1;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

/* Artist section styles */
.artist-item {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid rgba(0, 174, 239, 0.1);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
}

.artist-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 174, 239, 0.15);
}

.artist-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.artist-name {
    font-weight: 600;
    color: #1a202c;
    font-size: 0.95rem;
    min-width: 120px;
}

.artist-image-upload {
    display: flex;
    align-items: center;
    gap: 8px;
}

.image-upload-btn {
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.image-upload-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 174, 239, 0.3);
}

.remove-image-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.remove-image-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

.artist-image-preview {
    position: relative;
}

.artist-image-preview img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid rgba(0, 174, 239, 0.2);
    transition: all 0.3s ease;
}

.artist-image-preview img:hover {
    transform: scale(1.05);
    border-color: #00AEEF;
}

.remove-artist-btn {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 1.2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin-left: auto;
}

.remove-artist-btn:hover {
    transform: scale(1.1);
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.scanners-input {
    background-color: #f0f7ff;
    border: none;
    padding: 12px;
    border-radius: 8px;
}

.more-options-content {
    display: none;
    margin-top: 20px;
}

.option-pill {
    background-color: #f0f7ff;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-right: 10px;
    display: inline-block;
}

.option-pill.selected {
    background-color: #00AEEF;
    color: #fff;
}

.options-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.age-options {
    display: none;
}

.guardian-options {
    display: none;
}

.save-btn {
    background-color: #FFEB3B;
    color: #000;
    padding: 10px 20px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    margin-top: 10px;
    float: left;
}
</style>
