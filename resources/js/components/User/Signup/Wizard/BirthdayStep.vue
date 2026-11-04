<script setup lang="ts">
// Step: "When should we send the cake?" Month, day and year boxes; the server repeats the 18+ rule.
import { computed, ref, watch } from 'vue';
import Toggle from './Toggle.vue';
import * as s from './styles';

const answer = defineModel<{ birthday: string; show_age: boolean }>({ required: true });
const props = defineProps<{ minimumAge: number; errors: Record<string, string> }>();

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const [y0, m0, d0] = (answer.value.birthday || '').split('-');
const year = ref(y0 ?? '');
const month = ref(m0 ? String(Number(m0)) : '');
const day = ref(d0 ? String(Number(d0)) : '');

const now = new Date();
const years = Array.from({ length: 101 }, (_, i) => String(now.getFullYear() - i));
const daysInMonth = computed(() => (month.value ? new Date(Number(year.value || 2000), Number(month.value), 0).getDate() : 31));
const days = computed(() => Array.from({ length: daysInMonth.value }, (_, i) => String(i + 1)));

const age = computed(() => {
    if (!year.value || !month.value || !day.value) return null;
    let years = now.getFullYear() - Number(year.value);
    if (now.getMonth() + 1 < Number(month.value) || (now.getMonth() + 1 === Number(month.value) && now.getDate() < Number(day.value))) years--;
    return years;
});

watch([year, month, day], () => {
    if (Number(day.value) > daysInMonth.value) day.value = '';
    const complete = year.value && month.value && day.value;
    const birthday = complete ? `${year.value}-${month.value.padStart(2, '0')}-${day.value.padStart(2, '0')}` : '';
    answer.value = { ...answer.value, birthday };
});

const ageOk = computed(() => age.value !== null && age.value >= props.minimumAge);
const ageUnder = computed(() => age.value !== null && age.value < props.minimumAge);
</script>

<template>
    <div class="flex flex-col gap-4">
        <fieldset class="m-0 border-0 p-0">
            <legend class="mb-2 p-0" :style="s.label">Date of birth</legend>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1.3fr; gap: 10px">
                <select v-model="month" aria-label="Month" :style="s.select()">
                    <option value="">Month</option>
                    <option v-for="(m, i) in months" :key="m" :value="String(i + 1)">{{ m }}</option>
                </select>
                <select v-model="day" aria-label="Day" :style="s.select()">
                    <option value="">Day</option>
                    <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
                </select>
                <select v-model="year" aria-label="Year" :style="s.select()">
                    <option value="">Year</option>
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
            </div>
        </fieldset>
        <div
            v-if="ageOk"
            role="status"
            style="padding: 14px 16px; border-radius: 14px; background: #eef4ff; border: 1.5px solid #bfd0f5; color: #1e3a8a; font-size: 15px; font-weight: 600"
        >
            {{ answer.show_age ? `You'll show as ${age} on your profile.` : 'Your age will stay hidden on your profile.' }}
        </div>
        <div
            v-if="ageUnder"
            role="alert"
            style="padding: 14px 16px; border-radius: 14px; background: #fff4ec; border: 1.5px solid #f5c6a5; color: #8a3b0b; font-size: 15px; font-weight: 600"
        >
            You need to be {{ minimumAge }} or older to join LinkUp.
        </div>
        <p v-if="errors.birthday && !ageUnder" :style="s.fieldError" role="alert">{{ errors.birthday }}</p>
        <Toggle v-model="answer.show_age" label="Show my age on my profile" />
    </div>
</template>
