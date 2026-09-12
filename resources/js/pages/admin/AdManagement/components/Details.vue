<script setup lang="ts">
import { onMounted, ref, watch, watchEffect } from 'vue';
import { useCountryStateCity } from '@/composables/useCountryStateCity';

const props = defineProps<{
    details: {
        category: string;
        advertiserName: string;
        websiteUrl: string;
        contactEmail: string;
        contactPhone: string;
        headline: string;
        shortDescription: string;
        country: string;
        state: string;
        city: string;
        venue: string;
    };
    errors?: Record<string, string[]>;
}>();

const emit = defineEmits<{
    (e: 'update-details', payload: Partial<typeof props.details>): void;
}>();

const activeSelectedCat = ref(props.details.category || 'restaurant');
const advertiserName = ref(props.details.advertiserName || '');
const websiteUrl = ref(props.details.websiteUrl || '');
const contactEmail = ref(props.details.contactEmail || '');
const contactPhone = ref(props.details.contactPhone || '');
const headline = ref(props.details.headline || '');
const shortDescription = ref(props.details.shortDescription || '');
const selectedCountryCode = ref(props.details.country || '');
const selectedStateCode = ref(props.details.state || '');
const city = ref(props.details.city || '');
const venue = ref(props.details.venue || '');

const {
    countries,
    states,
    fetchCountries,
    fetchStates,
} = useCountryStateCity();

const handleCountryChange = (event: Event) => {
    selectedCountryCode.value = (event.target as HTMLSelectElement).value;
    selectedStateCode.value = '';
};

const handleStateChange = (event: Event) => {
    selectedStateCode.value = (event.target as HTMLSelectElement).value;
};

onMounted(() => {
    fetchCountries();
    if (selectedCountryCode.value) {
        fetchStates(selectedCountryCode.value);
    }
});

watch(selectedCountryCode, (newCode) => {
    if (newCode) {
        fetchStates(newCode);
    }
});

watchEffect(() => {
    emit('update-details', {
        category: activeSelectedCat.value,
        advertiserName: advertiserName.value,
        websiteUrl: websiteUrl.value,
        contactEmail: contactEmail.value,
        contactPhone: contactPhone.value,
        headline: headline.value,
        shortDescription: shortDescription.value,
        country: selectedCountryCode.value,
        state: selectedStateCode.value,
        city: city.value,
        venue: venue.value,
    });
});
</script>

<template>
    <p class="text-muted mb16">Choose the ad category — this controls how it appears in the app and
        where it targets users.</p>

    <div class="cat-grid">
        <label class="cat-card" :class="activeSelectedCat === 'restaurant' ? 'selected' : ''"
            @click="activeSelectedCat = 'restaurant'">
            <input type="radio" name="cat" value="restaurant" v-model="activeSelectedCat" />
            <div class="cat-icon">🍽️</div>
            <div class="cat-name">Restaurant</div>
            <div class="cat-desc">Food, drinks, dining</div>
        </label>
        <label class="cat-card" :class="activeSelectedCat === 'club' ? 'selected' : ''"
            @click="activeSelectedCat = 'club'">
            <input type="radio" name="cat" value="club" v-model="activeSelectedCat" />
            <div class="cat-icon">🎭</div>
            <div class="cat-name">Club / Fête</div>
            <div class="cat-desc">Nightlife, events, festivals</div>
        </label>
        <label class="cat-card" :class="activeSelectedCat === 'general' ? 'selected' : ''"
            @click="activeSelectedCat = 'general'">
            <input type="radio" name="cat" value="general" v-model="activeSelectedCat" />
            <div class="cat-icon">📢</div>
            <div class="cat-name">General Ad</div>
            <div class="cat-desc">Retail, services, brands</div>
        </label>
    </div>

    <div class="form-grid">
        <div class="field">
            <label>Advertiser Name *</label>
            <input type="text" placeholder="e.g. Tony's Pizzeria" v-model="advertiserName" />
            <div v-if="errors?.advertiser_name" class="error-message">{{ errors.advertiser_name.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Website URL *</label>
            <input type="url" placeholder="https://example.com" v-model="websiteUrl" />
            <div v-if="errors?.website_url" class="error-message">{{ errors.website_url.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Contact Email</label>
            <input type="email" placeholder="contact@example.com" v-model="contactEmail" />
            <div v-if="errors?.contact_email" class="error-message">{{ errors.contact_email.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Contact Phone</label>
            <input type="tel" placeholder="+1 (555) 000-0000" v-model="contactPhone" />
            <div v-if="errors?.contact_phone" class="error-message">{{ errors.contact_phone.join(', ') }}</div>
        </div>
        <div class="field full">
            <label>Ad Headline *</label>
            <input type="text" placeholder="Short punchy headline shown on the tile (max 60 chars)" maxlength="60" v-model="headline" />
            <div v-if="errors?.headline" class="error-message">{{ errors.headline.join(', ') }}</div>
        </div>
        <div class="field full">
            <label>Short Description</label>
            <textarea placeholder="One or two sentences that appear beneath the image (max 120 chars)"
                maxlength="120" v-model="shortDescription"></textarea>
            <div v-if="errors?.short_description" class="error-message">{{ errors.short_description.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Country</label>
            <select v-model="selectedCountryCode" @change="handleCountryChange">
                <option value="">Select Country</option>
                <option v-for="country in countries" :key="country.value" :value="country.value">{{ country.label }}
                </option>
            </select>
            <div v-if="errors?.country" class="error-message">{{ errors.country.join(', ') }}</div>
        </div>
        <div class="field">
            <label>State / Province</label>
            <select v-model="selectedStateCode" @change="handleStateChange">
                <option value="">Select State</option>
                <option v-for="state in states" :key="state.value" :value="state.value">
                    {{ state.label }}
                </option>
            </select>
            <div v-if="errors?.state" class="error-message">{{ errors.state.join(', ') }}</div>
        </div>
        <div class="field">
            <label>City</label>
            <input type="text" placeholder="e.g. Miami" v-model="city" />
            <div v-if="errors?.city" class="error-message">{{ errors.city.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Venue / Location</label>
            <input type="text" placeholder="e.g. 123 Main St" v-model="venue" />
            <div v-if="errors?.venue" class="error-message">{{ errors.venue.join(', ') }}</div>
        </div>
    </div>
</template>

<style scoped>
.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
}
</style>
