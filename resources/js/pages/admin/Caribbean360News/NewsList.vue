<script setup lang="ts">
import { computed, ref } from 'vue';

const emit = defineEmits<{
    (event: 'view-changed', id: string): void;
}>();

type Status = 'Published' | 'Review' | 'Scheduled' | 'Draft';

type ListRow = {
    title: string;
    countryRegion: string;
    category: string;
    author: string;
    status: Status;
    views: number;
    published: string;
};

const rows: ListRow[] = [
    { title: 'Bahamas Independence Weekend Events', countryRegion: 'Bahamas', category: 'Events', author: 'LinkUp News Desk', status: 'Published', views: 86420, published: 'Jun 01, 2026' },
    { title: 'Jamaica Carnival Travel Guide', countryRegion: 'Jamaica', category: 'Travel', author: 'Marsha Clarke', status: 'Published', views: 74850, published: 'May 30, 2026' },
    { title: 'Caribbean Startups to Watch', countryRegion: 'Regional', category: 'Business', author: 'Andre Rolle', status: 'Review', views: 63190, published: '—' },
    { title: 'Trinidad Soca Weekend Preview', countryRegion: 'Trinidad & Tobago', category: 'Entertainment', author: 'Sasha Baptiste', status: 'Scheduled', views: 51880, published: 'Jun 08, 2026' },
    { title: 'Barbados Crop Over Road March Watch', countryRegion: 'Barbados', category: 'Culture', author: 'Nia Browne', status: 'Published', views: 44200, published: 'May 27, 2026' },
    { title: 'Guyana Energy Corridor Update', countryRegion: 'Guyana', category: 'Business', author: 'Ramon Persaud', status: 'Published', views: 39910, published: 'May 25, 2026' },
    { title: 'Dominican Republic Summer Travel Deals', countryRegion: 'Dominican Republic', category: 'Travel', author: 'Lucia Mendez', status: 'Draft', views: 0, published: '—' },
    { title: 'Miami Caribbean Food Fest Announced', countryRegion: 'United States Diaspora', category: 'Events', author: 'Carib Diaspora Desk', status: 'Scheduled', views: 18100, published: 'Jun 10, 2026' },
];

const statusStyle: Record<Status, string> = {
    Published: 'bg-green-50 text-green-700',
    Review: 'bg-amber-50 text-amber-700',
    Scheduled: 'bg-blue-50 text-blue-700',
    Draft: 'bg-slate-100 text-slate-700',
};

const search = ref('');

const filteredRows = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return rows;
    return rows.filter((row) => [row.title, row.countryRegion, row.category, row.author, row.status].join(' ').toLowerCase().includes(q));
});

const num = (value: number) => value.toLocaleString();
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black">News List</h3>
                <p class="text-slate-500">All Caribbean 360 stories across countries, categories, authors, and publishing status.</p>
            </div>
            <input v-model="search" class="w-full rounded-2xl border border-slate-200 px-4 py-3 xl:w-96" placeholder="Search story, country, category, author..." />
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-4">Title</th>
                            <th>Country / Region</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in filteredRows" :key="row.title" class="border-t border-slate-100">
                            <td class="p-4 font-bold">{{ row.title }}</td>
                            <td>{{ row.countryRegion }}</td>
                            <td>{{ row.category }}</td>
                            <td>{{ row.author }}</td>
                            <td>
                                <span class="rounded-full px-3 py-1 text-xs font-black" :class="statusStyle[row.status]">{{ row.status }}</span>
                            </td>
                            <td>{{ num(row.views) }}</td>
                            <td>{{ row.published }}</td>
                            <td>
                                <button
                                    v-if="row.status !== 'Draft'"
                                    class="font-bold text-purple-600"
                                    @click="emit('view-changed', 'newsCreateCommand')"
                                >
                                    {{ row.status === 'Review' ? 'Review' : 'View' }}
                                </button>
                                <span v-if="row.status !== 'Draft'"> • </span>
                                <button class="font-bold text-purple-600" @click="emit('view-changed', 'newsCreateCommand')">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4 text-sm text-slate-500">
                Showing {{ filteredRows.length }} of {{ rows.length }} sample stories. These rows show the developer what fields belong in the news database.
            </div>
        </div>
    </div>
</template>
