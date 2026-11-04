<script setup lang="ts">
// Step: "Where are your roots?" One country, picked from the list or the flag grid.
import { computed, ref } from 'vue';
import { flagStyle } from './flags';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ roots: string }>({ required: true });
const props = defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

const query = ref('');
const groups = computed(() => {
    const q = query.value.trim().toLowerCase();
    const match = (n: string) => !q || n.toLowerCase().includes(q);
    return [
        { title: 'Caribbean', items: props.options.caribbean.filter(match) },
        { title: 'Latin America', items: props.options.latinAmerica.filter(match) },
    ].filter((g) => g.items.length);
});

const tile = (on: boolean) =>
    'display: flex; align-items: center; gap: 10px; min-height: 52px; padding: 6px 12px; border-radius: 12px; font-family: inherit; color: #0B1530; cursor: pointer; ' + s.card(on);
const selectFlag = computed(() =>
    answer.value.roots
        ? flagStyle(answer.value.roots, ' position: absolute; left: 16px; top: 15px; pointer-events: none;')
        : 'position: absolute; left: 16px; top: 15px; width: 36px; height: 24px; border-radius: 4px; border: 1.5px dashed #CBD5E1; box-sizing: border-box; pointer-events: none;',
);
</script>

<template>
    <div class="flex flex-col gap-3.5">
        <div class="relative">
            <label for="f-roots" class="sr-only">Country</label>
            <span aria-hidden="true" :style="selectFlag"></span>
            <select
                id="f-roots"
                :value="answer.roots"
                style="appearance: none; -webkit-appearance: none; width: 100%; height: 54px; padding: 0 40px 0 64px; border-radius: 14px; border: 1.5px solid #cbd5e1; background: #ffffff; font-family: inherit; font-size: 16px; font-weight: 600; color: #0b1530; cursor: pointer"
                @change="answer = { roots: ($event.target as HTMLSelectElement).value }"
            >
                <option value="">Select your country</option>
                <optgroup label="Caribbean">
                    <option v-for="n in options.caribbean" :key="n" :value="n">{{ n }}</option>
                </optgroup>
                <optgroup label="Latin America">
                    <option v-for="n in options.latinAmerica" :key="n" :value="n">{{ n }}</option>
                </optgroup>
            </select>
        </div>
        <input v-model="query" type="search" aria-label="Search countries" placeholder="Or search by flag" :style="s.searchField" />
        <div role="radiogroup" aria-label="Country of roots" style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding: 2px">
            <div v-for="g in groups" :key="g.title" class="flex flex-col gap-2">
                <span :style="s.groupTitle">{{ g.title }} · {{ g.items.length }}</span>
                <div class="lu-grid3">
                    <button
                        v-for="name in g.items"
                        :key="name"
                        type="button"
                        role="radio"
                        :aria-checked="answer.roots === name ? 'true' : 'false'"
                        :style="tile(answer.roots === name)"
                        @click="answer = { roots: name }"
                    >
                        <span aria-hidden="true" :style="flagStyle(name)"></span>
                        <span class="text-left text-[13px] font-bold leading-[1.2]">{{ name }}</span>
                    </button>
                </div>
            </div>
            <p v-if="!groups.length" class="my-1.5 text-[14px] text-[#475569]">No match. Try another spelling.</p>
        </div>
        <p v-if="errors.roots" :style="s.fieldError" role="alert">{{ errors.roots }}</p>
    </div>
</template>
