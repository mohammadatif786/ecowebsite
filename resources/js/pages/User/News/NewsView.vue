<script setup lang="ts">
import AppLayout from '@/layouts/admin/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, ExternalLink, Globe, Newspaper, Tag } from 'lucide-vue-next';

const props = defineProps<{
    news: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { 
        title: 'News', 
        href: route('admin.news.index') 
    },
    { 
        title: 'View', 
        href: '#' 
    },
];

function formatDate(dateString: any) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const goBack = () => {
    router.visit(route('admin.news.index'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
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

                <!-- Article Card -->
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <!-- Cover Image -->
                    <div class="relative h-64 overflow-hidden bg-gray-100 sm:h-80">
                        <img
                            v-if="props.news?.image_object"
                            :src="props.news.image_object"
                            :alt="props.news.title"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full items-center justify-center">
                            <Newspaper class="h-20 w-20 text-gray-300" />
                        </div>
                        <!-- Status Badge -->
                        <div class="absolute right-4 top-4">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium',
                                    props.news?.status == 1
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-gray-100 text-gray-600'
                                ]"
                            >
                                <span 
                                    :class="[
                                        'mr-1.5 h-2 w-2 rounded-full',
                                        props.news?.status == 1 ? 'bg-emerald-500' : 'bg-gray-400'
                                    ]"
                                ></span>
                                {{ props.news?.status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 sm:p-8">
                        <!-- Meta Info -->
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span v-if="props.news?.category" class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-sm text-violet-700">
                                <Tag class="h-3.5 w-3.5" />
                                {{ props.news.category }}
                            </span>
                            <span v-if="props.news?.country" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700">
                                <Globe class="h-3.5 w-3.5" />
                                {{ props.news.country }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
                                <Calendar class="h-3.5 w-3.5" />
                                {{ formatDate(props.news?.created_at) }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h1 class="mb-6 text-2xl font-bold text-gray-900 sm:text-3xl">
                            {{ props.news?.title || 'Untitled Article' }}
                        </h1>

                        <!-- Content Section -->
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 sm:p-6">
                            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Content / URL</h2>
                            <div class="prose max-w-none">
                                <p class="whitespace-pre-wrap text-gray-700">{{ props.news?.content || 'No content available.' }}</p>
                            </div>
                            
                            <!-- External Link -->
                            <a
                                v-if="props.news?.content && props.news.content.startsWith('http')"
                                :href="props.news.content"
                                target="_blank"
                                rel="noopener"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-violet-700"
                            >
                                <ExternalLink class="h-4 w-4" />
                                Open Article
                            </a>
                        </div>

                        <!-- Source Info -->
                        <div v-if="props.news?.source" class="mt-6 rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <h2 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-500">Source</h2>
                            <p class="text-gray-700">{{ props.news.source.type }} — {{ props.news.source.source }}</p>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-6 py-4 sm:px-8">
                        <button
                            @click="goBack"
                            class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                        >
                            Back to List
                        </button>
                        <a
                            v-if="props.news?.content && props.news.content.startsWith('http')"
                            :href="props.news.content"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Visit Source
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
