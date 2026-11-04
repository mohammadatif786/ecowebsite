<template>
    <div class="studio-bg fixed inset-0 z-[100] flex flex-col overflow-hidden text-white">
        <div class="studio-aurora"></div>

        <header
            class="lk-glass-solid relative z-[60] flex h-[60px] shrink-0 items-center justify-between px-3 sm:px-5"
            style="border-top: 0; border-right: 0; border-left: 0"
        >
            <div class="flex items-center gap-1.5">
                <button
                    @click="$emit('leave')"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-white/60 transition hover:bg-white/10 hover:text-white"
                >
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </button>
                <button
                    @click="leftSidebarOpen = true"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-white/70 transition hover:bg-white/10 hover:text-white lg:hidden"
                >
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="absolute top-1/2 left-1/2 flex -translate-x-1/2 -translate-y-1/2 items-center gap-2 sm:gap-3">
                <span class="logo text-xl leading-none font-black text-white">Link<span class="text-amber-400">up</span></span>
                <span
                    v-if="joined"
                    class="flex items-center gap-2 rounded-full py-1.5 pr-2 pl-3 text-[11px] font-black whitespace-nowrap text-white sm:pr-3 sm:pl-4 sm:text-xs"
                    style="background: linear-gradient(120deg, #dc2626, #ec4899)"
                >
                    <span class="lk-pulse-dot h-1.5 w-1.5 rounded-full bg-white"></span>
                    LIVE · {{ timer }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span class="lk-glass hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-black text-amber-300 sm:flex">
                    <i data-lucide="coins" class="h-4 w-4"></i>
                    {{ num(walletCoins) }}
                </span>
                <span class="lk-glass hidden rounded-full px-3.5 py-1.5 text-sm font-black text-emerald-300 sm:inline-flex">${{ walletBalanceAmount.toFixed(2) }}</span>
                <button
                    @click="rightPanelOpen = true"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-white/70 transition hover:bg-white/10 hover:text-white lg:hidden"
                >
                    <i data-lucide="panel-right-open" class="h-5 w-5"></i>
                </button>
            </div>
        </header>

        <div
            v-if="leftSidebarOpen || rightPanelOpen"
            class="fixed inset-x-0 bottom-0 top-[60px] z-20 bg-black/45 lg:hidden"
            @click="closeDrawers"
        ></div>

        <div class="relative z-30 flex min-h-0 flex-1 overflow-hidden">
            <aside
                :class="[
                    'lk-glass-solid fixed bottom-0 left-0 top-[60px] z-50 flex w-[min(82vw,18rem)] shrink-0 flex-col overflow-y-auto transition-transform duration-200 lg:static lg:top-auto lg:bottom-auto lg:left-auto lg:z-auto lg:w-64 lg:translate-x-0',
                    leftSidebarOpen ? 'translate-x-0' : '-translate-x-full',
                ]"
                style="border-top: 0; border-bottom: 0; border-left: 0"
            >
                <div class="flex items-center gap-3 p-4" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08)">
                    <div class="relative shrink-0">
                        <img :src="hostAvatar" class="h-12 w-12 rounded-full object-cover ring-2 ring-slate-950" />
                        <span class="absolute -right-0.5 -bottom-0.5 h-4 w-4 rounded-full border-2 border-[#0a1330] bg-emerald-400"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-white">{{ hostName }}</p>
                        <p class="flex items-center gap-1 text-xs font-bold text-white/45">
                            <i data-lucide="users" class="h-3 w-3"></i>{{ followersCount }} Followers
                        </p>
                    </div>
                    <button
                        @click="leftSidebarOpen = false"
                        class="ml-auto grid h-8 w-8 place-items-center rounded-full text-white/60 transition hover:bg-white/10 hover:text-white lg:hidden"
                    >
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 p-3">
                    <button
                        v-for="nav in navItems"
                        :key="nav.id"
                        @click="selectTab(nav.id)"
                        :class="[
                            'flex w-full items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-left text-sm font-bold transition',
                            activeTab === nav.id ? 'studio-nav-item active text-white' : 'studio-nav-item text-white/65',
                        ]"
                    >
                        <i :data-lucide="nav.icon" class="h-4 w-4 shrink-0"></i>
                        {{ nav.label }}
                    </button>
                </nav>

                <div v-if="activeTab === 'guests'" class="border-t border-white/5 p-3">
                    <slot name="guests-panel"></slot>
                </div>

                <div v-if="isHost" class="p-3">
                    <div
                        class="lk-card-glow rounded-2xl p-3.5"
                        style="background: linear-gradient(160deg, rgba(47, 155, 239, 0.14), rgba(139, 92, 246, 0.1))"
                    >
                        <p class="mb-1.5 text-[10px] font-black tracking-wide text-white/50 uppercase">Session Revenue</p>
                        <div class="flex items-center justify-between text-base font-black text-white">
                            <span class="flex items-center gap-1 text-amber-400">
                                <i data-lucide="coins" class="h-4 w-4"></i>
                                {{ num(sessionCoins) }}
                            </span>
                            <span class="text-emerald-400">${{ sessionCashAmount.toFixed(2) }}</span>
                        </div>
                        <button
                            @click="$emit('openTransfer')"
                            :disabled="sessionCashAmount < 50"
                            :class="[
                                'mt-2.5 w-full rounded-xl py-2.5 text-xs font-black text-white transition',
                                sessionCashAmount >= 50 ? 'hover:brightness-110' : 'cursor-not-allowed bg-white/5 text-white/30',
                            ]"
                            :style="sessionCashAmount >= 50 ? 'background: linear-gradient(120deg, #059669, #10b981)' : ''"
                        >
                            Transfer to Wallet
                        </button>
                    </div>
                </div>
            </aside>

            <main class="flex min-w-0 flex-1 overflow-hidden">
                <template v-if="activeTab === 'studio' || activeTab === 'guests'">
                    <section
                        class="relative m-2 flex-1 overflow-hidden rounded-2xl lg:m-3 lg:rounded-3xl"
                        style="background: radial-gradient(circle at 30% 20%, #1e3a6e, #0a1330 65%)"
                    >
                        <div
                            class="pointer-events-none absolute inset-0 opacity-70"
                            style="background: radial-gradient(circle at 75% 80%, rgba(139, 92, 246, 0.35), transparent 55%)"
                        ></div>
                        <div class="pointer-events-none absolute inset-0 grid place-items-center text-white/20">
                            <div class="text-center">
                                <div
                                    class="mx-auto grid h-20 w-20 place-items-center rounded-full"
                                    style="background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(255, 255, 255, 0.2)"
                                >
                                    <i data-lucide="video" class="h-8 w-8"></i>
                                </div>
                                <p class="mt-3 text-sm font-black tracking-wide">YOUR CAMERA PREVIEW</p>
                            </div>
                        </div>
                        <slot name="video-engine"></slot>

                        <button
                            v-if="featuredProduct"
                            type="button"
                            @click="$emit('openShop')"
                            class="absolute top-[86px] left-4 z-30 flex max-w-[290px] items-center gap-2 rounded-2xl bg-white p-1.5 pr-3 text-left shadow-xl transition hover:scale-[1.02]"
                        >
                            <img :src="featuredProduct.image || featuredProduct.cover_image" class="h-12 w-12 shrink-0 rounded-xl object-cover" />
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs leading-tight font-black text-slate-900">{{ featuredProduct.title }}</span>
                                <span class="mt-0.5 block text-[11px] leading-tight font-black text-blue-600">${{ Number(featuredProduct.price || 0).toFixed(2) }}</span>
                            </span>
                            <span class="shrink-0 rounded-full bg-blue-500 px-2.5 py-1 text-[10px] font-black text-white">
                                Shop {{ productCount > 1 ? productCount : '' }}
                            </span>
                        </button>

                        <div
                            class="pointer-events-none absolute inset-x-0 bottom-0 h-44"
                            style="background: linear-gradient(to top, rgba(5, 9, 25, 0.75), transparent)"
                        ></div>

                        <div class="absolute inset-x-4 top-4 z-20 flex flex-wrap items-start justify-between gap-2">
                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                <div class="lk-glass flex shrink-0 items-center gap-2 rounded-2xl px-3.5 py-2 whitespace-nowrap">
                                    <span class="flex items-center gap-2 text-sm font-black text-white">
                                        <span class="lk-pulse-dot h-2 w-2 rounded-full bg-lime-400"></span>
                                        Live Stream
                                    </span>
                                    <span class="h-4 w-px bg-white/15"></span>
                                    <span class="text-[11px] font-black tracking-wide text-white/70 uppercase">
                                        {{ streamCategory || 'Just Chatting' }}
                                    </span>
                                </div>
                                <span
                                    v-if="streamLocation"
                                    class="lk-glass flex shrink-0 items-center gap-1 rounded-full px-3 py-2 text-[11px] font-black whitespace-nowrap text-rose-300"
                                >
                                    <i data-lucide="map-pin" class="h-3 w-3"></i>
                                    {{ streamLocation }}
                                </span>
                            </div>

                            <div class="lk-glass flex shrink-0 items-center gap-3 rounded-2xl px-3.5 py-2 whitespace-nowrap">
                                <span class="flex items-center gap-1.5 text-sm font-black text-white">
                                    <i data-lucide="users" class="h-4 w-4 text-sky-300"></i>{{ num(viewerCount) }}
                                </span>
                                <span class="flex items-center gap-1.5 text-sm font-black text-white">
                                    <i data-lucide="heart" class="h-4 w-4 text-rose-400"></i>{{ num(likeCount) }}
                                </span>
                                <span class="flex items-center gap-1.5 text-sm font-black text-white">
                                    <i data-lucide="gift" class="h-4 w-4 text-amber-300"></i>{{ num(giftCount) }}
                                </span>
                            </div>
                        </div>

                        <div class="hide-scroll absolute right-4 bottom-20 left-4 z-20 flex gap-1.5 overflow-x-auto">
                            <slot name="guest-slots"></slot>
                        </div>

                        <div class="absolute right-4 bottom-4 left-4 z-20 flex items-center gap-3">
                            <div class="lk-glass flex items-center gap-1.5 rounded-full p-1.5">
                                <slot name="controls"></slot>
                            </div>
                            <button
                                v-if="isHost"
                                @click="$emit('end')"
                                class="ml-auto shrink-0 rounded-full px-6 py-3 font-black text-white transition hover:brightness-110 active:scale-95"
                                style="background: linear-gradient(120deg, #dc2626, #ec4899); box-shadow: 0 10px 24px -8px rgba(220, 38, 38, 0.6)"
                            >
                                End Live
                            </button>
                            <button
                                v-else
                                @click="$emit('leave')"
                                class="ml-auto shrink-0 rounded-full bg-white/10 px-6 py-3 font-black text-white transition hover:bg-white/20 active:scale-95"
                            >
                                Leave Stream
                            </button>
                        </div>
                    </section>

                    <aside
                        :class="[
                            'lk-glass-solid fixed bottom-0 right-0 top-[60px] z-50 flex w-[min(88vw,22rem)] shrink-0 flex-col overflow-hidden rounded-l-3xl transition-transform duration-200 lg:static lg:top-auto lg:right-auto lg:bottom-auto lg:z-auto lg:my-3 lg:mr-3 lg:w-80 lg:translate-x-0 lg:rounded-3xl',
                            rightPanelOpen ? 'translate-x-0' : 'translate-x-full',
                        ]"
                    >
                        <div class="flex shrink-0 items-center gap-2 p-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08)">
                            <div class="lk-seg w-full">
                                <button
                                    v-for="t in panelTabs"
                                    :key="t.id"
                                    @click="activePanel = t.id"
                                    :class="['flex-1 py-2 text-sm font-black', activePanel === t.id ? 'on' : '']"
                                >
                                    {{ t.label }}
                                </button>
                            </div>
                            <button
                                @click="rightPanelOpen = false"
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-white/60 transition hover:bg-white/10 hover:text-white lg:hidden"
                            >
                                <i data-lucide="x" class="h-5 w-5"></i>
                            </button>
                        </div>

                        <div class="relative flex min-h-0 flex-1 flex-col overflow-hidden">
                            <div v-show="activePanel === 'chat'" class="flex min-h-0 flex-1 flex-col">
                                <slot name="chat"></slot>
                            </div>
                            <div v-show="activePanel === 'qna'" class="hide-scroll h-full space-y-3 overflow-y-auto p-4">
                                <slot name="qna"></slot>
                            </div>
                            <div v-show="activePanel === 'polls'" class="hide-scroll h-full space-y-4 overflow-y-auto p-4">
                                <slot name="polls"></slot>
                            </div>
                        </div>

                        <div v-if="!isHost && joined" class="border-t border-white/5 bg-white/[0.02] p-4">
                            <slot name="gifts"></slot>
                        </div>
                    </aside>
                </template>

                <section v-else-if="activeTab === 'help'" class="hide-scroll flex-1 overflow-y-auto p-5 sm:p-8">
                    <div class="mx-auto max-w-3xl">
                        <div class="mb-6 flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-2xl text-white" style="background:linear-gradient(135deg,#f59e0b,#ec4899)"><i data-lucide="help-circle" class="h-6 w-6"></i></span><div><h2 class="text-2xl font-black">Help &amp; Guide</h2><p class="text-sm font-bold text-white/45">Quick tips for running a great LinkUp Live</p></div></div>
                        <div class="space-y-3">
                            <div v-for="guide in helpGuides" :key="guide.title" class="lk-glass rounded-2xl p-4"><div class="flex gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-white" :style="{ background: guide.gradient }"><i :data-lucide="guide.icon" class="h-5 w-5"></i></span><div><h3 class="font-black">{{ guide.title }}</h3><p class="mt-1 text-sm font-bold leading-relaxed text-white/55">{{ guide.text }}</p></div></div></div>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <slot name="overlays"></slot>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';

