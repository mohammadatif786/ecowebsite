<style>
.sliderTracker {
    background-color: gainsboro;
    height: 5px;
}
</style>
<template>
    <AuthenticatedLayout>
        <main class="w-full flex-1 rounded-lg bg-gray-100 p-2 md:p-8">
            <h2 class="mb-6 text-center text-2xl font-semibold text-gray-800">Profile</h2>
            <!-- Event Picture Upload -->
            <div class="relative mb-6 flex flex-col justify-center">
                <FileUpload v-model="form.avatar" :default-image="user.avatar ? `${user.avatar}` : null"
                    :error="form.errors.avatar" />
            </div>
            <form @submit.prevent="handleSubmit" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- First Name & Last Name Row -->
                <div class="">
                    <label for="firstName" class="block">First Name:</label>
                    <BaseInput name="firstName" class="w-full" label="First Name" v-model="form.first_name"
                        placeholder="First Name" :error="form.errors.first_name" />
                </div>
                <div class="">
                    <label for="lastName" class="block">Last Name:</label>
                    <BaseInput name="lastName" class="w-full" label="Last Name" v-model="form.last_name"
                        placeholder="Last Name" :error="form.errors.last_name" />
                </div>
                <!-- Email & Description Row -->
                <div class="">
                    <label for="email" class="block">Email:</label>
                    <BaseInput name="email" class="w-full" label="Email" v-model="form.email" placeholder="Email"
                        :error="form.errors.email" />
                </div>
                <div class="">
                    <label for="description" class="block">Phone_number:</label>
                    <BaseInput name="phone_number" class="w-full" label="phone_number" v-model="form.phone_number"
                        placeholder="phone_number" :error="form.errors.phone_number" />
                </div>
                <div class="">
                    <label for="gender" class="block">Gender:</label>
                    <div>
                        <select v-model="form.gender"
                            class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:ring-0"
                            required>
                            <option disabled selected value="">Select State</option>
                            <option class="text-gray-500" value="male">Male</option>
                            <option class="text-gray-500" value="female">Female</option>
                        </select>
                    </div>
                </div>
                <!-- Birthdate -->
                <div class="">
                    <label class="block">Birthdate:</label>
                    <VueDatePicker v-model="form.birthday" :enable-time-picker="false" :auto-apply="true"
                        placeholder="Select your birth date" format="yyyy-MM-dd" :model-type="'yyyy-MM-dd'" />
                    <p class="mt-1 text-xs text-gray-600" v-if="computedAge !== null">Age: {{ computedAge }}</p>
                    <p class="text-xs font-semibold text-red-500">{{ form.errors.birthday }}</p>
                </div>
                <!-- Country & State Row -->
                <div class="">
                    <label for="country" class="block">Country:
                        <span v-if="isLoadingCountry">
                            <Loader />
                        </span></label>
                    <select v-model="form.country" @change="handleCountryChange"
                        class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:ring-0"
                        required>
                        <option disabled selected value="">Select Country</option>
                        <option class="text-gray-500" v-for="country in countries" :key="country.value"
                            :value="country.value">
                            {{ country.label }}
                        </option>
                    </select>
                </div>
                <div class="">
                    <label for="state" class="flex">State:
                        <span class="inline" v-if="isLoadingState">
                            <Loader />
                        </span></label>
                    <select v-model="form.state" @change="handleStateChange"
                        class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:ring-0"
                        required>
                        <option disabled selected value="">Select State</option>
                        <option v-for="state in states" :key="state.label" class="text-gray-500" :value="state.value">
                            {{ state.label }}
                        </option>
                        <option v-if="!form.country" disabled value="" class="text-gray-500">Please Select Country
                        </option>
                    </select>
                </div>
                <div class="">
                    <label for="city" class="flex">City:
                        <span v-if="isLoadingCity">
                            <Loader />
                        </span>
                    </label>
                    <select v-model="form.city"
                        class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:ring-0"
                        required>
                        <option disabled selected value="">Select city</option>
                        <option class="text-gray-500" v-for="city in cities" :key="city.value" :value="city.value">
                            {{ city.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="font-medium">Who you like to see:</label>
                    <div class="flex gap-4">
                        <label class="flex items-center"> <input v-model="form.link_me_with" type="radio" value="male"
                                class="mr-1" /> Male </label>
                        <label class="flex items-center">
                            <input v-model="form.link_me_with" type="radio" value="female" class="mr-1" /> Female
                        </label>
                        <label class="flex items-center"> <input v-model="form.link_me_with" type="radio" value="both"
                                class="mr-1" /> Both </label>
                    </div>
                </div>
                <div class="w-full">
                    <label for="">Age Filter {{ form.age_filter[0] }}-{{ form.age_filter[1] }}</label>

                    <SliderRoot v-model="form.age_filter" class="relative flex h-5 touch-none items-center select-none"
                        :max="100" :min="18" :step="1">
                        <SliderTrack class="sliderTracker relative h-[3px] grow rounded-full">
                            <SliderRange class="absolute h-full rounded-full bg-blue-500" />
                        </SliderTrack>
                        <SliderThumb v-for="thumb in form.age_filter" :key="thumb"
                            class="shadow-blackA7 hover:bg-violet3 focus:shadow-blackA8 block h-5 w-5 rounded-[10px] bg-blue-500 shadow-[0_2px_10px] focus:shadow-[0_0_0_5px] focus:outline-none"
                            aria-label="Volume" />
                    </SliderRoot>
                    <p class="text-xs font-semibold text-red-500">{{ form.errors.age_filter }}</p>
                </div>
                <div class="w-full">
                    <label for="">Distance Filter {{ form.distance_filter[0] }}-{{ form.distance_filter[1] }}</label>
                    <SliderRoot v-model="form.distance_filter"
                        class="relative flex h-5 touch-none items-center select-none" :max="100" :step="1">
                        <SliderTrack class="sliderTracker relative h-[3px] grow rounded-full">
                            <SliderRange class="absolute h-full rounded-full bg-blue-500" />
                        </SliderTrack>
                        <SliderThumb v-for="thumb in form.distance_filter" :key="thumb"
                            class="shadow-blackA7 hover:bg-violet3 focus:shadow-blackA8 block h-5 w-5 rounded-[10px] bg-blue-500 shadow-[0_2px_10px] focus:shadow-[0_0_0_5px] focus:outline-none"
                            aria-label="Volume" />
                    </SliderRoot>
                    <p class="text-xs font-semibold text-red-500">{{ form.errors.distance_filter }}</p>
                </div>

                <div class="">
                    <label for="city" class="block">Language:</label>
                    <select v-model="form.language"
                        class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:ring-0"
                        required>
                        <option disabled selected value="">Select city</option>
                        <option class="text-gray-500" v-for="language in languages" :key="language" :value="language">
                            {{ language }}
                        </option>
                    </select>
                </div>
                <div class="w-full">
                    <label for="city" class="block"> Tell Link Up about your root </label>
                    <select v-model="form.caribbean_interest"
                        class="w-full border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2 focus:ring-0">
                        <option v-for="caribbean in caribbeanIsland" :key="caribbean.name" :value="caribbean.name">
                            {{ caribbean.value }}
                        </option>
                    </select>
                </div>
                <div class="col-span-2">
                    <div class="mt-5 flex items-baseline justify-between">
                        <label class="text-sm font-semibold">
                            Event interests <span class="font-normal text-gray-500">(select all that apply, min
                                3)</span>
                        </label>
                        <small class="text-gray-500">{{ form.interests.length }} selected</small>
                    </div>

                    <!-- Chips -->
                    <div id="chips"
                        class="mt-3 grid grid-cols-2 gap-2 overflow-y-auto pr-1 sm:grid-cols-3 md:grid-cols-4">
                        <label v-for="(interest, index) in interests" :key="index" :class="[
                            'flex cursor-pointer items-center gap-2 rounded-full border-2 px-3 py-2 transition select-none',
                            form.interests.includes(interest.label)
                                ? 'border-blue-400 bg-blue-50 shadow-sm'
                                : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50/30',
                        ]" @click="toggleInterest(interest.label)">
                            <span>{{ interest.icon }}</span> {{ interest.label }}
                        </label>
                    </div>
                    <p class="mt-1 text-xs font-semibold text-red-500">{{ form.errors.interests }}</p>
                </div>
                <div class="my-6">
                    <label class="mb-5 text-sm font-semibold">Images: </label>

                    <div class="mx-2 mt-3 grid grid-cols-3 gap-4">
                        <span v-for="(_, index) in 6" :key="index" class="rounded-ful relative w-full">
                            <EditImageUploaded v-model="form.more_photos[index]" />
                            <p v-if="form.errors[`more_photos.${index}`]"
                                class="mt-1 text-xs font-semibold text-red-500">
                                {{ form.errors[`more_photos.${index}`] }}
                            </p>
                        </span>
                    </div>
                </div>
            </form>
            <div class="mt-6 flex justify-center">
                <LoadingButton @click="handleSubmit()" type="submit" class="mt-10 w-full text-white"
                    :loading="form.processing">Update
                </LoadingButton>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
import BaseInput from '@/components/admin/BaseInput.vue';
import FileUpload from '@/components/admin/FileUpload.vue';
import LoadingButton from '@/components/admin/LoadingButton.vue';
import Loader from '@/components/Loader.vue';
import { useCountryStateCity } from '@/composables/useCountryStateCity';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui';
import { nextTick, onMounted, ref, watch, computed } from 'vue';
import EditImageUploaded from './view/EditImageUploaded.vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const {
    countries,
    states,
    cities,
    fetchingStates,
    fetchingCities,
    isLoadingState,
    isLoadingCountry,
    isLoadingCity,
    fetchCountries,
    fetchStates,
    fetchCities,
    resetStatesAndCities,
    resetCities,
} = useCountryStateCity();

