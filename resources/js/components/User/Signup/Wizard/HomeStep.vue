<script setup lang="ts">
// Step: "Where's home base?" Country, region and city come from the same country list the rest
// of LinkUp uses (useCountryStateCity), so the saved values match the old wizard and the app.
import { useCountryStateCity } from '@/composables/useCountryStateCity';
import { computed, nextTick, onMounted, ref } from 'vue';
import { popularCities, regionLabels } from './copy';
import { firstError, postJson } from './http';
import * as s from './styles';
import type { HomeAnswer } from './types';

const answer = defineModel<HomeAnswer>({ required: true });
const props = defineProps<{ locateUrl: string; errors: Record<string, string> }>();
/** Fired once "Use my current location" has filled country, region and city, so the page can move on. */
const emit = defineEmits<{ located: [] }>();

const { countries, states, cities, isLoadingState, isLoadingCity, fetchCountries, fetchStates, fetchCities } = useCountryStateCity();

const located = ref(false);
const locating = ref(false);
const locateError = ref('');
const typedCity = ref(false);

const norm = (v: string) =>
    v
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/\b(parish|province|state|island|islands|region|district|county|of|the)\b/g, '')
        .replace(/[^a-z0-9]/g, '');

/** An option whose name matches, ignoring words like "Parish" or "State". */
function matchOption<T extends { label: string; value: string }>(list: T[], name: string): T | undefined {
    const n = norm(name);
    if (!n) return undefined;
    return list.find((o) => norm(o.label) === n) ?? list.find((o) => norm(o.label).includes(n) || n.includes(norm(o.label)));
}

/** Options with the saved value kept in the list even when the source doesn't have it. */
function withCurrent(list: Array<{ label: string; value: string }>, value: string, label: string) {
    if (!value || list.some((o) => o.value === value)) return list;
    return [{ label: label || value, value }, ...list];
}

const countryOptions = computed(() =>
    withCurrent(
        countries.value.map((c) => ({ label: String(c.label), value: String(c.value) })),
        answer.value.country,
        answer.value.country_name,
    ),
);
const stateOptions = computed(() =>
    withCurrent(
        states.value.map((o) => ({ label: String(o.label), value: String(o.value) })),
        answer.value.state,
        answer.value.state_name,
    ),
);
const cityOptions = computed(() =>
    withCurrent(
        cities.value.map((o) => ({ label: String(o.label), value: String(o.value) })),
        answer.value.city,
        answer.value.city,
    ),
);

const regionLabel = computed(() => (answer.value.country ? regionLabels[answer.value.country] || 'State / Region' : 'State / Parish'));
const regionPlaceholder = computed(() => (answer.value.country ? 'Select ' + regionLabel.value.toLowerCase() : 'Choose a country first'));
const cityPlaceholder = computed(() => (answer.value.state ? (isLoadingCity.value ? 'Loading cities…' : 'Select city') : 'Choose a region first'));
/** No city list for this region: let the member type it. */
const cityByHand = computed(() => typedCity.value || (!!answer.value.state && !isLoadingCity.value && cities.value.length === 0));

// The parent's copy only reaches this component on the next render, so several changes in a row
// (country, then region, then city) would each start from stale values. Keep the newest here.
let latest: HomeAnswer | null = null;
const current = () => latest ?? answer.value;

function set(patch: Partial<HomeAnswer>) {
    latest = { ...current(), ...patch };
    answer.value = latest;
    nextTick(() => (latest = null));
}

async function loadStates(country: string) {
    await fetchStates(country);
}

async function loadCities(country: string, state: string) {
    await fetchCities(country, state);
}

async function onCountry(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    const label = countryOptions.value.find((c) => c.value === value)?.label ?? '';
    located.value = false;
    typedCity.value = false;
    set({ country: value, country_name: label, state: '', state_name: '', city: '', latitude: null, longitude: null });
    states.value = [];
    cities.value = [];
    if (value) await loadStates(value);
}

