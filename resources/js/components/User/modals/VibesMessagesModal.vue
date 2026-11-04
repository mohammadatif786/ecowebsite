<template>
    <div v-if="isOpen"
        class="fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300"
        @click.self="close">
        <div class="bg-white rounded-[2rem] max-w-md w-full shadow-2xl overflow-hidden flex flex-col border border-slate-100">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                    <i data-lucide="message-circle" class="w-6 h-6 text-slate-900"></i>
                    Vibes Messages
                </h3>
                <button @click="close"
                    class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Followed Creators List -->
            <div class="p-4 overflow-y-auto hide-scroll max-h-[60vh] space-y-2">
                <div v-if="followedUsers.length" class="space-y-1">
                    <div v-for="(creator, idx) in followedUsers" :key="creator.user_id || creator.id"
                        @click="openChat(creator)"
                        class="flex items-center gap-3.5 p-3 rounded-2xl hover:bg-slate-50 transition cursor-pointer group active:scale-[0.99]">
                        <!-- Dark Initial Badge / Avatar -->
                        <div class="shrink-0">
                            <img v-if="creator.avatar && !creator.avatar.includes('pravatar')" :src="creator.avatar"
                                @error="$event.target.style.display='none'"
                                class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm" />
                            <div v-else
                                class="w-12 h-12 rounded-full bg-[#2c3848] text-white font-black text-sm grid place-items-center">
                                @{{ getInitial(creator) }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-black text-sm text-slate-900 truncate">
                                @{{ creator.handle || creator.name }}
                            </p>
                            <p class="text-xs font-semibold text-slate-400 truncate mt-0.5">
                                {{ getSampleMessage(idx) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 grid place-items-center mx-auto mb-3">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <p class="font-bold text-sm text-slate-700">No followed creators yet</p>
                    <p class="text-xs font-medium text-slate-400 mt-1">Follow creators from the suggested creators section to message them!</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const emit = defineEmits(['selectUser']);

const isOpen = ref(false);
const followedUsers = ref([]);

const SAMPLE_MESSAGES = [
    'Sounds good, appreciate you reaching out!',
    'Hey, thanks for reaching out! 🙌',
    'Great vibes! Check out my latest reel.',
    'Thanks for the support! 💯',
    'Appreciate you following!'
];

const open = (users = []) => {
    followedUsers.value = users;
    isOpen.value = true;
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

const close = () => {
    isOpen.value = false;
};

const getInitial = (creator) => {
    const name = creator.handle || creator.name || 'U';
    return name.charAt(0).toLowerCase();
};

const getSampleMessage = (index) => {
    return SAMPLE_MESSAGES[index % SAMPLE_MESSAGES.length];
};

const openChat = (creator) => {
    close();
    emit('selectUser', creator);
};

defineExpose({ open, close });
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
