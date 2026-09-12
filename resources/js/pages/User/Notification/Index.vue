<template>
    <AuthenticatedLayout>

        <Head title="Notifications" />

        <div class="py-8 mt-5">
            <!-- Header -->
            <header class="flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-gray-800 drop-shadow">
                        Notifications
                    </h1>
                    <span v-if="unreadCount > 0"
                        class="text-sm px-2 py-1 rounded-lg bg-blue-500 text-white shadow-soft">
                        {{ unreadCount }} new
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="toggleDensity"
                        class="px-3 py-2 rounded-xl bg-white hover:bg-gray-50 shadow-sm text-gray-700 text-sm border"
                        title="Toggle density">
                        {{ compact ? 'Comfortable' : 'Compact' }}
                    </button>
                    <button v-if="selected.size > 0" @click="clearSelection"
                        class="px-3 py-2 rounded-xl bg-white hover:bg-gray-50 shadow-sm text-gray-700 text-sm border">
                        Clear ({{ selected.size }})
                    </button>
                    <button @click="markAllAsRead" :disabled="unreadCount === 0"
                        class="px-3 py-2 rounded-xl bg-white hover:bg-gray-50 shadow-sm text-gray-700 text-sm border disabled:opacity-50">
                        Mark all read
                    </button>
                    <button @click="showSettings = true"
                        class="px-3 py-2 rounded-xl bg-white hover:bg-gray-50 shadow-sm text-gray-700 text-sm border">
                        Settings
                    </button>
                </div>
            </header>

            <!-- Filters / Search -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <button v-for="filter in filters" :key="filter.value" @click="currentFilter = filter.value" :class="[
                    'px-3 py-1.5 rounded-full text-sm transition-colors',
                    currentFilter === filter.value
                        ? 'bg-blue-600 text-white'
                        : 'bg-white shadow-sm hover:bg-gray-50'
                ]">
                    {{ filter.label }}
                </button>

                <div class="ml-auto relative">
                    <input v-model="searchQuery" type="search" placeholder="Search notifications…"
                        class="w-64 max-w-[70vw] pl-9 pr-3 py-2 rounded-xl bg-white shadow-sm text-sm outline-none focus:ring-2 focus:ring-blue-500 border" />
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">🔎</span>
                </div>
            </div>

            <!-- Bulk actions -->
            <div v-if="selected.size > 0"
                class="mb-3 rounded-xl bg-white shadow-sm px-3 py-2 flex items-center justify-between border">
                <div><span class="font-medium">{{ selected.size }}</span> selected</div>
                <div class="flex gap-2">
                    <button @click="bulkMarkAsRead" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50">
                        Mark read
                    </button>
                    <button @click="bulkDelete"
                        class="px-3 py-1.5 rounded-lg border hover:bg-red-50 hover:text-red-600">
                        Delete
                    </button>
                    <button @click="clearSelection" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50">
                        Clear selection
                    </button>
                </div>
            </div>

            <!-- TODAY -->
            <section v-if="todayNotifications.length > 0" aria-labelledby="today" class="mb-8">
                <h2 id="today"
                    class="sticky top-0 z-10 text-xs font-semibold uppercase tracking-wider text-gray-700 bg-gray-50 py-2 px-2 rounded">
                    Today
                </h2>
                <ul role="feed" aria-busy="false" class="mt-2 space-y-2">
                    <NotificationCard v-for="notification in todayNotifications" :key="notification.id"
                        :notification="notification" :compact="compact" :appURL="appURL"
                        :is-selected="selected.has(notification.id)" @toggle-select="toggleSelect(notification.id)"
                        @mark-read="markAsRead(notification.id)" @delete="openDeleteModal(notification.id)"
                        @click="handleNotificationClick(notification)" @accept-invite="handleAcceptInvite" />
                </ul>
            </section>

            <!-- EARLIER -->
            <section v-if="earlierNotifications.length > 0" aria-labelledby="earlier" class="mb-8">
                <h2 id="earlier"
                    class="sticky top-0 z-10 text-xs font-semibold uppercase tracking-wider text-gray-700 bg-gray-50 py-2 px-2 rounded">
                    Earlier
                </h2>
                <ul role="feed" aria-busy="false" class="mt-2 space-y-2">
                    <NotificationCard v-for="notification in earlierNotifications" :key="notification.id"
                        :notification="notification" :compact="compact" :appURL="appURL"
                        :is-selected="selected.has(notification.id)" @toggle-select="toggleSelect(notification.id)"
                        @mark-read="markAsRead(notification.id)" @delete="openDeleteModal(notification.id)"
                        @click="handleNotificationClick(notification)" @accept-invite="handleAcceptInvite" />
                </ul>
            </section>

            <!-- Empty state -->
            <div v-if="filteredNotifications.length === 0" class="text-center text-gray-500 py-12">
                <div class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-gray-50 ring-1 ring-gray-200">
                    <span>✨</span>
                    <span>{{ searchQuery ? 'No notifications found' : "You're all caught up" }}</span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800">Delete Notification</h2>
                <p class="text-gray-600 text-sm mt-2">
                    Are you sure you want to delete this notification? This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
                        Cancel
                    </button>
                    <button @click="confirmDelete"
                        class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Settings Modal -->
        <Modal :show="showSettings" @close="showSettings = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Notification Settings</h3>
                <div class="space-y-4">
                    <div>
                        <p class="font-medium mb-2">Quiet hours</p>
                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="text-sm flex items-center gap-2">
                                From
                                <input v-model="settings.qhStart" type="time" class="border rounded px-2 py-1" />
                            </label>
                            <label class="text-sm flex items-center gap-2">
                                To
                                <input v-model="settings.qhEnd" type="time" class="border rounded px-2 py-1" />
                            </label>
                            <label class="text-sm inline-flex items-center gap-2">
                                <input v-model="settings.priorityBypass" type="checkbox" />
                                Allow priority
                            </label>
                        </div>
                    </div>

                    <div>
                        <p class="font-medium mb-2">Mute categories</p>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input v-model="settings.muteMessages" type="checkbox" />
                                Messages
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="settings.muteMatches" type="checkbox" />
                                Matches
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="settings.mutePayments" type="checkbox" />
                                Payments
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="settings.muteGifts" type="checkbox" />
                                Gifts
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="settings.muteSystem" type="checkbox" />
                                System
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button @click="showSettings = false" class="px-3 py-2 rounded-xl border hover:bg-gray-50">
                        Close
                    </button>
                    <button @click="saveSettings" class="px-3 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Modal from '@/components/front/Modal.vue';