const emit = defineEmits(['leave', 'end', 'openTransfer', 'openShop', 'open-analytics']);

const props = defineProps({
    isHost: Boolean,
    joined: Boolean,
    hostName: String,
    hostAvatar: String,
    followersCount: [Number, String],
    sessionCoins: [Number, String],
    sessionCash: [Number, String],
    walletCoins: [Number, String],
    walletBalance: [Number, String],
    streamTitle: String,
    streamCategory: String,
    streamLocation: String,
    viewerCount: [Number, String],
    likeCount: [Number, String],
    giftCount: [Number, String],
    timer: String,
    featuredProduct: { type: Object, default: null },
    productCount: { type: Number, default: 0 },
});

const activeTab = ref('studio');
const activePanel = ref('chat');
const leftSidebarOpen = ref(false);
const rightPanelOpen = ref(false);

const navItems = [
    { id: 'studio', label: 'Studio View', icon: 'video' },
    { id: 'analytics', label: 'Analytics', icon: 'bar-chart-2' },
    { id: 'guests', label: 'Guest List', icon: 'users' },
    { id: 'help', label: 'Help & Guide', icon: 'help-circle' },
];

const panelTabs = [
    { id: 'chat', label: 'Chat' },
    { id: 'qna', label: 'Q&A' },
    { id: 'polls', label: 'Polls' },
];

