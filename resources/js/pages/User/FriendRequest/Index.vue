<template>
    <AuthenticatedLayout>

        <Head title="Friend Requests" />

        <div class="py-8 mt-5">
            <!-- Header -->
            <header class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-gray-800 drop-shadow">
                        Friend Requests
                    </h1>
                    <span v-if="pendingReceivedCount > 0"
                        class="text-sm px-2 py-1 rounded-lg bg-blue-500 text-white shadow-sm">
                        {{ pendingReceivedCount }} pending
                    </span>
                </div>
            </header>

            <!-- Filters (Received / Sent) -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <button @click="activeTab = 'received'" :class="[
                    'px-3 py-1.5 rounded-full text-sm transition-colors inline-flex items-center gap-2',
                    activeTab === 'received'
                        ? 'bg-blue-600 text-white'
                        : 'bg-white shadow-sm hover:bg-gray-50 border'
                ]">
                    <Inbox class="w-4 h-4" /> Received
                </button>
                <button @click="activeTab = 'sent'" :class="[
                    'px-3 py-1.5 rounded-full text-sm transition-colors inline-flex items-center gap-2',
                    activeTab === 'sent'
                        ? 'bg-blue-600 text-white'
                        : 'bg-white shadow-sm hover:bg-gray-50 border'
                ]">
                    <Send class="w-4 h-4" /> Sent
                </button>
            </div>

            <!-- Received Requests -->
            <div v-show="activeTab === 'received'" class="space-y-3">
                <div v-if="receivedList.length" class="space-y-3">
                    <div v-for="req in receivedList" :key="req.id"
                        class="rounded-xl bg-white shadow-sm border px-5 py-5 flex flex-col sm:flex-row gap-5">
                        <!-- Avatar -->
                        <div class="relative shrink-0">
                            <div class="w-full sm:w-36 h-44 rounded-xl overflow-hidden border bg-gray-100">
                                <img :src="req.user.avatar" :alt="req.user?.name || 'User'"
                                    class="w-full h-full object-cover" />
                            </div>
                            <span
                                class="absolute bottom-2 right-2 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>

                        <!-- Body -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 flex-wrap mb-2">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-800">{{ req.user?.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ usernameOf(req.user) }} • {{
                                        req.user?.country || '—' }} • {{ req.user?.age ?? '—' }}</p>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                    :class="req.status === 0 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'">
                                    {{ req.status === 0 ? 'Pending' : 'Accepted' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">{{ bioOf(req.user) }}</p>

                            <div class="flex flex-wrap gap-2" v-if="req.status === 0">
                                <button
                                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"
                                    @click="acceptWithConfetti($event, req)">Accept</button>
                                <button
                                    class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition"
                                    @click="openDecline(req)">Decline</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-12">
                    <div class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-gray-50 ring-1 ring-gray-200">
                        <span>✨</span>
                        <span>No received requests</span>
                    </div>
                </div>
            </div>

            <!-- Sent Requests -->
            <div v-show="activeTab === 'sent'" class="space-y-3">
                <div v-if="sentList.length" class="space-y-3">
                    <div v-for="req in sentList" :key="req.id"
                        class="rounded-xl bg-white shadow-sm border px-5 py-5 flex flex-col sm:flex-row gap-5">
                        <!-- Avatar -->
                        <div class="relative shrink-0">
                            <div class="w-full sm:w-36 h-44 rounded-xl overflow-hidden border bg-gray-100">
                                <img :src="req.receiver.avatar" :alt="req.receiver?.name || 'User'"
                                    class="w-full h-full object-cover" />
                            </div>
                            <span v-if="req.status === 1"
                                class="absolute bottom-2 right-2 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>

                        <!-- Body -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 flex-wrap mb-2">
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-800">{{ req.receiver?.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ usernameOf(req.receiver) }} • {{
                                        req.receiver?.country || '—' }} • {{ req.receiver?.age ?? '—' }}</p>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                    :class="req.status === 0 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'">
                                    {{ req.status === 0 ? 'Waiting on Approval' : 'Accepted' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">{{ bioOf(req.receiver) }}</p>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition"
                                    @click="cancelOrUnfriend(req)">
                                    {{ req.status === 0 ? 'Cancel Request' : 'Unfriend' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-12">
                    <div class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-gray-50 ring-1 ring-gray-200">
                        <span>✨</span>
                        <span>No sent requests</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decline Modal -->
        <Modal :show="showDecline" @close="showDecline = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800">Decline Request</h2>
                <p class="text-gray-600 text-sm mt-2 mb-4">
                    Let <span class="font-medium text-gray-800">{{ declineName }}</span> know why (they'll receive a
                    polite message):
                </p>

                <div class="space-y-3 mb-6">
                    <label class="flex items-start gap-3 cursor-pointer text-sm text-gray-700">
                        <input type="radio" v-model="declineReason" value="not-looking" class="mt-1 accent-blue-600">
                        <span>Sorry, I'm not looking to add new friends right now</span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer text-sm text-gray-700">
                        <input type="radio" v-model="declineReason" value="events-only" class="mt-1 accent-blue-600">
                        <span>Thanks, but I'm mainly here for events & good vibes!</span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer text-sm text-gray-700">
                        <input type="radio" v-model="declineReason" value="island-time" class="mt-1 accent-blue-600">
                        <span>Appreciate it! My energy is on island time & low-key vibes right now 😌</span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer text-sm text-gray-700">
                        <input type="radio" v-model="declineReason" value="small-circle" class="mt-1 accent-blue-600">
                        <span>Thanks! Keeping my circle small & close these days</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="showDecline = false"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
                        Cancel
                    </button>
                    <button @click="confirmDecline"
                        class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition">
                        Send Decline
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import Modal from "@/components/front/Modal.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { FriendRequest } from "@/client/models/FriendRequest";
import { Inbox, Send } from "lucide-vue-next";
import confetti from "canvas-confetti";

const { friendRequests, sentRequests } = usePage<{
    friendRequests: FriendRequest[];
    sentRequests: FriendRequest[];
}>().props;

const activeTab = ref<"received" | "sent">("received");

const receivedList = computed(() => friendRequests || []);
const sentList = computed(() => sentRequests || []);

const pendingReceivedCount = computed(
    () => receivedList.value.filter((r: any) => r.status === 0).length
);

const showDecline = ref(false);
const declineName = ref("");
const declineReason = ref("not-looking");
const declineReq = ref<FriendRequest | null>(null);

const avatarFrom = (u: any, idx: number) => {
    const src = u?.more_photos?.[0] ? `/storage/${u.more_photos[0]}` : `https://picsum.photos/seed/${idx}/1200/800`;
    return src;
};
const usernameOf = (u: any) => u?.linkup_id || u?.username || "user";
const bioOf = (u: any) => u?.bio || "";

const acceptWithConfetti = (evt: MouseEvent, req: FriendRequest) => {
    // Confetti at button position
    const rect = (evt.currentTarget as HTMLElement).getBoundingClientRect();
    const x = (rect.left + rect.width / 2) / window.innerWidth;
    const y = (rect.top + rect.height / 2) / window.innerHeight;
    confetti({ particleCount: 140, spread: 70, origin: { x, y }, colors: ["#0ea5e9", "#22c55e", "#fbbf24", "#a78bfa"] });
    router.post(route("frontend.friend-request.accept", (req as any).id), undefined, {
        preserveScroll: true,
        preserveState: false,
    });
};

const openDecline = (req: FriendRequest) => {
    declineReq.value = req;
    // @ts-ignore
    declineName.value = (req.user?.name) || (req.receiver?.name) || "User";
    declineReason.value = "not-looking";
    showDecline.value = true;
};

const confirmDecline = () => {
    showDecline.value = false;
    if (declineReq.value) {
        router.post(route("frontend.friend-request.reject", (declineReq.value as any).id), undefined, {
            preserveScroll: true,
            preserveState: false,
        });
    }
};

const cancelOrUnfriend = (req: FriendRequest) => {
    // Use the same reject endpoint for cancel/unfriend to avoid inventing routes
    router.post(route("frontend.friend-request.reject", (req as any).id), undefined, {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>
