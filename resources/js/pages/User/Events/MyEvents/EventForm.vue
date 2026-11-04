<template>
    <main class="w-full flex-1 rounded-lg bg-gray-100 p-2 md:p-8">
        <h2 class="mb-6 text-center text-2xl font-semibold text-gray-800">Create Event</h2>
        <!-- Event Picture Upload -->
        <div class="relative mb-6 flex flex-col justify-center">
            <FileUpload v-model="form.image_file" :default-image="form.image_url" :error="form.errors.image_file" />
            <p class="mt-2 self-center text-center text-sm text-gray-500">Select Event Picture</p>
        </div>
        <!-- Form -->
        <form @submit.prevent="handleSubmit">
            <BaseInput
                name="eventTitle"
                class="dark:text-gray-500"
                label="Event Title"
                v-model="form.title"
                placeholder="Event Title"
                :error="form.errors.title"
            />
            <BaseTextarea
                class="dark:text-gray-500"
                name="description"
                label="Event Description"
                v-model="form.description"
                rows="3"
                placeholder="Event Description"
                :error="form.errors.description"
            />
            <div class="mb-4 flex w-full flex-wrap gap-4 lg:flex-nowrap">
                <BaseSelect
                    name="eventCategory"
                    label="Event Category"
                    v-model="form.category_id"
                    :options="categoryOptions"
                    placeholder="Select Event Category"
                    :error="form.errors.category_id"
                />
                <BaseSelect
                    name="eventType"
                    label="Event Type"
                    v-model="form.type"
                    :options="eventTypesOptions"
                    placeholder="Select Event Type"
                    :error="form.errors.type"
                />
            </div>
            <BaseInput
                name="eventAddress"
                class="dark:text-gray-500"
                label="Event Title"
                v-model="form.venue"
                placeholder="Event Address"
                :error="form.errors.venue"
            />
            <div class="mb-4 flex w-full flex-wrap gap-4 lg:flex-nowrap">
                <div>
                    <select
                        name="country"
                        id="country"
                        class="focus:border-primary focus:ring-primary mt-1 block w-full min-w-[70px] rounded-md border border-gray-300 px-2 py-2 dark:text-gray-400"
                        v-model="form.country"
                        @change="handleCountryChange"
                    >
                        <option value="">Select Country</option>
                        <option v-for="country in countries" :value="country.value" :key="country.value">{{ country.label }}</option>
                    </select>
                </div>
                <div>
                    <select
                        name="state"
                        id="state"
                        class="focus:border-primary focus:ring-primary mt-1 block w-full min-w-[70px] rounded-md border border-gray-300 px-2 py-2 dark:text-gray-400"
                        v-model="form.state"
                        @change="handleStateChange"
                        :disabled="!form.country || fetchingStates"
                    >
                        <option value="">Select State</option>
                        <option v-for="state in states" :value="state.value" :key="state.value">{{ state.label }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.state" />
                </div>
                <div>
                    <select
                        name="city"
                        id="city"
                        class="focus:border-primary focus:ring-primary mt-1 block w-full min-w-[70px] rounded-md border border-gray-300 px-2 py-2 dark:text-gray-400"
                        v-model="form.city"
                        :disabled="!form.state || fetchingCities"
                    >
                        <option value="">Select City</option>
                        <option v-for="city in cities" :value="city.value" :key="city.value">{{ city.label }}</option>
                    </select>
                </div>
            </div>
            <div class="mb-4 flex flex-wrap gap-4 lg:flex-nowrap">
                <div class="w-full">
                    <DatePicker
                        v-model="form.start_time"
                        placeholder="Start Date / Time"
                        :input-class="'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'"
                        :format="'yyyy-MM-dd hh:mm'"
                    />
                    <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p>
                </div>
                <div class="w-full">
                    <DatePicker v-model="form.end_time" placeholder="End Date / Time" :format="'yyyy-MM-dd hh:mm'" />
                    <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</p>
                </div>
            </div>
            <div class="mb-4 flex w-full flex-row flex-wrap gap-4 lg:flex-nowrap">
                <BaseInput
                    name="email"
                    class="w-full dark:text-gray-500"
                    label="Email"
                    v-model="form.email"
                    placeholder="Event Email"
                    :error="form.errors.email"
                />
                <BaseInput
                    name="email"
                    label="Phone"
                    v-model="form.phone"
                    placeholder="Event Phone"
                    class="w-full dark:text-gray-500"
                    :error="form.errors.phone"
                />
            </div>
            <div class="mb-4 flex w-full flex-wrap justify-between border-b-1 border-gray-300 py-2 lg:flex-nowrap">
                <label class="font-medium text-gray-500">Is Event Free</label>
                <div class="flex gap-4">
                    <RadioGroup
                        v-model="form.is_free"
                        :options="[
                            { label: 'Yes', value: true },
                            { label: 'No', value: false },
                        ]"
                        name="is_free"
                    />
                </div>
            </div>
            <template v-if="form.is_free === false">
                <div class="mb-4 flex flex-wrap gap-4 lg:flex-nowrap">
                    <BaseInput
                        name="earlyBirdPrice"
                        label="Early Bird General (Price)"
                        v-model="form.early_bird_price"
                        placeholder="Early Bird General Price"
                        class="w-full dark:text-gray-500"
                        :error="form.errors.early_bird_price"
                    />
                    <BaseInput
                        name="vipPrice"
                        label="VIP Price"
                        v-model="form.vip_price"
                        placeholder="VIP Price"
                        class="w-full dark:text-gray-500"
                        :error="form.errors.vip_price"
                    />
                </div>
            </template>
            <div class="mt-6 flex justify-center">
                <LoadingButton type="submit" class="mt-10 w-full text-white dark:text-gray-500" :loading="form.processing">SAVE </LoadingButton>
            </div>
        </form>
    </main>
