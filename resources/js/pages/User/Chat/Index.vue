<template>
    <AuthenticatedLayout>

        <Head title="Chats" />

        <div class="py-8 mt-5">
            <header class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800 drop-shadow">Chats</h1>
            </header>

            <!-- Empty state: no chats at all -->
            <div v-if="props.users.length === 0" class="text-center text-gray-500 py-12">
                <div class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-gray-50 ring-1 ring-gray-200">
                    <span>💬</span>
                    <span>No conversations yet</span>
                </div>
            </div>

            <template v-else>
                <!-- Pinned -->
                <section v-if="pinnedUsers.length > 0" class="mb-8">
                    <h2
                        class="sticky top-0 z-10 text-xs font-semibold uppercase tracking-wider text-gray-700 bg-gray-50 py-2 px-2 rounded mb-2">
                        Pinned
                    </h2>
                    <div class="space-y-2">
                        <div v-for="user in pinnedUsers" :key="user.id"
                            class="flex items-center gap-4 rounded-xl bg-white border border-blue-200 shadow-sm hover:bg-gray-50 px-4 py-3 cursor-pointer transition"
                            @click="startChat(user.name, user.uid)">

                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-blue-500">
                                    <img :src="avatarSrc(user)" alt="" class="w-full h-full object-cover" />
                                </div>
                                <span v-if="user.is_active"
                                    class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-semibold text-gray-800 truncate">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 shrink-0">{{
                                        formatTime(user?.last_message?.created_at) }}</div>
                                </div>
                                <div class="text-sm text-gray-500 truncate mt-0.5">{{ latestMessages[user.id] }}</div>
                            </div>

                            <!-- Meta -->
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span v-if="(user.unread_count ?? 0) > 0"
                                    class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-blue-600 text-white text-xs font-semibold flex items-center justify-center">
                                    {{ user.unread_count }}
                                </span>
                                <span v-if="!user.is_active" class="text-xs text-gray-400">
                                    {{ formatLastSeen(user.last_active) }}
                                </span>
                            </div>

                            <!-- Pin toggle -->
                            <button class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 shrink-0"
                                @click.stop="unpinUser(user)" title="Unpin">
                                <Pin class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </section>

                <!-- All Chats -->
                <section v-if="unpinnedUsers.length > 0">
                    <h2
                        class="sticky top-0 z-10 text-xs font-semibold uppercase tracking-wider text-gray-700 bg-gray-50 py-2 px-2 rounded mb-2">
                        All Chats
                    </h2>
                    <div class="space-y-2">
                        <div v-for="user in unpinnedUsers" :key="user.id"
                            class="flex items-center gap-4 rounded-xl bg-white border shadow-sm hover:bg-gray-50 px-4 py-3 cursor-pointer transition"
                            @click="startChat(user.name, user.uid)">

                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-gray-200">
                                    <img :src="avatarSrc(user)" alt="" class="w-full h-full object-cover" />
                                </div>
                                <span v-if="user.is_active"
                                    class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="truncate"
                                        :class="(user.unread_count ?? 0) > 0 ? 'font-semibold text-gray-800' : 'font-medium text-gray-700'">
                                        {{ user.name }}
                                    </div>
                                    <div class="text-xs text-gray-500 shrink-0">{{
                                        formatTime(user?.last_message?.created_at) }}</div>
                                </div>
                                <div class="text-sm text-gray-500 truncate mt-0.5">{{ latestMessages[user.id] }}</div>
                            </div>

                            <!-- Meta -->
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span v-if="(user.unread_count ?? 0) > 0"
                                    class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-blue-600 text-white text-xs font-semibold flex items-center justify-center">
                                    {{ user.unread_count }}
                                </span>
                                <span v-if="!user.is_active" class="text-xs text-gray-400">
                                    {{ formatLastSeen(user.last_active) }}
                                </span>
                            </div>

                            <!-- Pin toggle -->
                            <button
                                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-blue-500 shrink-0"
                                @click.stop="pinUser(user)" title="Pin">
                                <PinOff class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";
import { Pin, PinOff } from "lucide-vue-next";
import type { PageProps as InertiaPageProps } from '@inertiajs/core';

interface AuthUser {
    id: number;
    name: string;
    email: string;
}

interface PageProps extends InertiaPageProps {
    auth: {
        user: AuthUser;
    };
}

