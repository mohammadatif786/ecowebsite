<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Event Details
        </p>

        <!-- Event Name -->
        <label class="text-sm font-bold text-slate-700">Event Name *</label>
        <input type="text" v-model="form.name" placeholder="Event name"
            class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
            :class="props.allErrors?.['eventDetails.name'] ? 'border-rose-500' : 'border-slate-200'" />
        <p v-if="props.allErrors?.['eventDetails.name']" class="text-xs text-rose-500 font-bold mb-3 ml-1">
            {{ props.allErrors['eventDetails.name'] }}
        </p>

        <!-- Description -->
        <label class="text-sm font-bold text-slate-700">Description *</label>
        <textarea rows="3" v-model="form.description" placeholder="Describe your event"
            class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
            :class="props.allErrors?.['eventDetails.description'] ? 'border-rose-500' : 'border-slate-200'"></textarea>
        <p v-if="props.allErrors?.['eventDetails.description']" class="text-xs text-rose-500 font-bold mb-3 ml-1">
            {{ props.allErrors['eventDetails.description'] }}
        </p>

        <!-- Category -->
        <div v-show="!isCookoutOrganizer">
            <label class="text-sm font-bold text-slate-700">Category *</label>
            <select v-model="form.category_id"
                class="w-full border rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-1"
                :class="props.allErrors?.['eventDetails.category_id'] ? 'border-rose-500' : 'border-slate-200'">
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </select>
            <p v-if="props.allErrors?.['eventDetails.category_id']" class="text-xs text-rose-500 font-bold mb-3 ml-1">
                {{ props.allErrors['eventDetails.category_id'] }}
            </p>
        </div>

        <!-- Cookout auto-category -->
        <div v-if="isCookoutOrganizer" class="mb-3">
            <p class="text-xs font-black text-slate-500">Category</p>
            <p class="text-[11px] text-slate-400 mb-1">Automatically set for cookout organizers.</p>
            <div class="px-3 py-2 rounded-2xl font-black text-sm"
                style="background:rgba(14,165,233,.10); border:1px solid rgba(14,165,233,.30);">
                Cookout / Plate Sale
            </div>
        </div>

        <!-- Audiences -->
        <label class="text-sm font-bold text-slate-700">Audiences *</label>
        <p class="text-[11px] text-slate-400 mb-2">Select the audience types that are targeted in your event.</p>
        <div class="flex flex-wrap gap-2 mb-1">
            <button v-for="aud in ['Adults','Children','Family','Youth','Group']" :key="aud"
                type="button"
                @click="toggleAudience(aud)"
                :class="form.audiences.includes(aud)
                    ? 'border-blue-500 text-blue-700 bg-blue-50'
                    : (props.allErrors?.['eventDetails.audiences'] ? 'border-rose-500 text-rose-500' : 'border-slate-200 text-slate-500')"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                {{ form.audiences.includes(aud) ? '✓ ' : '' }}{{ aud }}
            </button>
        </div>
        <p v-if="props.allErrors?.['eventDetails.audiences']" class="text-xs text-rose-500 font-bold mb-3 ml-1">
            {{ props.allErrors['eventDetails.audiences'] }}
        </p>

        <!-- Attendees -->
        <label class="text-xs font-black text-slate-500">Attendees *</label>
        <div class="rounded-xl bg-blue-50 border border-blue-100 p-2.5 text-[11px] text-blue-700 font-bold mt-1 mb-2">
            Show the attendees number and list on the event page
        </div>
        <div class="flex gap-2 mb-3">
            <button v-for="opt in [['show','Show'],['hide','Hide']]" :key="opt[0]"
                type="button"
                @click="form.attendees = opt[0]"
                :class="form.attendees === opt[0]
                    ? 'border-blue-500 text-blue-700 bg-blue-50'
                    : 'border-slate-200 text-slate-500'"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                {{ opt[1] }}
            </button>
        </div>

        <!-- Enable Reviews -->
        <label class="text-xs font-black text-slate-500">Enable reviews *</label>
        <div class="flex gap-2 mt-1 mb-3">
            <button v-for="opt in [['enable','Enable'],['disable','Disable']]" :key="opt[0]"
                type="button"
                @click="form.reviews = opt[0]"
                :class="form.reviews === opt[0]
                    ? 'border-blue-500 text-blue-700 bg-blue-50'
                    : 'border-slate-200 text-slate-500'"
                class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                {{ opt[1] }}
            </button>
        </div>

        <!-- Seating Plan -->
        <div v-show="!isCookoutOrganizer">
            <label class="text-xs font-black text-slate-500">Does this event have a seating plan? *</label>
            <div class="flex gap-2 mt-1 mb-1">
                <button v-for="opt in [['no','No'],['yes','Yes']]" :key="opt[0]"
                    type="button"
                    @click="form.seating_plan = opt[0]"
                    :class="form.seating_plan === opt[0]
                        ? 'border-blue-500 text-blue-700 bg-blue-50'
                        : 'border-slate-200 text-slate-500'"
                    class="rounded-xl border-2 px-4 py-2 text-sm font-bold">
                    {{ opt[1] }}
                </button>
            </div>
        </div>

        <!-- Errors -->
        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in props.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>
<script setup lang="ts">
import { ref, reactive, watch, computed } from "vue";
import { usePage } from '@inertiajs/vue3';

// Define emit
const emit = defineEmits<{
    'update:isCookout': [value: boolean],
    'update:isWellness': [value: boolean]
}>()

const props = defineProps<{
    categories: Array<{ id: number; name: string }>
    errors?: string[];
    allErrors?: Record<string, string>;
    eventDetails: Array<Record<string, null>>
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

const toggleAudience = (aud: string) => {
    const idx = form.audiences.indexOf(aud);
    if (idx > -1) {
        form.audiences.splice(idx, 1);
    } else {
        form.audiences.push(aud);
    }
};

// Check if organizer is a cookout organizer
const page = usePage();
const user = (page.props as any).auth?.user;
const isCookoutOrganizer = computed(() => {
    const organizerProfile = user?.organizer_profile;
    if (!organizerProfile || !organizerProfile.categories) {
        return false;
    }
    return organizerProfile.categories.length === 1 && (organizerProfile.categories[0] === 'cookouts/Food' || organizerProfile.categories[0] === 'cookouts' || organizerProfile.categories[0] === 'Cookout / Plate Sale');
});

const isWellnessOrganizer = computed(() => {
    const organizerProfile = user?.organizer_profile;
    if (!organizerProfile || !organizerProfile.categories) {
        return false;
    }
    return organizerProfile.categories.length === 1 && (organizerProfile.categories[0] === 'Wellness and Spa' || organizerProfile.categories[0].toLowerCase().includes('wellness'));
});

// Watch for cookout status changes and emit to parent
watch(isCookoutOrganizer, (newValue) => {
    emit('update:isCookout', newValue);

    // Automatically set cookout category if organizer is cookout
    if (newValue && props.categories) {
        const cookoutCategory = props.categories.find(cat =>
            cat.name.toLowerCase().includes('cookout')
        );
        if (cookoutCategory) {
            form.category_id = cookoutCategory.id;
        }
    }
}, { immediate: true });

watch(isWellnessOrganizer, (newValue) => {
    emit('update:isWellness', newValue);

    // Automatically set wellness category if organizer is wellness
    if (newValue && props.categories) {
        const wellnessCategory = props.categories.find(cat =>
            cat.name.toLowerCase().includes('wellness')
        );
        if (wellnessCategory) {
            form.category_id = wellnessCategory.id;
        }
    }
}, { immediate: true });

</script>

