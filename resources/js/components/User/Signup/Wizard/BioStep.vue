<script setup lang="ts">
// Step: "Tell us something about yourself" (optional). Up to 300 characters, with starters.
import { bioStarters } from './copy';
import * as s from './styles';

const answer = defineModel<{ bio: string }>({ required: true });
defineProps<{ errors: Record<string, string> }>();

function use(starter: string) {
    const bio = answer.value.bio ? answer.value.bio.replace(/\s*$/, ' ') : '';
    answer.value = { bio: (bio + starter.replace('…', ' ')).slice(0, 300) };
}
</script>

<template>
    <div class="flex flex-col gap-3.5">
        <div class="flex flex-col gap-1.5">
            <div class="flex items-baseline justify-between">
                <label for="f-bio" :style="s.label">Your bio</label>
                <span class="text-[12px] font-bold text-[#64748B]">{{ answer.bio.length }}/300</span>
            </div>
            <textarea
                id="f-bio"
                maxlength="300"
                rows="5"
                placeholder="e.g. Bahamian living in Miami. Big on Junkanoo, Sunday brunch and finding the best conch salad outside Nassau."
                :value="answer.bio"
                style="resize: none; box-sizing: border-box; width: 100%; padding: 14px 16px; border-radius: 14px; border: 1.5px solid #cbd5e1; background: #ffffff; font-family: inherit; font-size: 16px; line-height: 1.55; color: #0b1530"
                @input="answer = { bio: ($event.target as HTMLTextAreaElement).value.slice(0, 300) }"
            ></textarea>
            <p v-if="errors.bio" :style="s.fieldError" role="alert">{{ errors.bio }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="t in bioStarters"
                :key="t"
                type="button"
                style="min-height: 44px; padding: 0 16px; border-radius: 999px; border: 1.5px dashed #bfd0f5; background: #ffffff; font-family: inherit; font-size: 14px; font-weight: 600; color: #1d4ed8; cursor: pointer"
                @click="use(t)"
            >
                {{ t }}
            </button>
        </div>
    </div>
</template>
