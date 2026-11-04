<template>
  <!-- The category pills under the campus bar: All, Events, Parties, Campus Life, Clubs, Food, Gigs. -->
  <div class="flex gap-2 overflow-x-auto hide-scroll mt-2">
    <button v-for="cat in tabs" :key="cat.id" type="button" @click="$emit('update:modelValue', cat.id)"
            :class="['rounded-full px-4 py-2 text-sm font-black shrink-0 flex items-center gap-1.5',
                     modelValue === cat.id ? 'bg-white text-violet-700' : 'bg-white/15 text-white']">
      <component :is="cat.icon" class="w-3.5 h-3.5" />{{ cat.label }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { categoryTabs } from '../../../../services/uvibeCategories';

// modelValue: the chosen category id ('all' shows everything).
// options: the server's composer options (its `categories` list) — optional.
const props = defineProps({
  modelValue: { type: String, default: 'all' },
  options: { type: Object, default: null },
});
defineEmits(['update:modelValue']);

const tabs = computed(() => categoryTabs(props.options));
</script>
