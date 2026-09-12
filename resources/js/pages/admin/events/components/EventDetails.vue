<template>
    <div class="form-section">
        <h2>Event Details</h2>
        <!-- Only show "+" when section is closed -->
        <button type="button" class="add-section-btn" @click.stop="toggleSection(0)" v-show="!showFields[0]">
            +
        </button>

        <!-- Only show "-" when section is open -->
        <button type="button" class="add-section-btn" @click.stop="toggleSection(0)" v-show="showFields[0]">
            -
        </button>
        <p class="section-overview" v-if="!showFields[0]">Enter the event name, a detailed description, and select the
            appropriate
            category to help attendees know what to expect.</p>
        <div class="form-fields" v-if="showFields[0]">
            <div class="form-group">
                <label for="event-name">Event Name</label>
                <input type="text" id="event-name" v-model="form.name" placeholder="Enter event name" required>
            </div>
            <div class="form-group">
                <label for="event-description">Description</label>
                <textarea id="event-description" rows="5" v-model="form.description" placeholder="Describe your event"
                    required></textarea>
            </div>
            <div class="form-group">
                <label for="event-category">Category</label>
                <select id="event-category" v-model="form.category_id">
                    <option value="">Select Category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id"> {{ category.name }}
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label>Audiences</label>
                <p style="color: #00AEEF; font-size: 0.9em; margin-bottom: 10px;"><span
                        style="margin-right: 5px;">?</span>Select the audience types that are targeted in your
                    event.</p>
                <div class="audience-checkboxes" style="display: flex; gap: 15px;">
                    <label><input type="checkbox" value="Adults" v-model="form.audiences"> Adults</label>
                    <label><input type="checkbox" value="Children" v-model="form.audiences"> Children</label>
                    <label><input type="checkbox" value="Family" v-model="form.audiences"> Family</label>
                    <label><input type="checkbox" value="Youth" v-model="form.audiences"> Youth</label>
                    <label><input type="checkbox" value="Group" v-model="form.audiences"> Group</label>
                </div>
            </div>
            <div class="form-group">
                <label>Attendees</label>
                <p class="info"><span class="info-icon">?</span> Show the attendees number and list in the event
                    page</p>
                <div class="toggle-group">
                    <label><input type="radio" name="attendees" value="hide" v-model="form.attendees"> Hide</label>
                    <label><input type="radio" name="attendees" value="show" v-model="form.attendees"> Show</label>
                </div>
            </div>
            <div class="form-group">
                <label>Enable reviews</label>
                <div class="toggle-group">
                    <label><input type="radio" name="reviews" value="enable" v-model="form.reviews"> Enable</label>
                    <label><input type="radio" name="reviews" value="disable" v-model="form.reviews"> Disable</label>
                </div>
            </div>
            <div class="form-group">
                <label>Does this event date have a seating plan?</label>
                <div class="toggle-group">
                    <label><input type="radio" name="seating-plan" value="no" v-model="form.seating_plan"> No</label>
                    <label><input type="radio" name="seating-plan" value="yes" v-model="form.seating_plan"> Yes</label>
                </div>
            </div>
        </div>
        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul>
                <li v-for="(err, idx) in props.errors" :key="idx">
                    {{ err }}
                </li>
            </ul>
        </div>


    </div>
</template>
<script setup lang="ts">
import { ref, reactive, watch } from "vue";

const props = defineProps<{
    categories: Array<{ id: number; name: string }>
    errors?: string[];
    eventDetails: any
}>()



const modelValue = defineModel<{
    name: string;
    description: string;
    category_id: number | null;
    audiences: string[];
    attendees: string;
    reviews: string;
    seating_plan: string;
    venue: string;
}>({
    default: {
        name: "",
        description: "",
        category_id: null,
        audiences: [],
        attendees: "show",
        reviews: "enable",
        seating_plan: "no",
        venue: ""
    }
});
// Local reactive state bound to form inputs
const form = reactive(modelValue.value);
// Keep modelValue in sync with local form
watch(form, (val) => {
    modelValue.value = val;
}, { deep: true });


const selectedcategory = ref('')

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.eventDetails });

const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};

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

.audience-checkboxes {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.audience-checkboxes label {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #334155;
}

.audience-checkboxes label:hover {
    border-color: #00AEEF;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 174, 239, 0.15);
}

.audience-checkboxes label input[type="checkbox"] {
    appearance: none;
    width: 22px;
    height: 22px;
    background-color: #ffffff;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    margin-right: 10px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.audience-checkboxes label input[type="checkbox"]:checked {
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    border-color: #00AEEF;
}

.audience-checkboxes label input[type="checkbox"]:checked::before {
    content: '?';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-size: 14px;
    font-weight: 700;
}

.audience-checkboxes label:has(input:checked) {
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
    border-color: #00AEEF;
    color: #0369a1;
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
    gap: 12px;
    align-items: center;
}

.toggle-group label {
    display: flex;
    align-items: center;
    padding: 10px 18px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #334155;
    font-size: 0.95rem;
}

.toggle-group label:hover {
    border-color: #00AEEF;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 174, 239, 0.15);
}

.toggle-group input[type="radio"] {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    margin-right: 10px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #ffffff;
}

.toggle-group input[type="radio"]:checked {
    border-color: #00AEEF;
    background: linear-gradient(135deg, #00AEEF 0%, #0088cc 100%);
    box-shadow: 0 0 0 3px rgba(0, 174, 239, 0.2);
}

.toggle-group input[type="radio"]:checked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: white;
}

.toggle-group label:has(input:checked) {
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
    border-color: #00AEEF;
    color: #0369a1;
}

.info {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 12px;
    padding: 10px 14px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 8px;
    border-left: 3px solid #00AEEF;
    line-height: 1.5;
}

.info-icon {
    color: #00AEEF;
    margin-right: 6px;
    font-size: 1.1em;
    font-weight: 700;
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