async function onRegion(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    const label = stateOptions.value.find((o) => o.value === value)?.label ?? '';
    located.value = false;
    typedCity.value = false;
    set({ state: value, state_name: label, city: '', latitude: null, longitude: null });
    cities.value = [];
    if (value) await loadCities(current().country, value);
}

function onCity(event: Event) {
    located.value = false;
    set({ city: (event.target as HTMLSelectElement).value, latitude: null, longitude: null });
}

/** Fill all three boxes from names (a popular chip or the located position). */
async function pickPlace(code: string, countryName: string, region: string | null, city: string | null, position?: { latitude: number; longitude: number }) {
    if (!countries.value.length) await fetchCountries();
    const country = countries.value.find((c) => String(c.value) === code);
    set({
        country: code,
        country_name: country ? String(country.label) : countryName,
        state: '',
        state_name: '',
        city: '',
        latitude: position?.latitude ?? null,
        longitude: position?.longitude ?? null,
    });
    typedCity.value = false;
    await loadStates(code);
    if (!region) return;
    const state = matchOption(stateOptions.value, region);
    set({ state: state?.value ?? region, state_name: state?.label ?? region });
    await loadCities(code, current().state);
    if (!city) return;
    const town = matchOption(cityOptions.value, city);
    set({ city: town?.value ?? city });
}

/** The best option for any of these names: an exact match first, then a close one. */
function bestOption<T extends { label: string; value: string }>(list: T[], names: string[]): T | undefined {
    for (const name of names) {
        const n = norm(name);
        const hit = n && list.find((o) => norm(o.label) === n);
        if (hit) return hit;
    }
    for (const name of names) {
        const hit = matchOption(list, name);
        if (hit) return hit;
    }
    return undefined;
}

interface LocatedPlace {
    country_code: string;
    country: string;
    state: string | null;
    city: string | null;
    region_codes?: string[];
    names?: string[];
}

/**
 * Fill the three boxes from a located position. Countries name places differently, so every name
 * the lookup returned is tried against the lists (and the official region code, e.g. Bahamas "NP").
 */
async function pickLocated(place: LocatedPlace, position: { latitude: number; longitude: number }) {
    if (!countries.value.length) await fetchCountries();
    const country = countries.value.find((c) => String(c.value) === place.country_code);
    set({
        country: place.country_code,
        country_name: country ? String(country.label) : place.country,
        state: '',
        state_name: '',
        city: '',
        latitude: position.latitude,
        longitude: position.longitude,
    });
    typedCity.value = false;
    await loadStates(place.country_code);

    const names = place.names?.length ? place.names : [place.city, place.state].filter((n): n is string => !!n);
    const broadFirst = [...names].reverse();
    const byCode = (place.region_codes ?? []).map((code) => stateOptions.value.find((o) => o.value.toUpperCase() === code.toUpperCase())).find(Boolean);
    const region = byCode ?? bestOption(stateOptions.value, place.state ? [place.state, ...broadFirst] : broadFirst);
    if (region) {
        set({ state: region.value, state_name: region.label });
    } else if (place.state) {
        set({ state: place.state, state_name: place.state });
    } else {
        return;
    }

    await loadCities(place.country_code, current().state);
    const others = names.filter((n) => norm(n) !== norm(current().state_name));
    const town = bestOption(cityOptions.value, place.city ? [place.city, ...others] : others);
    if (town) {
        set({ city: town.value });
    } else if (place.city && norm(place.city) !== norm(current().state_name)) {
        set({ city: place.city });
        if (!cities.value.length) typedCity.value = true;
    }
}

const isPopular = (code: string, city: string) => answer.value.country === code && answer.value.city === city;