// Derived age from birthday for immediate UI feedback
const computedAge = computed<number | null>(() => {
    const b = form.birthday as string | undefined;
    if (!b) return null;
    const d = new Date(b);
    if (isNaN(d.getTime())) return null;
    const today = new Date();
    let age = today.getFullYear() - d.getFullYear();
    const m = today.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
    return age;
});

const props = defineProps<{ user: any; caribbeanIsland: any; UserMorePhotos: any }>();
const languages = ['English', 'Spanish', 'French', 'Haitian Creole', 'Dutch', 'Papiamento', 'Portuguese', 'Sranan Tongo'];
const interests = [
    // General
    { label: 'Music Festivals', icon: '🎵' },
    { label: 'Travel', icon: '🛫' },
    { label: 'Cooking', icon: '🍳' },
    { label: 'Books', icon: '📚' },
    { label: 'Yoga', icon: '🧘' },
    { label: 'Movies', icon: '🎬' },
    { label: 'Wine', icon: '🍷' },
    { label: 'Church Events', icon: '⛪' },

    // Caribbean-specific
    { label: 'Liming', icon: '🌴' }, // (means hanging out / socialising)
    { label: 'Carnival / Mas', icon: '🎭' },
    { label: 'Junkanoo', icon: '🐚' },
    { label: 'Soca Fêtes', icon: '🎶' },
    { label: 'Reggae & Dancehall', icon: '🟩' },
    { label: 'Kompa / Zouk', icon: '🇭🇹' },
    { label: 'Steel Pan', icon: '🥁' },
    { label: 'Boat Fêtes', icon: '🛥️' },
    { label: 'Beach Lime / Bonfire', icon: '🏖️' },
    { label: 'Food & Rum Festivals', icon: '🍹' },
    { label: 'Crop Over', icon: '🎉' },
    { label: 'J’ouvert', icon: '🌅' },
    { label: 'Rake & Scrape', icon: '🪘' },

    // Latin America-specific
    { label: 'Salsa Socials', icon: '🫶' },
    { label: 'Bachata Nights', icon: '💜' },
    { label: 'Reggaetón Parties', icon: '🔥' },
    { label: 'Cumbia & Vallenato', icon: '🥁' },
    { label: 'Samba Blocos / Pagode', icon: '🟡' },
    { label: 'Forró Nights', icon: '🪗' },
    { label: 'Mariachi / Regional Mexicano', icon: '🎺' },
    { label: 'Día de los Muertos (festivals)', icon: '💐' },
    { label: 'Ferias & Street Fairs', icon: '🎪' },
    { label: 'Latin Food Fairs', icon: '🍽️' },
    { label: 'Folkloric Dance Shows', icon: '🩰' },
];
// Initialize the form with user's current data
const form = useForm({
    first_name: props.user.first_name || '',
    last_name: props.user.last_name || '',
    gender: props.user.gender || '',
    birthday: props.user.birthday || '',
    email: props.user.email || '',
    phone_number: props.user.phone_number || '',
    country: props.user.country || '',
    state: props.user.state || '',
    city: props.user.city || '',
    avatar: null,
    interests: props.user.interests ?? '',
    link_me_with: props.user.link_me_with || 'female',
    age_filter: props.user.age_filter || [18, 25],
    distance_filter: props.user.distance_filter || [0, 25],
    language: props.user.language || '',
    more_photos: Array.from({ length: 6 }, (_, i) => props.UserMorePhotos?.[i] || null),
    caribbean_interest: props.user.caribbean_interest || '',
    // indices to remove on update (for cleared photo slots)
    removed_more_photos: [] as number[],
});
onMounted(() => {
    fetchCountries();
});
// keep birthday in sync if the server returns updated props after save
watch(
    () => props.user?.birthday,
    (val) => {
        if (typeof val === 'string' && val !== form.birthday) {
            form.birthday = val;
        }
    }
);
function toggleInterest(label: string) {
    console.log('interest', label);

    if (form.interests.includes(label)) {
        form.interests = form.interests.filter((i) => i !== label);
    } else {
        form.interests.push(label);
    }
}
// start fetching countries on component mount
const handleCountryChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    form.state = '';
    form.city = '';
    resetStatesAndCities();
    fetchStates(target.value);
};
nextTick(() => {
    // If the user has a country set, fetch states for that country
    if (form.country) {
        fetchStates(form.country);
    }
    if (form.state) {
        fetchCities(form.country, form.state);
    }
});

const handleStateChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    form.city = '';
    resetCities();
    fetchCities(form.country, target.value);
};

// Track which photo slots were cleared and which were re-filled
const removedPhotoIndices = ref<number[]>([]);
watch(
    () => [...form.more_photos],
    (curr, prev) => {
        for (let i = 0; i < curr.length; i++) {
            const was = prev ? prev[i] : null;
            const now = curr[i];
            // Mark as removed when transitioned from value -> null
            if (was && !now && !removedPhotoIndices.value.includes(i)) {
                removedPhotoIndices.value.push(i);
            }
            // If user selected a new file, unmark removal
            if (now && removedPhotoIndices.value.includes(i)) {
                removedPhotoIndices.value = removedPhotoIndices.value.filter((idx) => idx !== i);
            }
        }
        // keep in form so it's submitted
        form.removed_more_photos = [...removedPhotoIndices.value];
    },
    { deep: false }
);

// JavaScript
const handleSubmit = () => {
    form.transform((data: any) => ({
        ...data,
        _method: 'PUT',
        // ensure removed indices are sent even if transform strips refs
        removed_more_photos: form.removed_more_photos,
    })).post(route('frontend.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Profile updated successfully.');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};
</script>
