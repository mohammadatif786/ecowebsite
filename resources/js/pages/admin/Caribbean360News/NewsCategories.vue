<script setup lang="ts">
import { ref } from 'vue';
import { Plus, Trash2, X } from 'lucide-vue-next';

type Category = {
    key: string;
    label: string;
    stories: number;
    views: number;
};

const categories = ref<Category[]>([
    { key: 'Breaking News', label: 'Breaking News', stories: 18, views: 225000 },
    { key: 'Politics', label: 'Politics', stories: 34, views: 198000 },
    { key: 'Business & Economy', label: 'Business', stories: 39, views: 201000 },
    { key: 'Entertainment', label: 'Entertainment', stories: 47, views: 292000 },
    { key: 'Sports', label: 'Sports', stories: 24, views: 118000 },
    { key: 'Culture', label: 'Culture', stories: 28, views: 144000 },
    { key: 'Technology', label: 'Technology', stories: 21, views: 132000 },
    { key: 'Faith', label: 'Faith', stories: 14, views: 78000 },
    { key: 'Events', label: 'Events', stories: 52, views: 312000 },
    { key: 'Travel', label: 'Travel', stories: 43, views: 248000 },
]);

const showModal = ref(false);
const form = ref({ name: '', stories: 0, views: 0 });

const num = (value: number) => Math.round(value || 0).toLocaleString();
const kfmt = (value: number) => (value >= 1000 ? `${(value / 1000).toFixed(0)}K` : num(value));

const openModal = () => {
    form.value = { name: '', stories: 0, views: 0 };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveCategory = () => {
    const name = form.value.name.trim();
    if (!name) {
        window.alert('Enter a category name.');
        return;
    }
    if (categories.value.some((category) => category.key.toLowerCase() === name.toLowerCase())) {
        window.alert('That category already exists.');
        return;
    }

    categories.value.push({ key: name, label: name, stories: form.value.stories || 0, views: form.value.views || 0 });
    closeModal();
};

const deleteCategory = (index: number) => {
    const category = categories.value[index];
    if (!category) return;
    if (!window.confirm(`Delete category "${category.label}"?`)) return;
    categories.value.splice(index, 1);
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black">News Categories</h3>
                <p class="text-slate-500">Manage Caribbean 360 categories and story counts. New categories appear in Preferences, Admin Upload &amp; Create Story.</p>
            </div>
            <button class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="openModal">
                <Plus class="h-4 w-4" /> Add Category
            </button>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div v-for="(category, index) in categories" :key="category.key" class="flex items-start justify-between rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div>
                    <b>{{ category.label }}</b>
                    <p class="mt-1 text-sm text-slate-500">{{ num(category.stories) }} stories • {{ kfmt(category.views) }} views</p>
                    <p class="mt-1 text-xs text-slate-400">Key: {{ category.key }}</p>
                </div>
                <button
                    title="Delete"
                    class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 text-slate-400 hover:text-rose-600"
                    @click="deleteCategory(index)"
                >
                    <Trash2 class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">Add Category</h3>
                        <p class="text-sm text-sky-100">Create a new news category</p>
                    </div>
                    <button @click="closeModal"><X class="h-6 w-6" /></button>
                </div>
                <div class="space-y-4 p-6">
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Category Name</span>
                        <input v-model="form.name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="e.g. Health" />
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Stories</span>
                            <input v-model.number="form.stories" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </label>
                        <label class="block">
                            <span class="text-sm font-black text-slate-600">Monthly Views</span>
                            <input v-model.number="form.views" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button class="rounded-2xl bg-slate-100 px-6 py-3 font-black" @click="closeModal">Cancel</button>
                    <button class="rounded-2xl bg-sky-600 px-6 py-3 font-black text-white" @click="saveCategory">Add Category</button>
                </div>
            </div>
        </div>
    </div>
</template>
