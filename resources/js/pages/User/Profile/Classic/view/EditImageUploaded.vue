<template>
    <div class="w-full">
        <div class="group relative w-full cursor-pointer rounded-xl border-2 border-dashed border-sky-300 bg-white/60 text-sky-500 transition hover:border-sky-500 hover:bg-sky-50">
            <div class="aspect-square w-full overflow-hidden rounded-xl md:aspect-[4/3]">
                <img v-if="previewUrl" :src="previewUrl" alt="Preview"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" />
                <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2">
                    <svg class="h-8 w-8 text-sky-500 md:h-10 md:w-10" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 7a3 3 0 0 1 3-3h2l1.2-1.6A2 2 0 0 1 11.8 1h.4a2 2 0 0 1 1.6.8L15 4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <circle cx="12" cy="13" r="4.25" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    <span class="text-sm font-medium text-sky-700">Upload photo</span>
                    <span class="text-xs text-sky-400">PNG or JPG</span>
                </div>
            </div>
            <!-- Remove button (visible only when there is a preview) -->
            <button v-if="previewUrl" type="button" title="Remove image"
                class="absolute right-2 top-2 z-20 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow hover:bg-white"
                @click.stop="clearImage">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <input ref="fileInput" class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0" type="file"
                @change="onFileChange" accept="image/*" />
        </div>
        <p v-if="props.error" class="mt-1 text-sm text-red-600">{{ props.error }}</p>
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount, onMounted } from 'vue';

const emit = defineEmits(['update:modelValue']);
const props = defineProps({
    modelValue: [File, String, null],
    error: String,
    defaultImage: String,
});

const fileInput = ref(null);
const previewUrl = ref(null);
let objectUrl = null;

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    emit('update:modelValue', file);

    // revoke previous object URL
    if (objectUrl) URL.revokeObjectURL(objectUrl);

    objectUrl = URL.createObjectURL(file);
    previewUrl.value = objectUrl;
};

const clearImage = () => {
    // Revoke existing object URL if any
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }
    // Clear preview and model
    previewUrl.value = null;
    emit('update:modelValue', null);
    // Also clear the input value so the same file can be reselected
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Watch modelValue for existing URLs
watch(
    () => props.modelValue,
    (val) => {
        if (val instanceof File) {
            previewUrl.value = objectUrl || URL.createObjectURL(val);
        } else if (typeof val === 'string') {
            previewUrl.value = val.startsWith('http') ? val : '/storage/' + val;
        } else if (props.defaultImage) {
            previewUrl.value = props.defaultImage;
        }
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
});
</script>