function locate() {
    locateError.value = '';
    if (!('geolocation' in navigator)) {
        locateError.value = "Your browser can't share your location. Pick it below instead.";
        return;
    }
    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            const result = await postJson<LocatedPlace>(props.locateUrl, { latitude, longitude });
            if (!result.ok) {
                locating.value = false;
                locateError.value = firstError(result, "We couldn't find your city. Pick it below instead.");
                return;
            }
            await pickLocated(result.data, { latitude, longitude });
            located.value = !!current().city;
            locating.value = false;
            if (!current().state || !current().city) {
                locateError.value = 'We found your country. Pick your region and city below.';
                return;
            }
            emit('located');
        },
        () => {
            locating.value = false;
            locateError.value = 'Location is turned off for this site. Pick your city below instead.';
        },
        { enableHighAccuracy: false, timeout: 10000, maximumAge: 600000 },
    );
}

const locateLabel = computed(() => (locating.value ? 'Finding you…' : located.value && answer.value.city ? 'Location found · ' + answer.value.city : 'Use my current location'));

onMounted(async () => {
    await fetchCountries();
    if (answer.value.country) {
        await loadStates(answer.value.country);
        if (answer.value.state) await loadCities(answer.value.country, answer.value.state);
    }
});
</script>

<template>
    <div class="flex flex-col gap-[18px]">
        <div class="flex flex-col gap-1.5">
            <button
                type="button"
                :disabled="locating"
                style="display: flex; align-items: center; justify-content: center; gap: 10px; height: 54px; border-radius: 14px; border: 1.5px solid #bfd0f5; background: #ffffff; color: #1d4ed8; font-family: inherit; font-size: 16px; font-weight: 700; cursor: pointer"
                @click="locate"
            >
                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
                {{ locateLabel }}
            </button>
            <p v-if="locateError" role="status" style="margin: 0; font-size: 13px; font-weight: 600; color: #b45309">{{ locateError }}</p>
        </div>

        <div class="lu-grid2" style="gap: 14px">
            <div class="flex flex-col gap-1.5">
                <label for="f-country" :style="s.label">Country</label>
                <select id="f-country" :value="answer.country" :style="s.select()" @change="onCountry">
                    <option value="">Select country</option>
                    <option v-for="c in countryOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="f-region" :style="s.label">{{ regionLabel }}</label>
                <select id="f-region" :value="answer.state" :disabled="!answer.country" :style="s.select(!!answer.country)" @change="onRegion">
                    <option value="">{{ isLoadingState ? 'Loading…' : regionPlaceholder }}</option>
                    <option v-for="r in stateOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="f-city" :style="s.label">City</label>
            <input
                v-if="cityByHand"
                id="f-city"
                type="text"
                maxlength="100"
                autocomplete="address-level2"
                placeholder="Type your city"
                :value="answer.city"
                :style="s.textField"
                @input="set({ city: ($event.target as HTMLInputElement).value, latitude: null, longitude: null })"
            />
            <select v-else id="f-city" :value="answer.city" :disabled="!answer.state" :style="s.select(!!answer.state)" @change="onCity">
                <option value="">{{ cityPlaceholder }}</option>
                <option v-for="c in cityOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
            </select>
            <button
                v-if="answer.state && !cityByHand && cities.length"
                type="button"
                style="align-self: flex-start; min-height: 32px; padding: 0; border: none; background: none; font-family: inherit; font-size: 13px; font-weight: 700; color: #1d4ed8; cursor: pointer"
                @click="typedCity = true"
            >
                My city isn't listed
            </button>
            <p v-if="errors.city || errors.country || errors.state" :style="s.fieldError" role="alert">{{ errors.city || errors.country || errors.state }}</p>
        </div>

        <div class="flex flex-col gap-2">
            <span style="font-size: 13px; font-weight: 700; color: #475569">Popular with the LinkUp community</span>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="[code, name, region, city] in popularCities"
                    :key="city"
                    type="button"
                    :aria-pressed="isPopular(code, city) ? 'true' : 'false'"
                    :style="s.chip(isPopular(code, city))"
                    @click="pickPlace(code, name, region, city)"
                >
                    {{ city }}
                </button>
            </div>
        </div>
    </div>
</template>
