<template>
    <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Social Media
        </p>

        <label class="text-xs font-black text-slate-500">X (Twitter)</label>
        <input type="text" v-model="modelValue.twitter" placeholder="Enter X link"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">

        <label class="text-xs font-black text-slate-500">Instagram</label>
        <input type="text" v-model="modelValue.instagram" placeholder="Enter Instagram link"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">

        <label class="text-xs font-black text-slate-500">Facebook</label>
        <input type="text" v-model="modelValue.facebook" placeholder="Enter Facebook link"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">

        <label class="text-xs font-black text-slate-500">TikTok</label>
        <input type="text" v-model="modelValue.tiktok" placeholder="Enter TikTok link"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">

        <label class="text-xs font-black text-slate-500">LinkedIn</label>
        <input type="text" v-model="modelValue.linkedin" placeholder="Enter LinkedIn link"
            class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400 mt-1 mb-3">

        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in props.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

const props = defineProps<{
    modelValue: any;
    socialMedia: Array<Record<string, null>>
    errors?: string[];
}>();

const emit = defineEmits(["update:modelValue"]);

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.socialMedia });
const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};
</script>