interface UserWithMessage {
    id: number;
    name: string;
    pinned: any;
    more_photos: string[];
    last_message?: Message | null;
    unread_count?: number;
    is_active?: any;
    last_active?: string;
    uid: string
}
interface Message {
    id: number;
    from_user_id: number;
    to_user_id: number;
    content: string;
    replied_to?: number | null;
    type?: string;
    meta?: Record<string, any> | null;
    created_at: string;
    updated_at: string;
    unread_count?: number;
    is_read: number;
}
const props = defineProps<{
    users: UserWithMessage[];
}>();

function formatTime(dateTime?: string): string {
    if (!dateTime) return "";
    const date = new Date(dateTime);
    const today = new Date();

    const isToday =
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear();

    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    const isYesterday =
        date.getDate() === yesterday.getDate() &&
        date.getMonth() === yesterday.getMonth() &&
        date.getFullYear() === yesterday.getFullYear();

    if (isToday) {
        return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    } else if (isYesterday) {
        return "Yesterday";
    } else {
        return date.toLocaleDateString([], {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }
}

const pinnedUsers = ref<UserWithMessage[]>([]);
function togglePin(users: UserWithMessage[]) {
    pinnedUsers.value = users.filter(u => u.pinned === 1);
}

const unpinnedUsers = ref<UserWithMessage[]>([]);
function toggleUnpin(users: UserWithMessage[]) {
    unpinnedUsers.value = users.filter((u: any) => !u.pinned);
}

function refreshPinnedLists() {
    togglePin(props.users);
    toggleUnpin(props.users);
}

// Fixed: '/storage/' + photo || fallback always produced a truthy string
// ("/storage/undefined") since string concatenation runs before `||`,
// so the picsum fallback never actually fired. This makes it an explicit check.
function avatarSrc(user: UserWithMessage): string {
    const photo = user.more_photos?.[0];
    return photo ? `/storage/${photo}` : `https://picsum.photos/seed/${user.id}/100`;
}

function getLastMessagePreview(user: UserWithMessage): string {
    const msg = user.last_message;
    if (!msg) return "No messages yet";

    const type = msg.type ?? 'text';
    if (type === 'text' || type === 'signal') {
        const content = (msg.content ?? '').toString().trim();
        return content !== '' ? content : 'New message';
    }

    if (type === 'image') return 'Sent a photo';
    if (type === 'pdf') return msg.meta?.name ? `Sent a PDF: ${msg.meta.name}` : 'Sent a PDF';
    if (type === 'file') return msg.meta?.name ? `Sent a file: ${msg.meta.name}` : 'Sent a file';
    if (type === 'gif') return 'Sent a GIF';
    if (type === 'ticket') return 'Sent a ticket';

    return 'New message';
}

const latestMessages = computed(() =>
    Object.fromEntries(
        props.users.map((user: any) => [user.id, getLastMessagePreview(user)])
    )
);

const page = usePage<PageProps>();
const currentUser = page.props.auth.user;
function computeUnreadPerUser(users: UserWithMessage[]) {
    users.forEach(user => {
        user.unread_count =
            user.last_message &&
                user.last_message.is_read === 0 &&
                user.last_message.to_user_id === currentUser.id
                ? 1
                : 0;
    });
}

const handleToggleUnPin = (userId: number) => {
    router.post(route("frontend.user.toggle.unpin", { userId }), {}, {
        preserveState: true
    });
};
const handleTogglePin = (userId: number) => {
    router.post(route("frontend.user.toggle.pin", { userId }), {}, {
        preserveState: true
    });
};

function pinUser(user: UserWithMessage) {
    handleTogglePin(user.id);
    user.pinned = 1;
    refreshPinnedLists();
}

function unpinUser(user: UserWithMessage) {
    handleToggleUnPin(user.id);
    user.pinned = 0;
    refreshPinnedLists();
}

function formatLastSeen(dateTime?: string): string {
    if (!dateTime) return "recently";

    const now = new Date().getTime();
    const last = new Date(dateTime).getTime();
    const diffSeconds = Math.floor((now - last) / 1000);

    if (diffSeconds < 60) return "just now";
    if (diffSeconds < 3600) return `${Math.floor(diffSeconds / 60)} minutes ago`;
    if (diffSeconds < 86400) return `${Math.floor(diffSeconds / 3600)} hours ago`;
    if (diffSeconds < 604800) return `${Math.floor(diffSeconds / 86400)} days ago`;

    return new Date(dateTime).toLocaleDateString();
}

onMounted(() => {
    refreshPinnedLists();
    computeUnreadPerUser(props.users);
});

const startChat = (slug: string, userId: string) => {
    router.visit(route("frontend.user.start.chat", { slug: slug, user: userId }));
};
</script>
