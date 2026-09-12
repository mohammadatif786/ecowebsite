<template>
    <AppLayout>
        <div class="card overflow-visible bg-white rounded-[20px] border border-slate-100 shadow-sm">
            <!-- Header with Results & Controls -->
            <div class="flex items-center justify-between px-5 py-4 flex-wrap gap-2">
                <p class="text-slate-400 font-bold text-sm">{{ sortedScanners.length }} result{{ sortedScanners.length
                    === 1 ? '' : 's' }} found</p>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-500 font-bold">Sort by</span>
                    <select v-model="sortBy"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none bg-white font-bold">
                        <option value="created_desc">Creation date (desc)</option>
                        <option value="created_asc">Creation date (asc)</option>
                        <option value="name_asc">Name (A-Z)</option>
                    </select>
                    <button v-if="hasPermission('organizer scanner create')" @click="showAddModal = true"
                        class="h-9 w-9 rounded-full text-white grid place-items-center font-black shrink-0 shadow-lg hover:scale-105 transition active:scale-95"
                        style="background:linear-gradient(135deg,#2dd4bf,#eab308)">
                        <Plus class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-100">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-4 py-3">Username</th>
                            <th class="px-4 py-3">Creation date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="scanner in sortedScanners" :key="scanner.id"
                            class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 font-black text-slate-700">{{ scanner.first_name }} {{
                                scanner.last_name }}</td>
                            <td class="px-4 py-3 text-slate-500 font-medium">{{ scanner.email }}</td>
                            <td class="px-4 py-3 text-slate-500 font-medium">{{ formatDate(scanner.created_at) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black"
                                    :class="scanner.status == 1 ? 'bg-cyan-50 text-cyan-700' : 'bg-slate-100 text-slate-500'">
                                    Person {{ scanner.status == 1 ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <button @click="toggleRowMenu($event, scanner.id)"
                                class="h-8 w-8 rounded-lg grid place-items-center hover:bg-slate-100 ml-auto transition">
                                <MoreVertical class="w-4 h-4 text-slate-400" />
                            </button>

                            <td class="px-4 py-3 text-right relative">

                                <Teleport to="body">
                                    <div v-if="openMenu === scanner.id"
                                        class="fixed z-[9999] w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 text-left animate-in fade-in slide-in-from-top-2 duration-200"
                                        :style="{ top: menuPosition.top + 'px', left: menuPosition.left + 'px' }"
                                        @click.stop>
                                        <button v-if="hasPermission('organizer scanner edit')"
                                            @click="openEdit(scanner); closeMenu()"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                            Edit
                                        </button>
                                        <button v-if="hasPermission('organizer change status')"
                                            @click="toggleStatus(scanner); closeMenu()"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                            {{ scanner.status == 1 ? 'Disable' : 'Enable' }}
                                        </button>
                                        <button v-if="hasPermission('organizer scanner delete')"
                                            @click="deleteScanner(scanner); closeMenu()"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                            Delete
                                        </button>
                                        <button v-if="hasPermission('organizer scanner assign role') && !hasScannerRole(scanner)"
                                            @click="assignScannerRole(scanner); closeMenu()"
                                           class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                            <span>Assign Role</span>
                                        </button>
                                        <button v-if="hasPermission('organizer scanner assign role') && hasScannerRole(scanner)"
                                            @click="removeScannerRole(scanner); closeMenu()"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-rose-600 transition">
                                            <span>Remove Role</span>
                                        </button>
                                        <button v-if="hasPermission('organizer scanners')"
                                            @click="openPermissions(scanner); closeMenu()"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                            Permissions
                                        </button>
                                    </div>
                                </Teleport>
                            </td>
                        </tr>
                        <tr v-if="!sortedScanners.length">
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400 font-bold">No scanners yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Modal matching orgOpenScannerForm reference -->
        <div v-if="showAddModal"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden"
            @click.self="showAddModal = false">
            <div
                class="bg-white rounded-[20px] w-full max-w-lg max-h-[90vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col">
                <div class="p-5 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-xl font-black text-slate-800">Add Scanner</h3>
                    <button @click="showAddModal = false"
                        class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center transition">
                        <X class="w-5 h-5 text-slate-500" />
                    </button>
                </div>

                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <form @submit.prevent="submitAdd" class="space-y-3">
                        <div>
                            <input v-model="addForm.first_name" placeholder="First Name"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                            <p v-if="addForm.errors.first_name" class="mt-1 text-xs text-red-600 font-bold">{{
                                addForm.errors.first_name }}</p>
                        </div>

                        <div>
                            <input v-model="addForm.last_name" placeholder="Last Name"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                            <p v-if="addForm.errors.last_name" class="mt-1 text-xs text-red-600 font-bold">{{
                                addForm.errors.last_name }}</p>
                        </div>

                        <div>
                            <input v-model="addForm.email" type="email" placeholder="Username or email"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                            <p v-if="addForm.errors.email" class="mt-1 text-xs text-red-600 font-bold">{{
                                addForm.errors.email }}</p>
                        </div>

                        <div>
                            <input v-model="addForm.password" type="password" placeholder="Password"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                            <p v-if="addForm.errors.password" class="mt-1 text-xs text-red-600 font-bold">{{
                                addForm.errors.password }}</p>
                        </div>

                        <div>
                            <input v-model="addForm.password_confirmation" type="password" placeholder="Repeat password"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-full py-3 rounded-xl font-black text-white transition hover:brightness-105 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2 mt-4"
                            style="background: linear-gradient(135deg,#2f9bef,#2563eb)" :disabled="addForm.processing">
                            <Loader2 v-if="addForm.processing" class="w-4 h-4 animate-spin" />
                            Add Scanner
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden"
            @click.self="showEditModal = false">
            <div
                class="bg-white rounded-[20px] w-full max-w-lg max-h-[90vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col">
                <div class="p-5 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-xl font-black text-slate-800">Edit Scanner</h3>
                    <button @click="showEditModal = false"
                        class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center transition">
                        <X class="w-5 h-5 text-slate-500" />
                    </button>
                </div>

                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <div
                        class="bg-blue-900 text-white px-3 py-2 rounded-xl mb-4 flex items-center text-[12px] font-bold">
                        <span
                            class="bg-white text-blue-900 rounded-full w-4 h-4 flex items-center justify-center font-bold mr-2 text-[10px]">i</span>
                        Leave the password empty to keep the old one
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input v-model="editForm.first_name" placeholder="First Name"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                                <p v-if="editForm.errors.first_name" class="mt-1 text-xs text-red-600 font-bold">{{
                                    editForm.errors.first_name }}</p>
                            </div>
                            <div>
                                <input v-model="editForm.last_name" placeholder="Last Name"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                                <p v-if="editForm.errors.last_name" class="mt-1 text-xs text-red-600 font-bold">{{
                                    editForm.errors.last_name }}</p>
                            </div>
                        </div>

                        <div>
                            <input v-model="editForm.email" type="email" placeholder="Username or email"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                            <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-600 font-bold">{{
                                editForm.errors.email }}</p>
                        </div>

                        <div>
                            <input v-model="editForm.password" type="password" placeholder="New Password (optional)"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                            <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-600 font-bold">{{
                                editForm.errors.password }}</p>
                        </div>

                        <div>
                            <input v-model="editForm.password_confirmation" type="password"
                                placeholder="Repeat new password"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-blue-400 font-medium" />
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-full py-3 rounded-xl font-black text-white transition hover:brightness-105 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2 mt-4"
                            style="background: linear-gradient(135deg,#2f9bef,#2563eb)" :disabled="editForm.processing">
                            <Loader2 v-if="editForm.processing" class="w-4 h-4 animate-spin" />
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Permissions Modal matching orgScannerPermissions reference line 4642 -->
        <div v-if="showPermissionsModal"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden"
            @click.self="showPermissionsModal = false">
            <div
                class="bg-white rounded-[20px] w-full max-w-lg max-h-[90vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col">
                <div class="p-5 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Permissions</h3>
                        <p class="text-slate-400 text-[12px] font-bold">{{ selectedScanner?.first_name }} {{
                            selectedScanner?.last_name }}</p>
                    </div>
                    <button @click="showPermissionsModal = false"
                        class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center transition">
                        <X class="w-5 h-5 text-slate-500" />
                    </button>
                </div>

                <div class="modal-scroll overflow-y-auto p-5 flex-1">
                    <div v-if="loadingPermissions" class="flex justify-center py-10">
                        <Loader2 class="w-8 h-8 text-blue-500 animate-spin" />
                    </div>

                    <div v-else-if="!scannerHasRole"
                        class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-4 rounded-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <Info class="h-5 w-5 text-amber-500" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-amber-700 font-bold">
                                    Assign the Scanner Role first to manage individual permissions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form v-else @submit.prevent="submitPermissions" class="space-y-4">
                        <div class="rounded-2xl border border-slate-100 px-4">
                            <div v-for="permission in availablePermissions" :key="permission.id"
                                class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                                <label :for="'perm-' + permission.id"
                                    class="font-bold text-sm text-slate-700 cursor-pointer">
                                    {{ formatPermissionName(permission.name) }}
                                </label>
                                <input type="checkbox" :id="'perm-' + permission.id" :value="permission.name"
                                    v-model="permissionForm.permissions"
                                    class="h-5 w-5 rounded border-slate-200 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                            </div>
                        </div>

                        <button type="submit" :disabled="permissionForm.processing"
                            class="btn btn-primary w-full py-3 rounded-xl font-black text-white transition hover:brightness-105 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2 mt-4"
                            style="background: linear-gradient(135deg,#2f9bef,#2563eb)">
                            <Loader2 v-if="permissionForm.processing" class="w-4 h-4 animate-spin" />
                            Done
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/organizer/AppLayout.vue'
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, usePage, router, Head, Link } from '@inertiajs/vue3'
import { QrCode, Plus, MoreVertical, Pencil, Trash2, X, Info, Loader2 } from 'lucide-vue-next'
import useMessages from '@/composables/useMessages'

