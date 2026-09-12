<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Activity, Download, LockKeyhole, Plus, ShieldCheck, Trash2, Users, X } from 'lucide-vue-next';

type PermissionRow = {
    module: string;
    view: boolean;
    create: boolean;
    edit: boolean;
    approve: boolean;
    export: boolean;
    delete: boolean;
};

type AdminRole = {
    id?: number;
    name: string;
    purpose: string;
    users: number;
    coreAccess: string;
    coreTone: string;
    risk: 'Low' | 'Medium' | 'High' | 'Critical';
    status: string;
    permissions: PermissionRow[];
};

const DEFAULT_MODULES = [
    'Dashboard',
    'Users',
    'Event Management',
    'Event Organizer',
    'LinkUp Eats',
    'E-Wallet',
    'ASUE Drawer Data',
    'Bill Gateway',
    'Marketplace',
    'Swipes',
    'Caribbean 360 News',
    'Emails',
    'Push Notifications',
    'Taxes',
    'Reports',
    'Admins',
    'Settings',
];

const props = defineProps<{
    initialRoles?: AdminRole[];
    initialPermissionModules?: string[];
}>();

const roles = computed<AdminRole[]>(() => props.initialRoles ?? []);
const permissionModules = computed(() => (props.initialPermissionModules?.length ? props.initialPermissionModules : DEFAULT_MODULES));

const roleSearch = ref('');
const roleModalOpen = ref(false);
const roleModalTitle = ref('Create Role');
const editingRoleId = ref<number | null>(null);

const emptyPermissionRows = (): PermissionRow[] =>
    permissionModules.value.map((module, index) => ({
        module,
        view: index === 0,
        create: false,
        edit: false,
        approve: false,
        export: false,
        delete: false,
    }));

const form = useForm({
    name: '',
    risk: 'Medium' as AdminRole['risk'],
    description: '',
    permissions: emptyPermissionRows(),
});

const metrics = computed(() => [
    { label: 'Total Roles', value: roles.value.length, icon: ShieldCheck, tone: 'purple' },
    { label: 'Admin Users', value: roles.value.reduce((sum, role) => sum + role.users, 0), icon: Users, tone: 'sky' },
    { label: 'Protected Modules', value: permissionModules.value.length, icon: LockKeyhole, tone: 'green' },
]);

const filteredRoles = computed(() => {
    const query = roleSearch.value.trim().toLowerCase();
    if (!query) return roles.value;

    return roles.value.filter((role) =>
        [role.name, role.purpose, role.coreAccess, role.risk, role.status].join(' ').toLowerCase().includes(query),
    );
});

const badgeClass = (tone: string) => {
    const classes: Record<string, string> = {
        purple: 'bg-purple-50 text-purple-700',
        sky: 'bg-sky-50 text-sky-700',
        green: 'bg-green-50 text-green-700',
        rose: 'bg-rose-50 text-rose-700',
        amber: 'bg-amber-50 text-amber-700',
        orange: 'bg-orange-50 text-orange-700',
        indigo: 'bg-indigo-50 text-indigo-700',
        pink: 'bg-pink-50 text-pink-700',
        slate: 'bg-slate-100 text-slate-700',
    };

    return classes[tone] || classes.slate;
};

const iconBoxClass = (tone: string) => {
    const classes: Record<string, string> = {
        purple: 'bg-purple-100 text-purple-600',
        sky: 'bg-sky-100 text-sky-600',
        green: 'bg-green-100 text-green-600',
        amber: 'bg-amber-100 text-amber-600',
    };

    return classes[tone] || classes.purple;
};

const riskClass = (risk: AdminRole['risk']) => {
    if (risk === 'Critical' || risk === 'High') return 'bg-rose-50 text-rose-700';
    if (risk === 'Medium') return 'bg-amber-50 text-amber-700';
    return 'bg-sky-50 text-sky-700';
};

const openRoleModal = (role?: AdminRole) => {
    roleModalTitle.value = role ? `Edit Role: ${role.name}` : 'Create Role';
    editingRoleId.value = role?.id ?? null;
    form.clearErrors();
    form.name = role?.name || '';
    form.risk = role?.risk || 'Medium';
    form.description = role?.purpose || '';

    if (role) {
        form.permissions = permissionModules.value.map((module) => {
            const existing = role.permissions.find((row) => row.module === module);
            return existing
                ? { ...existing }
                : { module, view: false, create: false, edit: false, approve: false, export: false, delete: false };
        });
    } else {
        form.permissions = emptyPermissionRows();
    }

    roleModalOpen.value = true;
};

const closeRoleModal = () => {
    roleModalOpen.value = false;
};

const saveRole = () => {
    const options = { preserveScroll: true, onSuccess: () => closeRoleModal() };

    if (editingRoleId.value) {
        form.put(route('admin.administration.roles.update', editingRoleId.value), options);
    } else {
        form.post(route('admin.administration.roles.store'), options);
    }
};

const deleteRole = (role: AdminRole) => {
    if (!role.id || role.name === 'admin') return;
    if (!confirm(`Delete the ${role.name} role?`)) return;

    router.delete(route('admin.administration.roles.destroy', role.id), { preserveScroll: true });
};

const adminExportCsv = () => {
    const rows = [
        ['Role', 'Purpose', 'Users', 'Core Access', 'Risk Level', 'Status'],
        ...roles.value.map((role) => [role.name, role.purpose, role.users, role.coreAccess, role.risk, role.status]),
    ];
    const csv = rows.map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'linkup_admin_roles.csv';
    link.click();
    URL.revokeObjectURL(link.href);
};
</script>