const helpGuides = [
    { title: 'Going live', icon: 'radio', gradient: 'linear-gradient(135deg,#f43f5e,#ec4899)', text: 'Pick a title, category and location in Broadcast Settings, then hit Go Live. Your camera preview appears here once connected.' },
    { title: 'Co-hosting', icon: 'user-plus', gradient: 'linear-gradient(135deg,#14b8a6,#38bdf8)', text: 'Invite up to 6 guests from the Guest List. They receive a request and choose to accept before joining — you stay in control of who is on screen.' },
    { title: 'Selling live', icon: 'shopping-bag', gradient: 'linear-gradient(135deg,#8b5cf6,#3b82f6)', text: 'Tag Marketplace products or event tickets so viewers can buy without leaving the stream.' },
    { title: 'Earning from gifts', icon: 'gift', gradient: 'linear-gradient(135deg,#f59e0b,#ec4899)', text: 'Viewers send gifts that convert to coins. Your eligible share can be transferred to your Wallet from Session Revenue.' },
    { title: 'Q&A and Polls', icon: 'bar-chart-2', gradient: 'linear-gradient(135deg,#38bdf8,#8b5cf6)', text: 'Use the Q&A tab to field viewer questions on stream, or start a Poll to get instant feedback with live results.' },
];

const num = (n) => Number(n || 0).toLocaleString();
const amount = (value) => {
    const parsed = Number(value || 0);
    return Number.isFinite(parsed) ? parsed : 0;
};

