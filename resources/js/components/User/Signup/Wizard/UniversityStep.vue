<script setup lang="ts">
// Step: "Where did you study?" (optional). Picking a school joins its UVibes campus when the
// wizard is finished; a school that isn't listed can be added by name.
import { computed, ref } from 'vue';
import * as s from './styles';
import type { Answers } from './types';
import { universities, universityCountry, universityRegions } from './universities';

const answer = defineModel<Answers['university']>({ required: true });
defineProps<{ errors: Record<string, string> }>();

const open = ref(false);
const query = ref('');
const region = ref<string>('All');

const chosen = computed(() => !answer.value.no_university && !!answer.value.university.trim());

const groups = computed(() => {
    const q = query.value.trim().toLowerCase();
    return universityRegions
        .filter((r) => region.value === 'All' || region.value === r)
        .map((r) => ({
            title: r,
            items: universities.filter(([name, loc, reg]) => reg === r && (!q || (name + ' ' + loc).toLowerCase().includes(q))),
        }))
        .filter((g) => g.items.length);
});
const exact = computed(() => universities.some(([name]) => name.toLowerCase() === query.value.trim().toLowerCase()));
const canAdd = computed(() => query.value.trim().length >= 2 && !exact.value);

function pick(name: string, location: string, reg: string) {
    answer.value = { ...answer.value, no_university: false, university: name, location, country: universityCountry(location, reg) };
    open.value = false;
    query.value = '';
}
function addTyped() {
    answer.value = { ...answer.value, no_university: false, university: query.value.trim().slice(0, 120), location: '', country: null };
    open.value = false;
}
function toggleStudying() {
    answer.value = { ...answer.value, studying: !answer.value.studying, no_university: false };
}
function toggleNone() {
    const none = !answer.value.no_university;
    answer.value = { no_university: none, university: '', location: '', country: null, studying: false };
    open.value = false;
}

const trigger = computed(
    () =>
        'display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; min-height: 60px; padding: 8px 14px; border-radius: 14px; border: 1.5px solid ' +
        (open.value ? '#1D4ED8' : '#CBD5E1') +
        '; background: #FFFFFF; font-family: inherit; cursor: pointer; text-align: left;',
);
const option = (on: boolean) =>
    'display: flex; flex-direction: column; align-items: flex-start; gap: 2px; min-height: 48px; padding: 8px 10px; border: none; border-radius: 10px; font-family: inherit; text-align: left; cursor: pointer; ' +
    (on ? 'background: #EEF4FF;' : 'background: transparent;');
</script>

<template>
    <div class="flex flex-col gap-3.5">
        <div class="flex flex-col gap-1.5">
            <span id="f-uni-label" :style="s.label">University or college</span>
            <button type="button" aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" aria-labelledby="f-uni-label" :style="trigger" @click="open = !open">
                <span class="flex min-w-0 items-center gap-3">
                    <span aria-hidden="true" class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px] bg-[#EEF4FF] text-[#1D4ED8]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 9L12 4 2 9l10 5 10-5z" />
                            <path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5" />
                        </svg>
                    </span>
                    <span class="flex min-w-0 flex-col items-start gap-0.5 text-left">
                        <span class="text-[16px]" :class="chosen ? 'font-bold text-[#0B1530]' : 'font-medium text-[#64748B]'">{{ chosen ? answer.university : 'Select your university' }}</span>
                        <span v-if="chosen && answer.location" class="text-[12px] text-[#64748B]">{{ answer.location }}</span>
                    </span>
                </span>
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-none">
                    <path :d="open ? 'M6 15l6-6 6 6' : 'M6 9l6 6 6-6'" />
                </svg>
            </button>

            <div
                v-if="open"
                style="display: flex; flex-direction: column; gap: 10px; padding: 12px; border-radius: 16px; border: 1.5px solid #dce3f0; background: #ffffff; box-shadow: 0 16px 36px rgba(11, 21, 48, 0.12)"
            >
                <input
                    v-model="query"
                    type="search"
                    aria-label="Search universities"
                    placeholder="Search universities worldwide"
                    style="height: 46px; box-sizing: border-box; padding: 0 14px; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f6f8fc; font-family: inherit; font-size: 16px; color: #0b1530"
                />
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="r in ['All', ...universityRegions]"
                        :key="r"
                        type="button"
                        :aria-pressed="region === r ? 'true' : 'false'"
                        :style="s.smallChip(region === r)"
                        @click="region = r"
                    >
                        {{ r }}
                    </button>
                </div>
                <div role="listbox" aria-label="Universities" style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px">
                    <div v-for="g in groups" :key="g.title" class="flex flex-col gap-0.5">
                        <span
                            style="position: sticky; top: 0; background: #ffffff; padding: 4px 6px; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em"
                            >{{ g.title }} · {{ g.items.length }}</span
                        >
                        <button
                            v-for="[name, loc, reg] in g.items"
                            :key="name"
                            type="button"
                            role="option"
                            :aria-selected="chosen && answer.university === name ? 'true' : 'false'"
                            :style="option(chosen && answer.university === name)"
                            @click="pick(name, loc, reg)"
                        >
                            <span class="text-[14px] font-bold text-[#0B1530]">{{ name }}</span>
                            <span class="text-[12px] text-[#64748B]">{{ loc }}</span>
                        </button>
                    </div>
                    <p v-if="!groups.length" class="m-1.5 text-[14px] text-[#475569]">No match for "{{ query }}".</p>
                </div>
                <button
                    v-if="canAdd"
                    type="button"
                    style="min-height: 44px; padding: 8px 12px; border: none; border-radius: 10px; background: #eef4ff; font-family: inherit; font-size: 14px; font-weight: 700; color: #1d4ed8; text-align: left; cursor: pointer"
                    @click="addTyped"
                >
                    Can't find it? Add "{{ query.trim() }}"
                </button>
            </div>
            <p v-if="errors.university" :style="s.fieldError" role="alert">{{ errors.university }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" :aria-pressed="answer.studying ? 'true' : 'false'" :style="s.chip(answer.studying)" @click="toggleStudying">I'm still studying here</button>
            <button type="button" :aria-pressed="answer.no_university ? 'true' : 'false'" :style="s.chip(answer.no_university)" @click="toggleNone">
                I didn't go to university
            </button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; padding: 16px 18px; border-radius: 16px; background: #eef4ff; border: 1.5px solid #bfd0f5">
            <div class="flex items-center gap-2.5">
                <span class="rounded-full bg-[#1D4ED8] px-2.5 py-1 text-[12px] font-extrabold text-white">UVibes</span>
                <span class="text-[15px] font-extrabold">What picking your school unlocks</span>
            </div>
            <p class="m-0 text-[14px] leading-[1.5] text-[#1E293B]">
                Meet students and alumni from your school wherever they live now, see campus fêtes, homecomings and alumni events first, and rep your school with a badge on your
                profile.
            </p>
        </div>
    </div>
</template>