import NotificationCard from '@/components/NotificationCard.vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

interface Notification {
    id: number;
    title: string;
    message: string;
    send_by: string;
    user_id?: number;
    type: 'message' | 'match' | 'payment' | 'gift' | 'system' | 'live_invite' | 'marketplace_order';
    context?: string;
    unread: boolean;
    priority: boolean;
    icon?: string;
    avatar?: string;
    metadata?: any;
    created_at: string;
}

const props = defineProps<{
    allnotifications: Notification[];
    appURL: string;
}>();

// State
const notifications = ref<Notification[]>([...props.allnotifications]);
const currentFilter = ref('all');
const searchQuery = ref('');
const compact = ref(false);
const selected = ref(new Set<number>());
const showDeleteModal = ref(false);
const notificationToDelete = ref<number | null>(null);
const showSettings = ref(false);

// Settings
const settings = ref({
    qhStart: '22:00',
    qhEnd: '07:00',
    priorityBypass: false,
    muteMessages: false,
    muteMatches: false,
    mutePayments: false,
    muteGifts: false,
    muteSystem: false,
});

// Filters
const filters = [
    { label: 'All', value: 'all' },
    { label: 'Unread', value: 'unread' },
    { label: 'Messages', value: 'message' },
    { label: 'Matches', value: 'match' },
    { label: 'Payments', value: 'payment' },
    { label: 'Marketplace Orders', value: 'marketplace_order' },
    { label: 'Gifts', value: 'gift' },
    { label: 'System', value: 'system' },
    { label: 'Priority', value: 'priority' },
];

// Computed
const unreadCount = computed(() => {
    return notifications.value.filter((n) => n.unread).length;
});

const filteredNotifications = computed(() => {
    let result = notifications.value;

    // Apply filter
    if (currentFilter.value === 'unread') {
        result = result.filter((n) => n.unread);
    } else if (currentFilter.value === 'priority') {
        result = result.filter((n) => n.priority);
    } else if (currentFilter.value !== 'all') {
        result = result.filter((n) => n.type === currentFilter.value);
    }

    // Apply search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (n) =>
                n.title.toLowerCase().includes(query) ||
                n.message.toLowerCase().includes(query) ||
                (n.context && n.context.toLowerCase().includes(query))
        );
    }

    // Apply settings mutes
    if (settings.value.muteMessages) {
        result = result.filter((n) => n.type !== 'message');
    }
    if (settings.value.muteMatches) {
        result = result.filter((n) => n.type !== 'match');
    }
    if (settings.value.mutePayments) {
        result = result.filter((n) => n.type !== 'payment');
    }
    if (settings.value.muteGifts) {
        result = result.filter((n) => n.type !== 'gift');
    }
    if (settings.value.muteSystem) {
        result = result.filter((n) => n.type !== 'system');
    }

    // Sort by priority and date
    return result.sort((a, b) => {
        if (a.priority !== b.priority) return a.priority ? -1 : 1;
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });
});