const sessionCashAmount = computed(() => amount(props.sessionCash));
const walletBalanceAmount = computed(() => amount(props.walletBalance));

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

const closeDrawers = () => {
    leftSidebarOpen.value = false;
    rightPanelOpen.value = false;
};

const selectTab = (tabId) => {
    if (tabId === 'analytics') {
        emit('open-analytics');
        leftSidebarOpen.value = false;
        return;
    }
    activeTab.value = tabId;
    if (tabId !== 'guests') {
        leftSidebarOpen.value = false;
    }
    refreshIcons();
};

const openGuests = () => {
    selectTab('guests');
    leftSidebarOpen.value = true;
};

defineExpose({ openGuests });

onMounted(() => {
    refreshIcons();
});
</script>

<style scoped>
.studio-bg {
    background: linear-gradient(165deg, #0a1330 0%, #121f4a 42%, #1b1550 78%, #241154 100%);
}
.studio-aurora {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.studio-aurora::before,
.studio-aurora::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.35;
    animation: studioDrift 14s ease-in-out infinite alternate;
}
.studio-aurora::before {
    width: 420px;
    height: 420px;
    left: -80px;
    top: -60px;
    background: radial-gradient(circle, #2f9bef, transparent 70%);
}
.studio-aurora::after {
    width: 460px;
    height: 460px;
    right: -100px;
    bottom: -80px;
    background: radial-gradient(circle, #8b5cf6, transparent 70%);
    animation-delay: -7s;
}
@keyframes studioDrift {
    from {
        transform: translate(0, 0) scale(1);
    }
    to {
        transform: translate(30px, -20px) scale(1.08);
    }
}
.lk-glass {
    background: rgba(255, 255, 255, 0.055);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 1px solid rgba(255, 255, 255, 0.09);
}
.lk-glass-solid {
    background: rgba(10, 17, 41, 0.55);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.lk-pulse-dot {
    position: relative;
}
.lk-pulse-dot::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 999px;
    background: #ef4444;
    opacity: 0.55;
    animation: lkPulseRing 1.6s ease-out infinite;
}
@keyframes lkPulseRing {
    0% {
        transform: scale(0.6);
        opacity: 0.6;
    }
    100% {
        transform: scale(2.2);
        opacity: 0;
    }
}
.studio-nav-item {
    transition: 0.18s ease;
    position: relative;
}
.studio-nav-item.active {
    background: linear-gradient(120deg, #2f9bef, #2563eb 60%, #6d5efc);
    box-shadow: 0 10px 24px -10px rgba(47, 155, 239, 0.65);
}
.studio-nav-item:not(.active):hover {
    background: rgba(255, 255, 255, 0.08);
}
.lk-seg {
    background: rgba(255, 255, 255, 0.06);
    border-radius: 999px;
    padding: 4px;
    display: inline-flex;
    gap: 2px;
}
.lk-seg button {
    border-radius: 999px;
    transition: 0.18s ease;
}
.lk-seg button.on {
    background: linear-gradient(120deg, #2f9bef, #2563eb);
    color: #fff;
    box-shadow: 0 6px 16px -6px rgba(47, 155, 239, 0.6);
}
.lk-seg button:not(.on) {
    color: rgba(255, 255, 255, 0.55);
}
.lk-card-glow {
    position: relative;
    isolation: isolate;
}
.lk-card-glow::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(135deg, rgba(47, 155, 239, 0.5), rgba(139, 92, 246, 0.5));
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

.hide-scroll::-webkit-scrollbar {
    display: none;
}
.hide-scroll {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
