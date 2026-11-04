<script setup lang="ts">
// "Your profile so far": the live preview card in the blue panel. It fills in as the member
// answers, so they can see what others will read.
import { computed } from 'vue';
import { flagStyle } from './flags';
import { wizardIcons } from './icons';
import type { Answers, WizardMember, WizardOptions } from './types';

const props = defineProps<{
    member: WizardMember;
    answers: Partial<Answers>;
    options: WizardOptions;
    /** Steps answered so far (out of 16). */
    answered: number;
    /** A main photo is picked. */
    hasPhoto: boolean;
}>();

const percent = computed(() => Math.min(100, Math.round(((props.answered + 1) / 17) * 100)));

const age = computed(() => {
    const birthday = props.answers.birthday?.birthday;
    if (!birthday) return null;
    const [y, m, d] = birthday.split('-').map(Number);
    const now = new Date();
    let years = now.getFullYear() - y;
    if (now.getMonth() + 1 < m || (now.getMonth() + 1 === m && now.getDate() < d)) years--;
    return years;
});

const nameLine = computed(() => {
    const first = props.member.firstName.trim();
    const last = props.member.lastName.trim();
    let line = first ? first + (last ? ' ' + last[0].toUpperCase() + '.' : '') : '[Your name]';
    if (age.value !== null && age.value >= 18 && props.answers.birthday?.show_age !== false) line += ', ' + age.value;
    const gender = props.answers.gender;
    if (gender?.gender && gender.show_gender !== false) line += ' · ' + gender.gender;
    return line;
});

const roots = computed(() => props.answers.roots?.roots || '');

const metas = computed(() => {
    const out: Array<{ d: string; text: string }> = [];
    const home = props.answers.home;
    if (home?.city) out.push({ d: wizardIcons.pin, text: home.city + (home.country_name ? ', ' + home.country_name : '') });
    const work = props.answers.work?.work?.trim();
    if (work) out.push({ d: wizardIcons.bag, text: work });
    const uni = props.answers.university;
    if (uni && !uni.no_university && uni.university?.trim()) {
        out.push({ d: wizardIcons.cap, text: (uni.studying ? 'Studying at ' : 'Studied at ') + uni.university });
    }
    const langs = props.answers.languages?.languages ?? [];
    if (langs.length) out.push({ d: wizardIcons.chat, text: 'Speaks ' + langs.join(', ') });
    return out;
});

const bio = computed(() => props.answers.bio?.bio?.trim() || '');

const tags = computed(() => {
    const titles: string[] = [];
    const picked = props.answers.intent?.intents ?? [];
    props.options.intents.forEach((group) => group.items.forEach((item) => picked.includes(item.id) && titles.push(item.title)));
    return [...(props.answers.interests?.interests ?? []), ...titles].slice(0, 8);
});

const empty = computed(() => !metas.value.length && !roots.value && !bio.value && !tags.value.length && nameLine.value === '[Your name]');
</script>

<template>
    <div
        class="lu-aside-hide relative mt-auto flex flex-col gap-3.5 rounded-[22px] bg-white px-[22px] py-5 text-[#0B1530] shadow-[0_24px_48px_rgba(11,21,48,0.28)]"
    >
        <div class="flex items-center justify-between gap-3">
            <span class="text-[12px] font-extrabold uppercase tracking-[0.05em] text-[#64748B]">Your profile so far</span>
            <span class="text-[12px] font-extrabold text-[#1D4ED8]">{{ percent }}% complete</span>
        </div>
        <div aria-hidden="true" class="h-1.5 overflow-hidden rounded-[3px] bg-[#EEF2F8]">
            <div class="h-full rounded-[3px] bg-[#1D4ED8] transition-[width] duration-300" :style="{ width: percent + '%' }"></div>
        </div>
        <div class="flex items-center gap-3.5">
            <div
                aria-hidden="true"
                class="flex h-14 w-14 flex-none items-center justify-center rounded-full"
                :class="hasPhoto ? 'bg-[#2B5FD9] text-white' : 'bg-[#DCE6FF] text-[#1D4ED8]'"
            >
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" />
                </svg>
            </div>
            <div class="flex min-w-0 flex-col gap-1">
                <span class="text-[18px] font-extrabold">{{ nameLine }}</span>
                <span v-if="roots" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#1E293B]">
                    <span aria-hidden="true" :style="flagStyle(roots, ' width: 24px; height: 16px; border-radius: 3px;')"></span>Repping {{ roots }}
                </span>
            </div>
        </div>
        <div v-if="metas.length" class="flex flex-col gap-1.5">
            <span v-for="m in metas" :key="m.text" class="flex items-start gap-2 text-[13px] leading-[1.4] text-[#1E293B]">
                <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 flex-none">
                    <path :d="m.d" />
                </svg>
                {{ m.text }}
            </span>
        </div>
        <p v-if="bio" class="m-0 text-[14px] leading-[1.5] text-[#1E293B]">{{ bio }}</p>
        <div v-if="tags.length" class="flex flex-wrap gap-1.5">
            <span v-for="t in tags" :key="t" class="rounded-full bg-[#EEF4FF] px-2.5 py-1 text-[12px] font-bold text-[#1D4ED8]">{{ t }}</span>
        </div>
        <span v-if="empty" class="text-[13px] text-[#64748B]">Your answers will build your profile here.</span>
    </div>
</template>
