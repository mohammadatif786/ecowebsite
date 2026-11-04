<script setup lang="ts">
// The frame around sign-up, sign-in, verify email and password reset: the blue brand panel on the
// left, the white page on the right. Mirrors the "LinkUp Onboarding" design. On narrow screens the
// panel collapses to the logo and headline and the page slides up over it like a sheet.
// On wide screens the panel stays fixed to the window (it scrolls inside itself if the window is
// short), so the form is always centred in view instead of floating in a very tall page.
import { Head } from '@inertiajs/vue3';
import { icons } from './icons';

withDefaults(
    defineProps<{
        step?: number;
        total?: number;
        /** Width of the page column: 580 for the account pages, 660 for the wizard (as in the design). */
        width?: 580 | 660;
    }>(),
    { step: 1, total: 17, width: 580 },
);

const features = [
    { title: 'LinkUp Events', desc: 'Host events, sell tickets and become your own event organiser.', d: icons.ticket },
    { title: 'LinkUp Live', desc: 'Go live, stream events and connect with your audience in real time.', d: icons.live },
    { title: 'Find Matches & Friends', desc: 'Meet new people, make friends and build real connections.', d: icons.people },
    { title: 'LinkUp Marketplace', desc: 'Buy and sell products directly on the platform.', d: icons.shop },
    { title: 'LinkUp Wallet', desc: 'Send and receive money, buy cryptocurrency, and pay for tickets, drinks and products.', d: icons.wallet },
    { title: 'LinkUp 360 News', desc: 'Stay updated with local, regional and global news.', d: icons.news },
];

const security = [
    '2-step verification (OTP or authenticator)',
    'Device checks and login alerts',
    'Lockouts after repeated attempts',
    'Never share your OTP or PIN. LinkUp support will never ask.',
];
</script>

<template>
    <Head>
        <link head-key="lu-font-a" rel="preconnect" href="https://fonts.googleapis.com" />
        <link head-key="lu-font-b" rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            head-key="lu-font"
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        />
    </Head>

    <div class="lu-shell flex min-h-screen flex-wrap bg-[#F6F8FC] text-[#0B1530]">
        <!-- Brand panel -->
        <aside
            class="relative box-border flex max-w-[560px] flex-[1_1_380px] flex-col gap-8 overflow-y-auto overflow-x-hidden bg-[#1D4ED8] px-12 py-10 text-white [scrollbar-width:thin] min-[901px]:sticky min-[901px]:top-0 min-[901px]:h-screen max-[900px]:max-w-none max-[900px]:basis-full"
        >
            <div aria-hidden="true" class="pointer-events-none absolute -right-40 -top-40 h-[480px] w-[480px] rounded-full border-[64px] border-white/[0.06]"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-[200px] -left-[120px] h-[420px] w-[420px] rounded-full bg-[rgba(212,232,58,0.12)]"></div>

            <div class="relative flex items-center">
                <img src="/assets/images/logo.png" alt="LinkUp" class="block h-[52px] w-auto" />
            </div>

            <slot name="aside">
                <div class="relative flex flex-col gap-4">
                    <h2 class="m-0 text-[52px] font-extrabold leading-[1.02] tracking-[-0.03em] max-[900px]:text-[30px]">Create. Connect.<br />Pay.</h2>
                    <span class="self-start rounded-full bg-white/[0.14] px-3.5 py-2 text-[13px] font-bold">Create events · Go live · Meet people · Wallet · News</span>
                    <p class="m-0 text-[17px] leading-[1.55] text-[#DBE6FF]">
                        One secure platform where you create events, connect with people, manage money and stay informed, all under one verified LinkUp ID.
                    </p>
                </div>

                <div class="relative flex flex-col gap-2 max-[900px]:hidden">
                    <div v-for="feature in features" :key="feature.title" class="flex items-start gap-3.5 rounded-[14px] bg-white/10 px-3.5 py-3">
                        <span class="flex h-[38px] w-[38px] flex-none items-center justify-center rounded-[11px] bg-white text-[#1D4ED8]">
                            <svg aria-hidden="true" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="feature.d" />
                            </svg>
                        </span>
                        <span class="flex flex-col gap-0.5">
                            <span class="text-[15px] font-extrabold">{{ feature.title }}</span>
                            <span class="text-[13px] leading-[1.45] text-[#DBE6FF]">{{ feature.desc }}</span>
                        </span>
                    </div>
                </div>

                <div class="relative flex flex-col gap-2.5 rounded-[16px] border-[1.5px] border-white/[0.18] bg-[rgba(11,21,48,0.22)] px-[18px] py-4 max-[900px]:hidden">
                    <div class="flex items-center gap-2.5">
                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4E83A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="icons.shield" />
                            <path :d="icons.shieldCheck" />
                        </svg>
                        <span class="text-[15px] font-extrabold">Built with security in mind</span>
                    </div>
                    <p class="m-0 text-[13px] leading-[1.5] text-[#DBE6FF]">Reserve your LinkUp ID, protect your account and turn on 2-step verification.</p>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 max-[480px]:grid-cols-1">
                        <span v-for="item in security" :key="item" class="flex items-start gap-2 text-[13px] leading-[1.4]">
                            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4E83A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="mt-[3px] flex-none">
                                <path :d="icons.check" />
                            </svg>
                            {{ item }}
                        </span>
                    </div>
                </div>
            </slot>
        </aside>

        <!-- Page -->
        <main
            class="relative box-border flex min-w-0 flex-[999_1_560px] flex-col bg-[#F6F8FC] px-[72px] py-10 max-[900px]:-mt-3 max-[900px]:rounded-t-[20px] max-[900px]:px-5 max-[900px]:pb-8 max-[900px]:pt-6"
        >
            <slot name="header">
                <div class="flex flex-col gap-3.5">
                    <div class="flex min-h-11 items-center justify-between">
                        <span class="text-[14px] font-bold text-[#1D4ED8]">Welcome to LinkUp</span>
                        <span class="text-[14px] font-bold text-[#475569]">Step {{ step }} of {{ total }}</span>
                    </div>
                    <div aria-hidden="true" class="flex gap-1">
                        <div v-for="n in total" :key="n" class="h-1.5 flex-1 rounded-[3px]" :class="n <= step ? 'bg-[#2563EB]' : 'bg-[#DCE3F0]'"></div>
                    </div>
                </div>
            </slot>

            <div
                class="mx-auto flex w-full flex-1 flex-col justify-center gap-[22px] pb-2"
                :class="width === 660 ? 'max-w-[660px] pt-7' : 'max-w-[580px] pt-6'"
            >
                <slot />
            </div>
        </main>
    </div>
