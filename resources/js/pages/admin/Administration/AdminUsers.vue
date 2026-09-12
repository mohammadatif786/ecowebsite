<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, X } from 'lucide-vue-next';

type AdminUser = {
    id?: number;
    name: string;
    email: string;
    role: string;
    scope: string;
    lastLogin: string;
    twofa: 'Enabled' | 'Pending';
    status: 'Active' | 'Suspended';
};

const props = defineProps<{
    initialAdminUsers?: AdminUser[];
}>();

const users = computed<AdminUser[]>(() => props.initialAdminUsers ?? []);

const modalOpen = ref(false);
const search = ref('');
const form = useForm({
    name: '',
    email: '',
    role: 'News Manager',
    scope: 'Caribbean',
    two_factor_enabled: false,
    status: 'Active' as 'Active' | 'Suspended',
});

const filteredUsers = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return users.value;

    return users.value.filter((user) =>
        [user.name, user.email, user.role, user.scope, user.lastLogin, user.twofa, user.status].join(' ').toLowerCase().includes(query),
    );
});

const openAddModal = () => {
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
};

const addUser = () => {
    form.post(route('admin.administration.admin-users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            modalOpen.value = false;
        },
    });
};

const deleteUser = (user: AdminUser) => {
    if (!user.id) return;
    if (!confirm(`Remove ${user.name} from admin users?`)) return;

    router.delete(route('admin.administration.admin-users.destroy', user.id), {
        preserveScroll: true,
    });
};

const twofaClass = (twofa: AdminUser['twofa']) => {
    return twofa === 'Enabled' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700';
};
</script>

<template>
    <section class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-3xl font-black">Admin Users</h3>
                <p class="text-slate-500">Assign staff members to roles and restrict backend access by department.</p>
            </div>
            <button @click="openAddModal" class="inline-flex items-center gap-2 rounded-2xl bg-purple-600 px-5 py-3 font-black text-white">
                <Plus class="h-4 w-4" /> Add Admin User
            </button>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h4 class="text-xl font-black">Admin Directory</h4>
                    <p class="text-sm text-slate-500">Review role assignments, country scope, last login, and 2FA status.</p>
                </div>
                <input
                    v-model="search"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none md:w-80"
                    placeholder="Search admin, email, role..."
                />
            </div>

            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Admin</th>
                            <th class="py-3 pr-4">Email</th>
                            <th class="py-3 pr-4">Role</th>
                            <th class="py-3 pr-4">Country Scope</th>
                            <th class="py-3 pr-4">Last Login</th>
                            <th class="py-3 pr-4">2FA</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in filteredUsers" :key="user.email">
                            <td class="py-4 pr-4 font-black">{{ user.name }}</td>
                            <td class="py-4 pr-4 text-slate-600">{{ user.email }}</td>
                            <td class="py-4 pr-4 font-semibold text-slate-700">{{ user.role }}</td>
                            <td class="py-4 pr-4 text-slate-600">{{ user.scope }}</td>
                            <td class="py-4 pr-4 text-slate-600">{{ user.lastLogin }}</td>
                            <td class="py-4 pr-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold" :class="twofaClass(user.twofa)">{{ user.twofa }}</span>
                            </td>
                            <td class="py-4 pr-4 font-semibold text-slate-700">{{ user.status }}</td>
                            <td class="py-4">
                                <button @click="deleteUser(user)" class="rounded-xl p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Remove">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!filteredUsers.length">
                            <td colspan="8" class="py-8 text-center text-slate-400">No admin users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-5">
            <div class="w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between bg-gradient-to-r from-purple-600 to-fuchsia-500 p-8 text-white">
                    <div>
                        <h3 class="text-4xl font-black">Add Admin User</h3>
                        <p class="mt-2 text-lg text-purple-100">Assign a staff member to an administrative role.</p>
                    </div>
                    <button @click="modalOpen = false" class="rounded-2xl p-2 hover:bg-white/10">
                        <X class="h-8 w-8" />
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 p-8 md:grid-cols-2">
                    <label>
                        <span class="font-bold text-slate-600">Admin Name</span>
                        <input v-model="form.name" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none" placeholder="Full name" />
                        <span v-if="form.errors.name" class="mt-1 block text-sm text-rose-600">{{ form.errors.name }}</span>
                    </label>
                    <label>
                        <span class="font-bold text-slate-600">Email</span>
                        <input v-model="form.email" type="email" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none" placeholder="admin@linkupvibes.com" />
                        <span v-if="form.errors.email" class="mt-1 block text-sm text-rose-600">{{ form.errors.email }}</span>
                    </label>
                    <label>
                        <span class="font-bold text-slate-600">Role</span>
                        <select v-model="form.role" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none">
                            <option>Super Administrator</option>
                            <option>News Manager</option>
                            <option>Event Operations</option>
                            <option>Wallet Operations</option>
                            <option>Marketplace Manager</option>
                            <option>Marketing & Messaging</option>
                            <option>Read Only Auditor</option>
                        </select>
                    </label>
                    <label>
                        <span class="font-bold text-slate-600">Country Scope</span>
                        <input v-model="form.scope" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none" placeholder="Global, Caribbean, Bahamas..." />
                    </label>
                    <label>
                        <span class="font-bold text-slate-600">2FA</span>
                        <select v-model="form.two_factor_enabled" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none">
                            <option :value="true">Enabled</option>
                            <option :value="false">Pending</option>
                        </select>
                    </label>
                    <label>
                        <span class="font-bold text-slate-600">Status</span>
                        <select v-model="form.status" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none">
                            <option>Active</option>
                            <option>Suspended</option>
                        </select>
                    </label>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 p-6">
                    <button @click="modalOpen = false" class="rounded-2xl bg-slate-100 px-6 py-3 font-black">Cancel</button>
                    <button @click="addUser" :disabled="form.processing" class="rounded-2xl bg-purple-600 px-6 py-3 font-black text-white disabled:opacity-60">Add User</button>
                </div>
            </div>
        </div>
    </section>
</template>
