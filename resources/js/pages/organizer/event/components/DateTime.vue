<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Date and Time
        </p>

        <!-- Event Type -->
        <p class="text-xs font-black text-slate-500 mb-2">Type of event</p>
        <div class="grid grid-cols-2 gap-2 mb-3">
            <button type="button" @click="form.eventType = 'single'"
                class="rounded-xl border-2 p-3 text-left transition"
                :class="form.eventType === 'single' ? 'border-blue-500 bg-blue-50' : 'border-slate-200'">
                <p class="font-black text-sm">📅 Single event</p>
                <p class="text-[11px] text-slate-500">For events that happen once</p>
            </button>
            <button type="button" @click="form.eventType = 'recurring'"
                class="rounded-xl border-2 p-3 text-left transition"
                :class="form.eventType === 'recurring' ? 'border-blue-500 bg-blue-50' : 'border-slate-200'">
                <p class="font-black text-sm">📅 Recurring event</p>
                <p class="text-[11px] text-slate-500">For timed entry and multiple days</p>
            </button>
        </div>

        <!-- Recurring Options -->
        <template v-if="form.eventType === 'recurring'">
            <label class="text-xs font-black text-slate-500">Recurrence Pattern *</label>
            <select v-model="form.recurrPattern"
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-black text-slate-500">Start Date *</label>
                    <input type="date" v-model="form.recurrStartDate"
                        class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
                </div>
                <div>
                    <label class="text-xs font-black text-slate-500">End Date *</label>
                    <input type="date" v-model="form.recurrEndDate"
                        class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
                </div>
            </div>
        </template>

        <!-- Single Event Options -->
        <template v-else>
            <label class="text-xs font-black text-slate-500">Event Date *</label>
            <input type="date" v-model="form.singleDate"
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3" />
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-black text-slate-500">Start time</label>
                    <input type="time" v-model="form.singleStartTime"
                        class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
                </div>
                <div>
                    <label class="text-xs font-black text-slate-500">End time</label>
                    <input type="time" v-model="form.singleEndTime"
                        class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1" />
                </div>
            </div>
        </template>

        <!-- Errors -->
        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in props.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, watchEffect } from "vue";

const props = defineProps<{
    dateTime?: Record<string, any>; // make it flexible
    errors?: string[];
    eventDetails: {event_type: string, single_event: string, single_event_date: string, single_start_time:string, single_end_time:string
        recurr_pattern: string, recurr_start_date:string, recurr_end_date:string
    }
}>();

// Step 1: define model with neutral defaults
const modelValue = defineModel<{
    eventType: string;
    singleDate: string | null;
    singleStartTime: string | null;
    singleEndTime: string | null;
    recurrPattern: string | null;
    recurrStartDate: string | null;
    recurrEndDate: string | null;
}>({
    default: {
        eventType: "single",
        singleDate: null,
        singleStartTime: null,
        singleEndTime: null,
        recurrPattern: null,
        recurrStartDate: null,
        recurrEndDate: null,
    },
});
// Step 2: local form state
const form = reactive(modelValue.value);

// Step 3: hydrate from props when available
watchEffect(() => {
    if (props.dateTime) {
        form.eventType = props.eventDetails?.event_type ?? "single";

        // Format date values (YYYY-MM-DD)
        form.singleDate = props.eventDetails?.single_event_date
            ? props.eventDetails?.single_event_date.split("T")[0]
            : null;

        // Times are already good (HH:mm)
        form.singleStartTime = props.eventDetails?.single_start_time ?? null;
        form.singleEndTime = props.eventDetails?.single_end_time ?? null;

        form.recurrPattern = props.eventDetails?.recurr_pattern ?? null;

        form.recurrStartDate = props.eventDetails?.recurr_start_date
            ? props.eventDetails?.recurr_start_date.split("T")[0]
            : null;

        form.recurrEndDate = props.eventDetails?.recurr_end_date
            ? props.eventDetails?.recurr_end_date.split("T")[0]
            : null;
    }
});

// Step 4: sync back up to v-model
watch(
    form,
    (val) => {
        modelValue.value = val;
    },
    { deep: true }
);

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.dateTime });

const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};
</script>


