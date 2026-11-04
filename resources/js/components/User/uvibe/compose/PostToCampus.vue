<template>
  <!-- "Posting to": only the member's own universities, one tap each. -->
  <div class="space-y-1.5">
    <p class="text-[11px] font-black text-slate-400 uppercase">Posting to</p>
    <button v-if="!universities.length" type="button" class="text-xs font-bold text-slate-500 underline" @click="$emit('openUniversityModal')">
      Add your university in your profile to post.
    </button>
    <div v-else class="flex gap-2 overflow-x-auto hide-scroll py-0.5">
      <button v-for="u in universities" :key="u.id" type="button" @click="$emit('update:modelValue', u.id)"
              :class="['shrink-0 rounded-full px-3.5 py-2 text-sm font-black whitespace-nowrap border-2 transition',
                       modelValue === u.id ? 'border-violet-600 bg-violet-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800 hover:border-violet-300']">
        {{ u.is_main ? '🏠' : u.flag }} {{ u.name }}
        <span v-if="u.status === 'alumni'" class="text-[10px] font-extrabold text-slate-500">· Alumni</span>
      </button>
    </div>
    <p v-if="universities.length === 1" class="text-[11px] font-bold text-slate-400">
      Attended more than one school? Add them under My universities in your profile.
    </p>
  </div>
</template>

<script setup>
import { watch } from 'vue';

const props = defineProps({
  modelValue: { type: Number, default: null }, // the chosen university's id
  universities: { type: Array, default: () => [] }, // the member's own universities
  defaultId: { type: Number, default: null }, // the campus they are looking at, if it is theirs
});
const emit = defineEmits(['update:modelValue', 'openUniversityModal']);

// The campus being viewed if it is yours, else your main one, else the first.
const pickDefault = () => {
  const ids = props.universities.map((u) => u.id);
  const id = ids.includes(props.defaultId) ? props.defaultId : (props.universities.find((u) => u.is_main)?.id ?? ids[0] ?? null);
  emit('update:modelValue', id);
};

// Keep the choice valid: if nothing is chosen (or the list changed under it), fall back to the default.
watch([() => props.universities, () => props.defaultId, () => props.modelValue], () => {
  if (!props.universities.some((u) => u.id === props.modelValue)) pickDefault();
}, { immediate: true });

defineExpose({ pickDefault });
</script>
