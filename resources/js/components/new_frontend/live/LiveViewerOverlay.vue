<template>
    <div class="relative flex h-full w-full flex-col overflow-hidden bg-black">
        <img
            v-if="session.cover || session.thumb"
            :src="session.cover || session.thumb"
            alt=""
            class="absolute inset-0 z-0 h-full w-full object-cover opacity-90"
        />

        <div class="absolute inset-0 z-[1]">
            <slot></slot>
        </div>

        <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-32 bg-gradient-to-b from-black/70 to-transparent"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-72 bg-gradient-to-t from-black/85 to-transparent"></div>

        <header class="absolute inset-x-3 top-4 z-20 flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2 rounded-full bg-black/40 py-1 pr-2 pl-1">
                <img :src="session.hostAvatar || 'https://i.pravatar.cc/60?img=33'" class="h-9 w-9 rounded-full object-cover" />
                <div class="min-w-0 leading-none text-white">
                    <p class="max-w-[110px] truncate text-sm font-black">{{ formattedHost }}</p>
                    <p class="text-[11px] text-white/80">Heart {{ num((session.hearts || 0) * 100 + 5700) }}</p>
                </div>
                <button @click="handleFollow" class="ml-1 rounded-full bg-rose-500 px-3 py-1 text-[11px] font-black text-white">
                    {{ session.isFollowing ? 'Following' : '+ Follow' }}
                </button>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1 rounded-full bg-black/50 px-3 py-1 text-sm font-black text-white">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>{{ num(session.viewers) }}</span>
                </div>
                <button @click="$emit('close')" class="grid h-9 w-9 place-items-center rounded-full bg-black/50 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </header>

        <div class="absolute inset-x-3 top-16 z-20 flex items-center gap-2 text-[11px] font-black">
            <span class="rounded-full bg-black/50 px-3 py-1 text-white">Top Live</span>
            <span class="flex-1 truncate rounded-full bg-black/40 px-3 py-1 text-center text-amber-100">
                {{ session.category || 'Just Chatting' }}
            </span>
            <span class="rounded-full bg-black/60 px-3 py-1 text-white">
                <svg class="mr-1 inline h-3 w-3 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18M7 6h1v4"/></svg>
                <span class="text-amber-300">{{ num(session.coins || 0) }}</span>
                <span class="text-white/50"> · </span>
                <span class="text-emerald-300">${{ ((session.coins || 0) * 0.01).toFixed(2) }}</span>
            </span>
        </div>

        <div class="pointer-events-none absolute inset-x-3 top-[104px] z-20 flex items-start justify-between">
            <div class="pointer-events-auto space-y-3">
                <div v-if="featuredProduct" @click="$emit('openShop')" class="flex max-w-[290px] cursor-pointer items-center gap-2 rounded-2xl bg-white p-1.5 pr-3 shadow-lg">
                    <img :src="featuredProduct.image || featuredProduct.cover_image" class="h-12 w-12 shrink-0 rounded-xl object-cover" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs leading-tight font-black text-slate-900">{{ featuredProduct.title }}</p>
                        <p class="mt-0.5 text-[11px] leading-tight font-black text-blue-600">${{ Number(featuredProduct.price || 0).toFixed(2) }}</p>
                    </div>
                    <button @click.stop="$emit('openShop')" class="shrink-0 rounded-full bg-blue-500 px-2.5 py-1 text-[10px] font-black text-white">
                        Shop {{ session.products?.length > 1 ? session.products.length : '' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 flex flex-col">
            <div ref="chatBody" class="hide-scroll pointer-events-auto mb-4 max-h-52 max-w-[400px] space-y-1 overflow-y-auto px-3">
                <div v-for="(msg, i) in chat" :key="i" class="flex items-start gap-2 py-0.5">
                    <div
                        v-if="msg.who !== 'LinkUp' && msg.who !== 'You'"
                        :class="[
                            'grid h-6 w-6 shrink-0 place-items-center rounded-full border border-white/5 text-[9px] font-black text-white shadow-sm',
                            i % 2 === 0 ? 'bg-orange-500' : 'bg-sky-500',
                        ]"
                    >
                        {{
                            String(msg.who || '?')
                                .charAt(0)
                                .toUpperCase()
                        }}
                    </div>
                    <div v-else-if="msg.who === 'LinkUp'" class="bg-lkblue flex h-6 w-6 shrink-0 items-center justify-center rounded-full shadow-md">
                        <svg class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    </div>

                    <div class="min-w-0 flex-1 drop-shadow-lg">
                        <p class="text-[13px] leading-snug font-bold text-white">
                            <span
                                :class="[
                                    'mr-1.5 font-black',
                                    msg.who === 'You' ? 'text-sky-400' : msg.isHost || msg.who === 'LinkUp' ? 'text-lkyellow' : 'text-orange-400',
                                ]"
                            >
                                {{ msg.who }}
                            </span>
                            <span v-if="msg.isHost" class="mr-1.5 rounded bg-amber-500 px-1 text-[9px] font-black text-black uppercase">Host</span>
                            <span class="text-white/95">{{ msg.msg }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="pointer-events-auto flex w-full items-center gap-2 px-3 pb-4">
                <div class="flex min-w-0 flex-1 items-center rounded-full border border-white/20 bg-white/15">
                    <input
                        v-model="localChatInput"
                        @keyup.enter="handleSendChat"
                        type="text"
                        placeholder="Say something..."
                        class="min-w-0 flex-1 bg-transparent px-4 py-3 text-sm text-white outline-none placeholder:text-white/70"
                    />
                    <button
                        type="button"
                        @click="handleSendChat"
                        :disabled="!localChatInput.trim()"
                        class="mr-1 shrink-0 rounded-full bg-blue-500 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:opacity-45"
                    >
                        Send
                    </button>
                </div>

                <button @click="$emit('sendHeart')" aria-label="Send heart" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/15 text-rose-400">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                </button>

                <button
                    @click="showGiftToast"
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-full shadow-lg"
                    style="background: linear-gradient(135deg, #f59e0b, #ec4899)"
                >
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v9h14v-9M7.5 8A2.5 2.5 0 1 1 12 6v2M16.5 8A2.5 2.5 0 1 0 12 6v2"/></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps({
    session: { type: Object, required: true },
    chat: { type: Array, default: () => [] },
    featuredProduct: { type: Object, default: null },
    hasVideo: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'sendChat', 'sendHeart', 'openShop', 'openGift', 'follow']);

const localChatInput = ref('');
const chatBody = ref(null);

const formattedHost = computed(() => {
    const host = String(props.session.host || 'Broadcaster');
    return host.startsWith('@') ? host : `@${host}`;
});

const num = (n) => Number(n || 0).toLocaleString();

const handleFollow = () => {
    emit('follow');
};

const handleSendChat = () => {
    if (!localChatInput.value.trim()) return;
    emit('sendChat', localChatInput.value);
    localChatInput.value = '';
    scrollToBottom();
};

const showGiftToast = () => {
    emit('openGift');
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatBody.value) {
            chatBody.value.scrollTop = chatBody.value.scrollHeight;
        }
    });
};

onMounted(() => {
    if (window.lucide) window.lucide.createIcons();
    scrollToBottom();
});
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar {
    display: none;
}
.hide-scroll {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
