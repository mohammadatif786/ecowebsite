<template>
    <AuthenticatedLayout>
        <main class="w-full flex-1 rounded-lg bg-gray-100 p-2 md:p-8">
            <p class="py-8 text-center text-3xl font-bold">
                {{ app_setting[0].key }}
            </p>

            <!-- QuillEditor in read-only mode -->
            <QuillEditor v-model:content="app_setting[0].value" contentType="html" theme="bubble" readOnly />
        </main>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.bubble.css';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const { app_setting } = defineProps<{
    app_setting: { key: string; value: string }[];
}>();
</script>
