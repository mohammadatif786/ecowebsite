<template>
    <div class="form-section">
        <h2>Location</h2>
        <button type="button" class="add-section-btn" @click="toggleSection(0)" v-show="!showFields[0]">+</button>
        <button type="button" class="add-section-btn" @click="toggleSection(0)" v-show="showFields[0]">-</button>

        <p class="section-overview" v-if="!showFields[0]">
            Provide the venue name, full address, city, and country so attendees know where the event is taking place.
        </p>

        <div class="form-fields" v-if="showFields[0]">
            <!-- Tabs -->
            <div class="location-tabs">
                <div class="location-tab" :class="{ active: activeTab === 'venue' }" @click="activeTab = 'venue'">
                    ?? Venue
                </div>
                <div class="location-tab" :class="{ active: activeTab === 'online' }" @click="activeTab = 'online'">
                    ??? Online event
                </div>
                <div class="location-tab" :class="{ active: activeTab === 'tba' }" @click="activeTab = 'tba'">
                    ?? To be announced
                </div>
            </div>

            <!-- Venue -->
            <div v-if="activeTab === 'venue'">
                <div class="location-search form-group">
                    <label>Venue Location</label>
                    <!-- <input type="text" v-model="venueInput" placeholder="Enter venue name or address"
                        @input="debouncedSearch"> -->
                    <input type="text" v-model="venueInput" placeholder="Enter venue name or address">
                    <div class="error-message">Location is required</div>
                </div>
                <div class="grid md:grid-cols-2 gap-3">
                    <div class="location-search form-group">
                        <label>Latitude</label>
                        <input type="text" v-model="form.latitude" placeholder="Enter latitude">
                    </div>
                    <div class="location-search form-group">
                        <label>Longtitude</label>
                        <input type="text" v-model="form.longtitude" placeholder="Enter longtitude">
                    </div>
                </div>
                <div class="map-container">
                    <div id="map" class="map-placeholder"></div>
                </div>
                <div class="reserved-seating">
                    <label><input type="checkbox"> Reserved seating</label>
                    <p class="reserved-description">
                        Use your venue map to set price tiers for each section and choose whether attendees can pick
                        their seat.
                    </p>
                </div>
            </div>

            <!-- Online -->
            <div v-if="activeTab === 'online'">
                <div class="form-group">
                    <label>Online Event Link</label>
                    <input type="url" v-model="form.venue" placeholder="Enter online event link (Zoom, Teams, etc.)">
                </div>
            </div>

            <!-- TBA -->
            <div v-if="activeTab === 'tba'">
                <p>Location to be announced later.</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive, watch } from "vue";
const props = defineProps<{
    events?: { location_type?: string, venue?: string, latitude?: string, longtitude?: string }
}>();

const modelValue = defineModel<{
    location_type: string;
    venue: string;
    latitude: string;
    longtitude: string;
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
    // Clear venue when switching away from venue tab
    if (val !== 'venue') {
        form.venue = '';
    }
});

// Venue search and map functionality
const venueInput = ref('');
const map = ref<any>(null);
const marker = ref<any>(null);
const geocoder = ref<any>(null);

// Search venue and update map
const searchVenue = async () => {
    if (!venueInput.value.trim() || !geocoder.value) return;

    try {
        const results = await new Promise<any[]>((resolve, reject) => {
            geocoder.value.geocode(
                { address: venueInput.value },
                (results: any, status: any) => {
                    if (status === 'OK' && results) {
                        resolve(results);
                    } else {
                        reject(new Error('Geocoding failed'));
                    }
                }
            );
        });

        if (results.length > 0) {
            const location = results[0].geometry.location;
            const formattedAddress = results[0].formatted_address;

            // Update form data
            form.venue = formattedAddress;

            // Update map
            if (map.value) {
                map.value.setCenter(location);
                map.value.setZoom(15);

                // Update or create marker
                if (marker.value) {
                    marker.value.setPosition(location);
                } else {
                    marker.value = new (window as any).google.maps.Marker({
                        position: location,
                        map: map.value,
                        title: formattedAddress
                    });
                }
            }
        }
    } catch (error) {
        console.error('Error searching venue:', error);
    }
};

// Debounced venue search
let searchTimeout: any;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchVenue, 500);
};

watch(venueInput, () => {
    if (venueInput.value.trim()) {
        debouncedSearch();
    }
});
// Google Maps initialization
onMounted(() => {
    // Initialize venue input with existing data
    if (form.venue) {
        venueInput.value = form.venue;
    }

    // Initialize Google Maps
    if ((window as any).google?.maps) {
        const mapElement = document.getElementById('map') as HTMLElement;
        if (mapElement) {
            map.value = new (window as any).google.maps.Map(mapElement, {
                center: { lat: 37.7749, lng: -122.4194 },
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false
            });

            geocoder.value = new (window as any).google.maps.Geocoder();

            // If we have existing venue data, search for it
            if (form.venue) {
                searchVenue();
            }
        }
    } else {
        console.warn('Google Maps API not loaded');
    }
});

onMounted(() => {
    loadMap()
});

const loadMap = () => {
    const lat = props.events?.latitude;
    const lon = props.events?.longtitude;
    if (lat && lon) {
        const map = L.map('map').setView([lat, lon], 15);
        // OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        // marker + popup
        const marker = L.marker([lat, lon]).addTo(map);
    }
}
</script>

<style scoped>
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
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #007BFF;
    outline: none;
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
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.location-tab {
    padding: 14px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #475569;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 8px;
    border: 2px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.95rem;
}

.location-tab:hover {
    border-color: #00AEEF;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 174, 239, 0.15);
}

.location-tab.active {
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    color: #fff;
    border-color: #00AEEF;
    box-shadow: 0 6px 20px rgba(0, 174, 239, 0.3);
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
    background-color: #fff;
    color: #333;
    border: 1px solid #ddd;
    padding: 8px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s;
}

.add-button:hover {
    background-color: #f0f0f0;
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
    gap: 5px;
    margin-top: 10px;
}

.tag {
    background-color: #e0e0e0;
    padding: 5px 10px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.tag-remove {
    cursor: pointer;
    color: #999;
}

.tag-remove:hover {
    color: #333;
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
