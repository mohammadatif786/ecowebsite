<template>
    <Head :title="title" />
    <LegalLayout>
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-[#17365D]">{{ title }}</h1>
            <p v-if="updated_at" class="text-center mt-2 text-sm text-slate-500">Last Updated: {{ updated_at }}</p>
        </div>

        <div 
            class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#17365D] prose-headings:font-bold" 
            style="white-space: pre-wrap;"
            v-html="content"
        >
        </div>
    </LegalLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/layouts/LegalLayout.vue';

defineProps({
    title: {
        type: String,
        required: true
    },
    content: {
        type: String,
        default: ''
    },
    updated_at: {
        type: String,
        default: null
    }
});
</script>
