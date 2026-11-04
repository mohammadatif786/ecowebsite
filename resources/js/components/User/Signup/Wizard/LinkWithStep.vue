<script setup lang="ts">
// Step: "Who do you want to link up with?" Any number of countries, or whole regions at once.
import { computed, ref } from 'vue';
import { flagStyle } from './flags';
import { wizardIcons } from './icons';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ countries: string[] }>({ required: true });
const props = defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

const query = ref('');
const all = computed(() => [...props.options.caribbean, ...props.options.latinAmerica]);
const has = (n: string) => answer.value.countries.includes(n);
const allOf = (list: string[]) => list.every(has);

function toggle(name: string) {
    answer.value = { countries: has(name) ? answer.value.countries.filter((c) => c !== name) : [...answer.value.countries, name] };
}
function toggleSet(list: string[]) {
    answer.value = {
        countries: allOf(list) ? answer.value.countries.filter((c) => !list.includes(c)) : [...answer.value.countries, ...list.filter((n) => !has(n))],
    };
}
function toggleAll() {
    answer.value = { countries: allOf(all.value) ? [] : [...all.value] };
}

const groups = computed(() => {
    const q = query.value.trim().toLowerCase();
    const match = (n: string) => !q || n.toLowerCase().includes(q);
    return [
        { title: 'Caribbean', items: props.options.caribbean.filter(match) },
        { title: 'Latin America', items: props.options.latinAmerica.filter(match) },
    ].filter((g) => g.items.length);
});

const tile = (on: boolean) =>
    'display: flex; align-items: center; gap: 10px; min-height: 52px; padding: 6px 10px 6px 12px; border-radius: 12px; font-family: inherit; color: #0B1530; cursor: pointer; ' +
    s.card(on);
</script>

<template>
    <div class="flex flex-col gap-3.5">
        <div class="flex flex-wrap gap-2">
            <button type="button" :aria-pressed="allOf(all) ? 'true' : 'false'" :style="s.chip(allOf(all))" @click="toggleAll">All countries</button>
            <button type="button" :aria-pressed="allOf(options.caribbean) ? 'true' : 'false'" :style="s.chip(allOf(options.caribbean))" @click="toggleSet(options.caribbean)">
                All Caribbean
            </button>
            <button
                type="button"
                :aria-pressed="allOf(options.latinAmerica) ? 'true' : 'false'"
                :style="s.chip(allOf(options.latinAmerica))"
                @click="toggleSet(options.latinAmerica)"
            >
                All Latin America
            </button>
        </div>
        <input v-model="query" type="search" aria-label="Search countries" :placeholder="`Search ${all.length} countries`" :style="s.searchField" />
        <div role="group" aria-label="Countries" style="max-height: 320px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding: 2px">
            <div v-for="g in groups" :key="g.title" class="flex flex-col gap-2">
                <span :style="s.groupTitle">{{ g.title }} · {{ g.items.length }}</span>
                <div class="lu-grid3">
                    <button v-for="name in g.items" :key="name" type="button" :aria-pressed="has(name) ? 'true' : 'false'" :style="tile(has(name))" @click="toggle(name)">
                        <span aria-hidden="true" :style="flagStyle(name)"></span>
                        <span class="flex-1 text-left text-[13px] font-bold leading-[1.2]">{{ name }}</span>
                        <span :style="s.checkBox(has(name))">
                            <svg v-if="has(name)" aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="wizardIcons.check" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
            <p v-if="!groups.length" class="my-1.5 text-[14px] text-[#475569]">No match. Try another spelling.</p>
        </div>
        <p v-if="errors.countries" :style="s.fieldError" role="alert">{{ errors.countries }}</p>
    </div>
</template>
