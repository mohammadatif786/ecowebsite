<script setup lang="ts">
import type { News } from '@/client';
import InputError from '@/components/admin/InputError.vue';
import AppLayout from '@/layouts/admin/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Image, Newspaper, Save, Upload } from 'lucide-vue-next';
import { ref } from 'vue';
import DefaultImage from '../../../../assets/images/default-image2.png';

const props = defineProps<{
    news: News;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: route('admin.news.index'),
    },
    {
        title: props.news?.id ? 'Edit' : 'Create',
        href: '#',
    },
];

const form = useForm<News>({
    title: props.news?.title || '',
    content: props.news?.content || '',
    status: props.news?.status || 0,
    image_object: props.news?.image_object || null,
    _method: props.news?.id ? 'PUT' : 'POST',
});

const previewImage = ref<string | null>(props.news?.image_object || null);

const handleImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const newFile = input.files[0];
        form.image_object = newFile;
        previewImage.value = URL.createObjectURL(newFile);
    }
};

const triggerFileInput = () => {
    document.getElementById('image-input')?.click();
};

const submit = () => {
    if (props.news) {
        form.post(route('admin.news.update', props.news.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('admin.news.index'));
            },
        });
    } else {
        form.post(route('admin.news.store'), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('admin.news.index'));
            },
        });
    }
};

const goBack = () => {
    router.visit(route('admin.news.index'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="news ? 'Edit News' : 'Create News'" />
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-4xl">
                <!-- Back Button -->
                <button
                    @click="goBack"
                    class="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-gray-700"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to News
                </button>

                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ news ? 'Edit News Article' : 'Create News Article' }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ news ? 'Update the article information below' : 'Fill in the information below to create a new article' }}
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <!-- Main Content -->
                        <div class="space-y-6 lg:col-span-2">
                            <!-- Title Card -->
                            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                                    <h2 class="font-semibold text-gray-900">Article Details</h2>
                                </div>
                                <div class="space-y-4 p-6">
                                    <div>
                                        <label for="title" class="mb-2 block text-sm font-medium text-gray-700">Title</label>
                                        <input
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            placeholder="Enter article title"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"
                                        />
                                        <InputError class="mt-1" :message="form.errors.title" />
                                    </div>
                                    <div>
                                        <label for="content" class="mb-2 block text-sm font-medium text-gray-700">Content</label>
                                        <textarea
                                            id="content"
                                            v-model="form.content"
                                            rows="10"
                                            placeholder="Enter article content or URL"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"
                                        ></textarea>
                                        <InputError class="mt-1" :message="form.errors.content" />
                                    </div>
                                </div>
                            </div>

                            <!-- Status Card -->
                            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                                    <h2 class="font-semibold text-gray-900">Status</h2>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-2 gap-4">
                                        <button
                                            type="button"
                                            @click="form.status = 1"
                                            :class="[
                                                'flex items-center gap-3 rounded-lg border-2 p-4 transition-all',
                                                form.status == 1
                                                    ? 'border-emerald-500 bg-emerald-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            ]"
                                        >
                                            <div :class="[
                                                'flex h-10 w-10 items-center justify-center rounded-full',
                                                form.status == 1 ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-400'
                                            ]">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <p :class="['font-medium', form.status == 1 ? 'text-emerald-700' : 'text-gray-700']">Active</p>
                                                <p class="text-xs text-gray-500">Visible to users</p>
                                            </div>
                                        </button>
                                        <button
                                            type="button"
                                            @click="form.status = 0"
                                            :class="[
                                                'flex items-center gap-3 rounded-lg border-2 p-4 transition-all',
                                                form.status == 0
                                                    ? 'border-gray-500 bg-gray-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            ]"
                                        >
                                            <div :class="[
                                                'flex h-10 w-10 items-center justify-center rounded-full',
                                                form.status == 0 ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-400'
                                            ]">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <p :class="['font-medium', form.status == 0 ? 'text-gray-700' : 'text-gray-700']">Inactive</p>
                                                <p class="text-xs text-gray-500">Hidden from users</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar - Image -->
                        <div class="lg:col-span-1">
                            <div class="sticky top-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                                    <h2 class="font-semibold text-gray-900">Cover Image</h2>
                                </div>
                                <div class="p-6">
                                    <!-- Image Preview -->
                                    <div
                                        @click="triggerFileInput"
                                        class="group relative cursor-pointer overflow-hidden rounded-lg border-2 border-dashed border-gray-200 transition-colors hover:border-violet-400"
                                    >
                                        <div class="aspect-video">
                                            <img
                                                v-if="previewImage"
                                                :src="previewImage"
                                                alt="Cover image"
                                                class="h-full w-full object-cover"
                                            />
                                            <img
                                                v-else-if="form.image_object && typeof form.image_object === 'string'"
                                                :src="form.image_object"
                                                alt="Cover image"
                                                class="h-full w-full object-cover"
                                            />
                                            <div v-else class="flex h-full flex-col items-center justify-center bg-gray-50 p-4">
                                                <Image class="h-10 w-10 text-gray-300" />
                                                <p class="mt-2 text-center text-sm text-gray-500">Click to upload image</p>
                                            </div>
                                        </div>
                                        <!-- Hover Overlay -->
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                            <div class="flex flex-col items-center text-white">
                                                <Upload class="h-8 w-8" />
                                                <p class="mt-1 text-sm">Change Image</p>
                                            </div>
                                        </div>
                                    </div>
                                    <input
                                        id="image-input"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleImageChange"
                                    />
                                    <InputError class="mt-2" :message="form.errors.image" />
                                    <p class="mt-3 text-center text-xs text-gray-500">
                                        Recommended: 16:9 aspect ratio
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                        <button
                            type="button"
                            @click="goBack"
                            class="rounded-lg border border-gray-200 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-violet-700 disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <Save v-else class="h-4 w-4" />
                            {{ news ? 'Update Article' : 'Create Article' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