const page = usePage<{
    auth: {
        permissions: string[];
    };
}>();

const props = defineProps<{ scanners: any[] }>()
const permissions = computed(() => page.props.auth?.permissions || []);

const hasPermission = (permission: string) => {
    return permissions.value.includes(permission);
};

useMessages()

// Sorting
const sortBy = ref('created_desc')

const sortedScanners = computed(() => {
    const scannersCopy = [...props.scanners]

    switch (sortBy.value) {
        case 'created_desc':
            return scannersCopy.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        case 'created_asc':
            return scannersCopy.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
        case 'name_asc':
            return scannersCopy.sort((a, b) => {
                const nameA = `${a.first_name} ${a.last_name}`.toLowerCase()
                const nameB = `${b.first_name} ${b.last_name}`.toLowerCase()
                return nameA.localeCompare(nameB)
            })
        default:
            return scannersCopy
    }
})

// Modals
const showAddModal = ref(false)
const showEditModal = ref(false)
const showPermissionsModal = ref(false)
const loadingPermissions = ref(false)
const selectedScanner = ref<any>(null)
const scannerHasRole = ref(false)
const availablePermissions = ref<any[]>([])
const openMenu = ref<number | null>(null)

const closeMenu = () => {
    openMenu.value = null
}

onMounted(() => {
    window.addEventListener('click', closeMenu)
})

