<template>
    <div class="w-full">
        <div
            class="group relative w-full cursor-pointer overflow-hidden rounded-xl border-2 border-dashed border-sky-300 bg-white/60 text-sky-500 transition hover:border-sky-500 hover:bg-sky-50 md:h-28 lg:h-40"
            aria-label="Upload image"
            role="button"
        >
            <!-- Preview -->
            <img
                v-if="previewUrl || props.defaultImage"
                :src="previewUrl ? previewUrl : props.defaultImage"
                alt="Preview"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
            />

            <!-- Placeholder when no preview -->
            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2">
                <svg class="h-7 w-7 md:h-8 md:w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span class="text-xs font-medium text-sky-700">Upload photo</span>
                <span class="text-[11px] text-sky-400">PNG or JPG</span>
            </div>

            <!-- Hidden input for file selection -->
            <input
                ref="fileInput"
                class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                type="file"
                @change="onFileChange"
                accept="image/*"
            />
        </div>
        <p v-if="props.error" class="mt-1 text-sm text-red-600">{{ props.error }}</p>
    </div>
</template>
<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: File,
    error: '',
    defaultImage: null,
});

watch(
    () => props.defaultImage,
    (newVal, oldval) => {
        console.log(oldval, newVal);
        previewUrl.value = props.defaultImage;
    },
);

const fileInput = ref(null);
const previewUrl = ref(null);
const fileObjectUrl = ref(null);

const onFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    emit('update:modelValue', file);
    if (fileObjectUrl.value) {
        URL.revokeObjectURL(fileObjectUrl.value);
    }
    fileObjectUrl.value = URL.createObjectURL(file);
    previewUrl.value = fileObjectUrl.value;
};

onBeforeUnmount(() => {
    if (fileObjectUrl.value) {
        URL.revokeObjectURL(fileObjectUrl.value);
    }
});
</script>
