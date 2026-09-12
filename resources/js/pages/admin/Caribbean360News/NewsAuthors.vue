<script setup lang="ts">
import { computed, ref } from 'vue';
import { Pencil, Plus, Trash2, X } from 'lucide-vue-next';

type Status = 'Active' | 'Inactive';

type Author = {
    name: string;
    role: string;
    country: string;
    email: string;
    stories: number;
    views: number;
    status: Status;
};

const authors = ref<Author[]>([
    { name: 'LinkUp News Desk', role: 'Editor', country: 'Regional', email: 'news@linkupvibes.com', stories: 68, views: 410000, status: 'Active' },
    { name: 'Marsha Clarke', role: 'Travel Writer', country: 'Jamaica', email: 'marsha@linkup360.com', stories: 22, views: 148000, status: 'Active' },
    { name: 'Andre Rolle', role: 'Business Contributor', country: 'Bahamas', email: 'andre@linkup360.com', stories: 17, views: 103000, status: 'Active' },
    { name: 'Sasha Baptiste', role: 'Entertainment Writer', country: 'Trinidad & Tobago', email: 'sasha@linkup360.com', stories: 19, views: 126000, status: 'Active' },
    { name: 'Nia Browne', role: 'Culture Contributor', country: 'Barbados', email: 'nia@linkup360.com', stories: 14, views: 88000, status: 'Active' },
    { name: 'Carib Diaspora Desk', role: 'Diaspora Editor', country: 'United States', email: 'diaspora@linkup360.com', stories: 31, views: 231000, status: 'Active' },
]);

const num = (value: number) => Math.round(value || 0).toLocaleString();
const kfmt = (value: number) => (value >= 1000 ? `${(value / 1000).toFixed(0)}K` : num(value));

const showModal = ref(false);
const editingIndex = ref<number | null>(null);
const form = ref({ name: '', role: '', country: '', email: '', stories: 0, status: 'Active' as Status });

const modalTitle = computed(() => (editingIndex.value != null ? 'Edit Author' : 'Add Author'));
const saveLabel = computed(() => (editingIndex.value != null ? 'Update Author' : 'Add Author'));

const openAddModal = () => {
    editingIndex.value = null;
    form.value = { name: '', role: '', country: '', email: '', stories: 0, status: 'Active' };
    showModal.value = true;
};

const openEditModal = (index: number) => {
    const author = authors.value[index];
    if (!author) return;
    editingIndex.value = index;
    form.value = { name: author.name, role: author.role, country: author.country, email: author.email, stories: author.stories, status: author.status };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveAuthor = () => {
    const name = form.value.name.trim();
    if (!name) {
        window.alert('Enter an author name.');
        return;
    }

    const data: Author = {
        name,
        role: form.value.role.trim() || 'Contributor',
        country: form.value.country.trim() || 'Regional',
        email: form.value.email.trim(),
        stories: form.value.stories || 0,
        views: editingIndex.value != null ? authors.value[editingIndex.value].views : 0,
        status: form.value.status,
    };

    if (editingIndex.value != null) {
        authors.value[editingIndex.value] = { ...authors.value[editingIndex.value], ...data };
    } else {
        authors.value.push(data);
    }
    closeModal();
};

const deleteAuthor = (index: number) => {
    const author = authors.value[index];
    if (!author) return;
    if (!window.confirm(`Delete author "${author.name}"?`)) return;
    authors.value.splice(index, 1);
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black">Authors &amp; Contributors</h3>
                <p class="text-slate-500">Manage Caribbean 360 writers, editors, and contributors. Authors appear in Create Story &amp; Admin Upload.</p>
            </div>
            <button class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="openAddModal">
                <Plus class="h-4 w-4" /> Add Author
            </button>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-4">Name</th>
                            <th>Role</th>
                            <th>Country</th>
                            <th>Email</th>
                            <th>Stories</th>
                            <th>Monthly Views</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(author, index) in authors" :key="author.name" class="border-t border-slate-100">
                            <td class="p-4 font-black">{{ author.name }}</td>
                            <td>{{ author.role }}</td>
                            <td>{{ author.country }}</td>
                            <td class="text-slate-500">{{ author.email }}</td>
                            <td>{{ num(author.stories) }}</td>
                            <td>{{ kfmt(author.views) }}</td>
                            <td>
                                <span class="rounded-full px-3 py-1 text-xs font-black" :class="author.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600'">
                                    {{ author.status }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="rounded-xl bg-slate-100 px-3 py-2" @click="openEditModal(index)"><Pencil class="h-4 w-4" /></button>
                                    <button class="rounded-xl bg-slate-100 px-3 py-2 text-slate-500" @click="deleteAuthor(index)"><Trash2 class="h-4 w-4" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">{{ modalTitle }}</h3>
                        <p class="text-sm text-sky-100">Create a writer, editor, or contributor</p>
                    </div>
                    <button @click="closeModal"><X class="h-6 w-6" /></button>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <span class="text-sm font-black text-slate-600">Name</span>
                        <input v-model="form.name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="Full name" />
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Role</span>
                        <input v-model="form.role" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="e.g. Travel Writer" />
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Country</span>
                        <input v-model="form.country" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="e.g. Jamaica" />
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-black text-slate-600">Email</span>
                        <input v-model="form.email" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="email@linkup360.com" />
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Stories</span>
                        <input v-model.number="form.stories" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Status</span>
                        <select v-model="form.status" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </label>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button class="rounded-2xl bg-slate-100 px-6 py-3 font-black" @click="closeModal">Cancel</button>
                    <button class="rounded-2xl bg-sky-600 px-6 py-3 font-black text-white" @click="saveAuthor">{{ saveLabel }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