</template>

<style>
/* Shared look for every control inside the frame. Namespaced so nothing outside it changes. */
.lu-shell {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}
.lu-shell select:focus,
.lu-shell input:focus,
.lu-shell textarea:focus,
.lu-shell button:focus-visible,
.lu-shell a:focus-visible {
    outline: 3px solid rgba(37, 99, 235, 0.35);
    outline-offset: 2px;
}
.lu-shell input::placeholder,
.lu-shell textarea::placeholder {
    color: #94a3b8;
}
.lu-shell main a {
    color: #1d4ed8;
}
.lu-shell main a:hover {
    color: #1e3a8a;
}

/* Grids from the design (2, 3 and 5 columns that fold on small screens). */
.lu-grid2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}
.lu-grid3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
}
.lu-grid5 {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
}
@media (max-width: 900px) {
    .lu-aside-hide {
        display: none !important;
    }
    .lu-title {
        font-size: 28px !important;
    }
    .lu-grid3,
    .lu-grid5 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 480px) {
    .lu-grid2 {
        grid-template-columns: 1fr;
    }
}

/* Two-handle age slider. */
.lu-dual {
    position: relative;
    height: 40px;
}
.lu-dual input {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 40px;
    margin: 0;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}
.lu-dual input:focus {
    outline: none;
}
.lu-dual input::-webkit-slider-runnable-track {
    background: transparent;
    height: 40px;
}
.lu-dual input::-moz-range-track {
    background: transparent;
}
.lu-dual input::-webkit-slider-thumb {
    pointer-events: auto;
    -webkit-appearance: none;
    width: 30px;
    height: 30px;
    margin-top: 5px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #1d4ed8;
    box-shadow: 0 2px 8px rgba(11, 21, 48, 0.2);
    cursor: grab;
}
.lu-dual input::-moz-range-thumb {
    pointer-events: auto;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #1d4ed8;
    cursor: grab;
}
.lu-dual input:focus-visible::-webkit-slider-thumb {
    box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.3);
}
</style>