</template>
<script setup lang="ts">
import LoadingButton from '@/components/admin/LoadingButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';

import RadioGroup from '@/components/admin/RadioGroup.vue';
import { computed, ref } from 'vue';
// import DatePicker from '@/components/admin/ui/datepicker';
import BaseInput from '@/components/admin/BaseInput.vue';
import BaseSelect from '@/components/admin/BaseSelect.vue';
import BaseTextarea from '@/components/admin/BaseTextarea.vue';
import DatePicker from '@/components/admin/DatePicker.vue';
import FileUpload from '@/components/admin/FileUpload.vue';
import { SelectOption } from '@/types';
import axios from 'axios';
import { onMounted } from 'vue';

const props = withDefaults(
    defineProps<{
        mode?: string;
        event: object;
    }>(),
    {
        mode: 'create',
    },
);

// Reactive form state
const form = useForm<any>(props.event);
const showSpinner = ref(false);

const categoryOptions = computed(() => {
    const categories = usePage().props.categories;
    return categories.map((item) => ({
        value: item.id,
        label: item.name,
    }));
});
const eventTypesOptions = computed(() => {
    const eventTypes = ['Public', 'Private'];
    return eventTypes.map((type) => ({
        value: type,
        label: type,
    }));
});
const fetchingStates = ref(false);
const fetchingCities = ref(false);
const countries = ref<Array<SelectOption>>([]);
const states = ref<Array<SelectOption>>([]);
const cities = ref<Array<SelectOption>>([]);

const handleCountryChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    form.state = '';
    form.city = '';
    states.value = [];
    cities.value = [];
    fetchStates(target.value);
};
const handleStateChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    fetchCities(form.country, target.value);
};

const fetchCountries = () => {
    axios.get('https://countriesnow.space/api/v0.1/countries/capital').then((response) => {
        const list = response.data.data;
        countries.value = list.map((country: any) => ({
            label: country.name,
            value: country.name,
        }));
    });
};

const fetchStates = (countryName: string | undefined) => {
    if (!countryName) return;

    fetchingStates.value = true;
    axios
        .post('https://countriesnow.space/api/v0.1/countries/states', {
            country: countryName,
        })
        .then((response) => {
            const list = response.data.data.states || [];
            states.value = list.map((state: any) => ({
                label: state.name,
                value: state.name,
            }));
        })
        .finally(() => {
            fetchingStates.value = false;
            if (countryName === 'Bahamas') {
                const alreadyExists = states.value.some((s) => s.value === 'New Providence');
                if (!alreadyExists) {
                    states.value.push({
                        label: 'New Providence',
                        value: 'New Providence',
                    });
                }
            }
        });
};

const fetchCities = (countryName: string | undefined, stateName: string | undefined) => {
    if (!countryName || !stateName) return;

    fetchingCities.value = true;
    if (countryName === 'Bahamas' && stateName === 'New Providence') {
        cities.value = [{ label: 'Nassau', value: 'Nassau' }];
    }
    axios
        .post('https://countriesnow.space/api/v0.1/countries/state/cities', {
            country: countryName,
            state: stateName,
        })
        .then((response) => {
            const list = response.data.data || [];
            cities.value = list.map((city: any) => ({
                label: city,
                value: city,
            }));
        })
        .finally(() => {
            fetchingCities.value = false;
        });
};

onMounted(() => {
    fetchCountries();
    if (props.event.id > 0 && form.country) {
        fetchStates(form.country);
    }
    if (props.event.id > 0 && form.country && form.state) {
        fetchCities(form.country, form.state);
    }
});

// Handle form submission
const handleSubmit = () => {
    if (form.id > 0) {
        form.transform((data: any) => ({
            ...data,
            _method: 'PUT',
        })).post(route('frontend.myevents.update', form.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('frontend.myevents.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
    // console.log(form);
};
</script>
<style scoped></style>
