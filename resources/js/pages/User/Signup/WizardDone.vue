<script setup lang="ts">
// "You're all linked up": shown once after the wizard is finished, with places to start based
// on what the member said brought them to LinkUp.
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import WizardAside from '@/components/User/Signup/Wizard/WizardAside.vue';
import { wizardIcons, type WizardIconKey } from '@/components/User/Signup/Wizard/icons';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    firstName: string;
    destinations: Array<{ title: string; desc: string; icon: WizardIconKey; url: string }>;
    urls: { explore: string; profile: string };
}>();

const name = props.firstName.trim();
const title = name ? `You're in, ${name}. Welcome to LinkUp!` : "You're in. Welcome to LinkUp!";
</script>

<template>
    <Head title="Welcome to LinkUp" />

    <AuthLayout :width="660">
        <template #aside>
            <WizardAside tag="Welcome" :head="`You're all\nlinked up.`" side="Your profile is live. Update anything anytime from Settings." />
        </template>

        <template #header><span></span></template>

        <div class="flex flex-col gap-[26px] py-6">
            <div class="flex h-24 w-24 items-center justify-center rounded-[28px] bg-[#2A2E31]">
                <img src="/assets/images/logo.png" alt="" class="block h-auto w-[76px]" />
            </div>
            <div class="flex flex-col gap-2.5">
                <h1 class="lu-title m-0 text-[44px] font-extrabold leading-[1.05] tracking-[-0.03em]">{{ title }}</h1>
                <p class="m-0 text-[17px] leading-[1.55] text-[#475569]">Your profile is ready. Here's where to start, based on what brought you here.</p>
            </div>
            <div class="lu-grid2">
                <Link
                    v-for="d in destinations"
                    :key="d.title"
                    :href="d.url"
                    class="flex min-h-[72px] items-center gap-3.5 rounded-[16px] border-[1.5px] border-[#DCE3F0] bg-white px-4 py-3.5 text-left no-underline hover:border-[#BFD0F5]"
                >
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-[12px] bg-[#1D4ED8] text-white">
                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="wizardIcons[d.icon]" />
                        </svg>
                    </span>
                    <span class="flex flex-col gap-0.5">
                        <span class="text-[15px] font-extrabold text-[#0B1530]">{{ d.title }}</span>
                        <span class="text-[13px] text-[#475569]">{{ d.desc }}</span>
                    </span>
                </Link>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    :href="urls.explore"
                    class="inline-flex h-[58px] items-center gap-2.5 rounded-[14px] bg-[#1D4ED8] px-7 text-[17px] font-bold !text-white no-underline shadow-[0_10px_24px_rgba(29,78,216,0.28)]"
                >
                    Start exploring
                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="wizardIcons.arrow" />
                    </svg>
                </Link>
                <Link :href="urls.profile" class="inline-flex min-h-11 items-center px-3 text-[15px] font-bold no-underline">Edit my profile</Link>
            </div>
        </div>
    </AuthLayout>
</template>