const todayNotifications = computed(() => {
    const dayAgo = Date.now() - 24 * 60 * 60 * 1000;
    return filteredNotifications.value.filter(
        (n) => new Date(n.created_at).getTime() > dayAgo
    );
});

const earlierNotifications = computed(() => {
    const dayAgo = Date.now() - 24 * 60 * 60 * 1000;
    return filteredNotifications.value.filter(
        (n) => new Date(n.created_at).getTime() <= dayAgo
    );
});

// Methods
function toggleDensity() {
    compact.value = !compact.value;
}

function toggleSelect(id: number) {
    if (selected.value.has(id)) {
        selected.value.delete(id);
    } else {
        selected.value.add(id);
    }
}

function clearSelection() {
    selected.value.clear();
}

function handleNotificationClick(notification: Notification) {
    if (notification.unread) {
        markAsRead(notification.id);
    }
}

function markAsRead(id: number) {
    router.patch(
        route('frontend.notifications.read', id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                const notification = notifications.value.find((n) => n.id === id);
                if (notification) {
                    notification.unread = false;
                }
            },
        }
    );
}

function markAllAsRead() {
    router.post(
        route('frontend.notifications.markAllRead'),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                notifications.value.forEach((n) => (n.unread = false));
            },
        }
    );
}

function openDeleteModal(id: number) {
    notificationToDelete.value = id;
    showDeleteModal.value = true;
}

function confirmDelete() {
    if (!notificationToDelete.value) return;

    router.delete(route('frontend.notifications.destroy', notificationToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            notifications.value = notifications.value.filter(
                (n) => n.id !== notificationToDelete.value
            );
            showDeleteModal.value = false;
            notificationToDelete.value = null;
        },
        onError: () => {
            alert('Failed to delete notification. Please try again.');
            showDeleteModal.value = false;
        },
    });
}

function bulkMarkAsRead() {
    const ids = Array.from(selected.value);
    router.post(
        route('frontend.notifications.bulkRead'),
        { ids },
        {
            preserveScroll: true,
            onSuccess: () => {
                notifications.value.forEach((n) => {
                    if (ids.includes(n.id)) {
                        n.unread = false;
                    }
                });
                clearSelection();
            },
        }
    );
}

function bulkDelete() {
    const ids = Array.from(selected.value);
    router.post(
        route('frontend.notifications.bulkDelete'),
        { ids },
        {
            preserveScroll: true,
            onSuccess: () => {
                notifications.value = notifications.value.filter((n) => !ids.includes(n.id));
                clearSelection();
            },
        }
    );
}

const handleAcceptInvite = async (notification: Notification) => {
    // 1. Mark notification as read
    if (notification.unread) {
        markAsRead(notification.id);
    }

    const streamId = notification.metadata?.stream_id;
    if (!streamId) {
        toast.error('Stream ID missing from invitation.');
        return;
    }

    // 2. Check stream status
    try {
        // We use the 'end' endpoint just to check if it returns 200 or 400 (if it returns 400 with 'ended' message)
        // Actually, let's just use the replyInvite endpoint. If the stream is ended, it will return 400 now with my backend change.
        const response = await axios.post(route('frontend.live.invite.reply', { stream: streamId }), { accept: true });

        if (response.data.success) {
            // 3. Redirect to stream
            window.location.href = route('frontend.go-live.index') + '?join_stream=' + streamId;
        }

    } catch (error: any) {
        console.error("Error accepting invitation", error);
        toast.error(error.response?.data?.message || 'Failed to join stream or stream ended.');
    }
}

function saveSettings() {
    localStorage.setItem('notifSettings', JSON.stringify(settings.value));
    showSettings.value = false;
}

function loadSettings() {
    const saved = localStorage.getItem('notifSettings');
    if (saved) {
        try {
            settings.value = { ...settings.value, ...JSON.parse(saved) };
        } catch (e) {
            console.error('Failed to load settings:', e);
        }
    }
}

// Keyboard shortcuts
function handleKeydown(e: KeyboardEvent) {
    const tag = (e.target as HTMLElement).tagName.toLowerCase();
    if (tag === 'input' || tag === 'textarea') return;

    if (e.key === '/') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"]') as HTMLInputElement;
        searchInput?.focus();
    }

    if (e.key === 'A' && e.shiftKey) {
        e.preventDefault();
        markAllAsRead();
    }
}

// Lifecycle
onMounted(() => {
    loadSettings();
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>