<template>
    <section class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="gradient-title text-3xl font-black">Administrative Roles & Access Control</h3>
                <p class="text-slate-500">Create roles, assign module permissions, and control who can access each LinkUp backend section.</p>
            </div>
            <div class="flex gap-2">
                <button
                    @click="openRoleModal()"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 px-5 py-3 font-black text-white shadow-lg shadow-purple-200"
                >
                    <Plus class="h-4 w-4" /> Create Role
                </button>
                <button @click="adminExportCsv" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white">
                    <Download class="h-4 w-4" /> Export CSV
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="metric in metrics" :key="metric.label" class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-14 w-14 place-items-center rounded-2xl" :class="iconBoxClass(metric.tone)">
                    <component :is="metric.icon" class="h-6 w-6" />
                </div>
                <div>
                    <p class="font-bold text-slate-500">{{ metric.label }}</p>
                    <h3 class="text-4xl font-black">{{ metric.value }}</h3>
                </div>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-xl font-black">Role Directory</h3>
                    <p class="text-slate-500">Each role controls exactly what the admin can see, create, edit, approve, export, or delete.</p>
                </div>
                <input
                    v-model="roleSearch"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none xl:w-96"
                    placeholder="Search role, module, permission..."
                />
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Role</th>
                            <th class="py-3 pr-4">Purpose</th>
                            <th class="py-3 pr-4">Users</th>
                            <th class="py-3 pr-4">Core Access</th>
                            <th class="py-3 pr-4">Risk Level</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="role in filteredRoles" :key="role.name">
                            <td class="py-4 pr-4 font-black">{{ role.name }}</td>
                            <td class="py-4 pr-4 text-slate-600">{{ role.purpose }}</td>
                            <td class="py-4 pr-4 font-bold">{{ role.users }}</td>
                            <td class="py-4 pr-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold" :class="badgeClass(role.coreTone)">{{ role.coreAccess }}</span>
                            </td>
                            <td class="py-4 pr-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold" :class="riskClass(role.risk)">{{ role.risk }}</span>
                            </td>
                            <td class="py-4 pr-4">
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">{{ role.status }}</span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <button @click="openRoleModal(role)" class="font-bold text-purple-600">Edit</button>
                                    <button
                                        v-if="role.name !== 'admin'"
                                        @click="deleteRole(role)"
                                        class="rounded-xl p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                        title="Delete role"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filteredRoles.length">
                            <td colspan="7" class="py-8 text-center text-slate-400">No roles found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="roleModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-5">
            <div class="w-full max-w-6xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between bg-gradient-to-r from-purple-600 to-fuchsia-500 p-8 text-white">
                    <div>
                        <h3 class="text-4xl font-black">{{ roleModalTitle }}</h3>
                        <p class="mt-2 text-lg text-purple-100">Set module permissions for this administrative role.</p>
                    </div>
                    <button @click="closeRoleModal" class="rounded-2xl p-2 hover:bg-white/10">
                        <X class="h-8 w-8" />
                    </button>
                </div>
                <div class="scrollbar max-h-[75vh] space-y-6 overflow-y-auto p-8">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <label class="md:col-span-2">
                            <span class="font-bold text-slate-600">Role Name</span>
                            <input v-model="form.name" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none" placeholder="e.g. Wallet Operations" />
                            <span v-if="form.errors.name" class="mt-1 block text-sm text-rose-600">{{ form.errors.name }}</span>
                        </label>
                        <label>
                            <span class="font-bold text-slate-600">Risk Level</span>
                            <select v-model="form.risk" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none">
                                <option>Low</option>
                                <option>Medium</option>
                                <option>High</option>
                                <option>Critical</option>
                            </select>
                        </label>
                        <label class="md:col-span-3">
                            <span class="font-bold text-slate-600">Description</span>
                            <input v-model="form.description" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none" placeholder="What this role is responsible for" />
                        </label>
                    </div>

                    <div class="overflow-hidden rounded-3xl border border-slate-200">
                        <div class="bg-slate-50 p-5">
                            <h4 class="text-xl font-black">Permission Builder</h4>
                            <p class="text-slate-500">Choose what this role can do in each LinkUp module.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="p-4">Module</th>
                                        <th>View</th>
                                        <th>Create</th>
                                        <th>Edit</th>
                                        <th>Approve</th>
                                        <th>Export</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in form.permissions" :key="row.module" class="border-t border-slate-100">
                                        <td class="p-4 font-black">{{ row.module }}</td>
                                        <td><input v-model="row.view" type="checkbox" class="h-5 w-5 accent-purple-600" /></td>
                                        <td><input v-model="row.create" type="checkbox" class="h-5 w-5 accent-purple-600" /></td>
                                        <td><input v-model="row.edit" type="checkbox" class="h-5 w-5 accent-purple-600" /></td>
                                        <td><input v-model="row.approve" type="checkbox" class="h-5 w-5 accent-purple-600" /></td>
                                        <td><input v-model="row.export" type="checkbox" class="h-5 w-5 accent-purple-600" /></td>
                                        <td><input v-model="row.delete" type="checkbox" class="h-5 w-5 accent-purple-600" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-6">
                    <button @click="closeRoleModal" class="rounded-2xl bg-slate-100 px-6 py-3 font-black">Cancel</button>
                    <button @click="saveRole" :disabled="form.processing" class="rounded-2xl bg-purple-600 px-6 py-3 font-black text-white disabled:opacity-60">Save Role</button>
                </div>
            </div>
        </div>
    </section>
</template>
