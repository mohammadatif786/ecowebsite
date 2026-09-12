<template>
    <main class="flex-1 bg-gray-100 p-2 md:p-8 rounded-lg w-full">
        <h2 class="text-2xl font-semibold text-center mb-6 text-gray-800">Create Event</h2>
        <!-- Event Picture Upload -->
        <div class="relative mb-6 flex flex-col justify-center">
            <FileUpload v-model="form.image_file" :default-image="form.featured_image"
                :error="form.errors.image_file" />
            <p class="text-center mt-2 text-sm text-gray-500 self-center">Select Event Picture</p>
        </div>
        <!-- Form -->
        <form @submit.prevent="handleSubmit">
            <BaseInput name="eventTitle" class="dark:text-gray-500" label="Event Title" v-model="form.title"
                placeholder="Event Title" :error="form.errors.title" />
            <BaseTextarea class="dark:text-gray-500" name="description" label="Event Description"
                v-model="form.description" rows="3" placeholder="Event Description" :error="form.errors.description" />
            <div class="flex flex-wrap lg:flex-nowrap gap-4 mb-4 w-full">
                <BaseSelect name="eventCategory" label="Event Category" v-model="form.category_id"
                    :options="categoryOptions" placeholder="Select Event Category" :error="form.errors.category_id" />
                <BaseSelect name="eventType" label="Event Type" v-model="form.type" :options="eventTypesOptions"
                    placeholder="Select Event Type" :error="form.errors.type" />
            </div>
            <BaseInput name="eventAddress" class="dark:text-gray-500" label="Event Title" v-model="form.venue"
                placeholder="Event Address" :error="form.errors.venue" />
            <div class="flex flex-wrap lg:flex-nowrap gap-4 mb-4">
                <BaseSelect name="country" label="Country" v-model="form.country" :options="countryOptions"
                    placeholder="Select Country" :error="form.errors.country" />
                <BaseSelect name="state" label="State" v-model="form.state" :options="stateOptions"
                    placeholder="Select State" :error="form.errors.state" />
                <BaseSelect name="city" label="City" v-model="form.city" :options="cityOptions"
                    placeholder="Select City" :error="form.errors.city" />
            </div>
            <div class="flex flex-wrap lg:flex-nowrap gap-4 mb-4">
                <div class="w-full">
                    <DatePicker v-model="form.start_time" placeholder="Start Date / Time"
                        :input-class="'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'"
                        :format="'yyyy-MM-dd hh:mm'" />
                    <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p>
                </div>
                <div class="w-full">
                    <DatePicker v-model="form.end_time" placeholder="End Date / Time" :format="'yyyy-MM-dd hh:mm'" />
                    <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</p>
                </div>
            </div>
            <div class="flex flex-row flex-wrap lg:flex-nowrap gap-4 mb-4 w-full">
                <BaseInput name="email" class="dark:text-gray-500 w-full" label="Email" v-model="form.email"
                    placeholder="Event Email" :error="form.errors.email" />
                <BaseInput name="email" label="Phone" v-model="form.phone" placeholder="Event Phone"
                    class="w-full dark:text-gray-500" :error="form.errors.phone" />
            </div>
            <div class="flex flex-wrap lg:flex-nowrap justify-between border-b-1 border-gray-300 w-full mb-4 py-2">
                <label class="font-medium text-gray-500">Is Event Free</label>
                <div class="flex gap-4">
                    <RadioGroup v-model="form.is_free" :options="[
                        { label: 'Yes', value: true },
                        { label: 'No', value: false },
                    ]" name="is_free" />
                </div>
            </div>
            <template v-if="form.is_free === false">
                <div class="flex flex-wrap lg:flex-nowrap gap-4 mb-4">
                    <BaseInput name="earlyBirdPrice" label="Early Bird General (Price)" v-model="form.early_bird_price"
                        placeholder="Early Bird General Price" class="w-full dark:text-gray-500"
                        :error="form.errors.early_bird_price" />
                    <BaseInput name="vipPrice" label="VIP Price" v-model="form.vip_price" placeholder="VIP Price"
                        class="w-full dark:text-gray-500" :error="form.errors.vip_price" />
                </div>
            </template>
            <div class="flex justify-center mt-6">
                <LoadingButton type="submit" class="w-full mt-10 text-white dark:text-gray-500"
                    :loading="form.processing">SAVE </LoadingButton>
            </div>
        </form>
    </main>
</template>
<script setup lang="ts">
    import { Head, useForm, usePage } from '@inertiajs/vue3';
    import LoadingButton from "@/components/admin/LoadingButton.vue";

    import { computed, ref } from 'vue';
    import RadioGroup from "@/components/admin/RadioGroup.vue";
    // import DatePicker from '@/components/admin/ui/datepicker';
    import BaseInput from '@/components/admin/BaseInput.vue'
    import BaseTextarea from '@/components/admin/BaseTextarea.vue';
    import BaseSelect from "@/components/admin/BaseSelect.vue";
    import FileUpload from '@/components/admin/FileUpload.vue';
    import DatePicker from '@/components/admin/DatePicker.vue';

    const props = withDefaults(defineProps<{
        mode?: string
        event: object
    }>(), {
        mode: 'create'
    });


    // Reactive form state
    const form = useForm<any>(props.event);
    const showSpinner = ref(false);


    const categoryOptions = computed(() => {
        const categories = usePage().props.categories;
        return categories.map(item => ({
            value: item.id,
            label: item.name
        }));
    });
    const eventTypesOptions = computed(() => {
        const eventTypes = ['Public', 'Private', 'Hybrid'];
        return eventTypes.map(type => ({
            value: type,
            label: type
        }));
    });
    const countryOptions = computed(() => {
        const eventTypes = ['USA', 'Canada', 'UK'];
        return eventTypes.map(type => ({
            value: type,
            label: type
        }));
    });
    const stateOptions = computed(() => {
        const eventTypes = ['California', 'Texas', 'Florida'];
        return eventTypes.map(type => ({
            value: type,
            label: type
        }));
    });
    const cityOptions = computed(() => {
        const eventTypes = ['Los Angeles', 'Austin', 'Miami'];
        return eventTypes.map(type => ({
            value: type,
            label: type
        }));
    });


    // Handle form submission
    const handleSubmit = () => {
        if (form.id > 0) {
            form.transform((data: any) => ({
                ...data,
                _method: 'PUT',
            })).post(route('frontend.event.update', form.id), {
                preserveScroll: true
            });
        } else {
            form.post(route('frontend.event.store'), {
                preserveScroll: true,
                onSuccess: () => form.reset()
            });
        }
        // console.log(form);
    };
</script>
<style scoped></style>
