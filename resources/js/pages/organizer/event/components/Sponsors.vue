<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Sponsors
        </p>

        <label class="text-xs font-black text-slate-500">Selected Event *</label>
        <select v-model="form.event_id"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">
            <option value="">Select Event</option>
            <option v-for="event in allEvent" :key="event?.id" :value="event?.id">
                {{ event?.title }}
            </option>
        </select>

        <label class="text-xs font-black text-slate-500">Name *</label>
        <input type="text" v-model="form.name"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3" />

        <label class="text-xs font-black text-slate-500">Description</label>
        <textarea rows="3" v-model="form.description"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3"></textarea>

        <label class="text-xs font-black text-slate-500">Status *</label>
        <select v-model="form.status"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

        <div class="mb-3">
            <label class="text-xs font-black text-slate-500">Sponsor Image</label>
            <input type="file" @change="handleFileUpload" accept="image/*" class="w-full mt-1 mb-2 text-sm" />
            <div v-if="imagePreview">
                <img :src="`${imagePreview}`" alt="Preview" class="h-32 w-auto rounded-xl border border-slate-200 object-cover" />
            </div>
        </div>

        <div v-if="form.errors" class="text-red-500 text-sm mt-2 mb-3">
            <span v-if="typeof form.errors === 'string'">{{ form.errors }}</span>
            <ul v-else class="list-disc pl-4">
                <li v-for="(err, idx) in form.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>

        <button type="button" @click="saveSponsor"
            class="px-6 py-2 rounded-2xl font-black text-white text-sm transition hover:scale-[1.02]"
            style="background: linear-gradient(90deg,#2f9bef,#f59e0b)">
            {{ props.sponsor == null ? 'Create' : 'Update' }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";

import { useForm } from '@inertiajs/vue3';
import { toast } from "vue-sonner";
// Props
const props = defineProps<{
    events: Array<{ id: number; title: string }>;
    sponsor: {
        link_up_event_id?: number;
        name?: string;
        description?: string;
        status?: string;
        image_object?: string;
    } | null;
    appURL: string;
    allEvent: Array<{ id: number; title: string }>;
    edit: string | boolean;
}>()
const isEdit = computed(() => props.edit === true || props.edit === "true");

// State
const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.sponsor })
const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index]
}

const form = useForm({
    event_id: props.sponsor?.link_up_event_id ?? '',
    name: props.sponsor?.name ?? '',
    description: props.sponsor?.description ?? '',
    status: props.sponsor?.status == true ? 'active' : 'inactive',
    image: props.sponsor?.image_object ?? null as File | null,
})

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        form.image = target.files[0]
    }
}
const saveSponsor = () => {
    if (isEdit.value == true && props.sponsor) {
        form.post(route('organizer.event.sponsor.update', { sponsor_id: props.sponsor?.id }), {
            forceFormData: true,
            onSuccess: () => {
                toast.success("Sponsor Updated successfully!")
            },
            onError: (errors) => {
                console.error("Form submission errors:", errors)
                toast.error("Failed to save sponsor.")
            },
        })

    } else {
        form.post(route('organizer.event.sponsor.store'), {
            forceFormData: true, // ensures file upload
            onSuccess: () => {
                toast.success("Sponsor saved successfully!")
            },
            onError: (errors) => {
                console.error("Form submission errors:", errors)
                toast.error("Failed to save sponsor.")
            },
        })
    }
}


const imagePreview = computed(() => {
    if (form.image instanceof File) {
        return URL.createObjectURL(form.image);
    } else if (props.sponsor?.image_object) {
        return `${props.appURL}${props.sponsor.image_object}`;
    }
    return null;
});
</script>