onUnmounted(() => {
    window.removeEventListener('click', closeMenu)
})

// Add form
const addForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function submitAdd() {
    addForm.post(route('organizer.scanner.store'), {
        onSuccess: () => {
            addForm.reset()
            showAddModal.value = false
        },
    })
}

// Edit form
const editForm = useForm({
    id: null as number | null,
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function openEdit(scanner: any) {
    editForm.id = scanner.id
    editForm.first_name = scanner.first_name
    editForm.last_name = scanner.last_name
    editForm.email = scanner.email
    editForm.password = ''
    editForm.password_confirmation = ''
    showEditModal.value = true
}

function submitEdit() {
    if (!editForm.id) return
    editForm.put(route('organizer.scanner.update', editForm.id), {
        onSuccess: () => {
            editForm.reset()
            showEditModal.value = false
        },
    })
}

function assignScannerRole(scanner: any) {
    if (confirm(`Are you sure you want to assign the Scanner role to ${scanner.first_name}?`)) {
        router.post(route('organizer.scanner.assign-role', scanner.id), {
            role: 'scanner'
        }, {
            onSuccess: () => {
                // Flash message will be handled by the layout
            }
        })
    }
    openMenu.value = null
}

// Permissions form
const permissionForm = useForm({
    permissions: [] as string[]
})

async function openPermissions(scanner: any) {
    selectedScanner.value = scanner
    showPermissionsModal.value = true
    loadingPermissions.value = true

    try {
        const response = await fetch(route('organizer.scanner.permissions.get', scanner.id))
        const data = await response.json()
        availablePermissions.value = data.available
        permissionForm.permissions = data.current
        scannerHasRole.value = data.has_role
    } catch (error) {
        console.error('Failed to fetch permissions:', error)
    } finally {
        loadingPermissions.value = false
    }
}

function submitPermissions() {
    if (!selectedScanner.value) return
    permissionForm.post(route('organizer.scanner.permissions.assign', selectedScanner.value.id), {
        onSuccess: () => {
            showPermissionsModal.value = false
        }
    })
}

function formatPermissionName(name: string) {
    // Map internal permission names to labels matching reference
    const map: Record<string, string> = {
        'scanner scan_qr': 'Scan QR codes',
        'scanner manual_checkin': 'Manual list check-in',
        'scanner view_attendees': 'View attendee list',
        'scanner view_stats': 'View event stats'
    }
    return map[name] || name.replace('scanner ', '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const menuPosition = ref({ top: 0, left: 0 })
// Row actions
function toggleRowMenu(event: MouseEvent, id: number) {
    event.stopPropagation()

    if (openMenu.value === id) {
        openMenu.value = null
        return
    }

    const button = event.currentTarget as HTMLElement
    const rect = button.getBoundingClientRect()

    menuPosition.value = {
        top: rect.bottom + 4,           // just below the button
        left: rect.right - 192,         // 192px = w-48; right-align to button
    }

    openMenu.value = id
}

function toggleStatus(scanner: any) {
    router.patch(route('organizer.scanner.toggle', scanner.id))
}

function deleteScanner(scanner: any) {
    if (confirm(`Remove this scanner?`)) {
        router.delete(route('organizer.scanner.destroy', scanner.id))
    }
}

function removeScannerRole(scanner: any) {
    if (confirm(`Remove scanner role from this person? They will lose scanning access but remain on record.`)) {
        router.post(route('organizer.scanner.remove-role', scanner.id))
    }
}

function hasScannerRole(scanner: any) {
    if (!scanner || !scanner.roles) return false;
    return scanner.roles.some((role: any) => role.name === 'scanner');
}

function formatDate(dateString: string) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

watch(showAddModal, (isOpen) => {
    if (isOpen) {
        addForm.clearErrors()
    }
})

</script>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 20px;
}

.modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.modal-scroll::-webkit-scrollbar {
    width: 8px;
}

.modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.modal-scroll::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
    border: 2px solid white;
    background-clip: padding-box;
}

.modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

.fade-in {
    animation-name: fadeIn;
}

.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes slideInFromTop {
    from {
        transform: translateY(-0.5rem);
    }

    to {
        transform: translateY(0);
    }
}
</style>
